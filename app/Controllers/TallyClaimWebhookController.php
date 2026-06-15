<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Models\Claim;
use App\Models\Client;
use App\Models\Contract;
use App\Models\Document;
use App\Models\TallyQueue;
use App\Services\AuditLogger;

class TallyClaimWebhookController extends BaseController
{
    public function handle(): never
    {
        $rawBody = (string)file_get_contents('php://input');

        // ── 1. Vérification signature HMAC-SHA256 ────────────────────────────
        $secret   = $this->tallySecret();
        $header   = $_SERVER['HTTP_TALLY_SIGNATURE'] ?? '';
        $received = str_starts_with($header, 'sha256=') ? substr($header, 7) : $header;
        $expected = hash_hmac('sha256', $rawBody, $secret);

        if (!$secret || !hash_equals($expected, $received)) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid signature']);
            exit;
        }

        // ── 2. Parse JSON ─────────────────────────────────────────────────────
        $payload = json_decode($rawBody, true);
        if (!is_array($payload)) {
            $this->ack();
        }

        if (($payload['eventType'] ?? '') !== 'FORM_RESPONSE') {
            $this->ack();
        }

        $data       = $payload['data'] ?? [];
        $responseId = (string)($data['responseId'] ?? '');
        $fields     = (array)($data['fields']      ?? []);

        // ── 3. Idempotence ────────────────────────────────────────────────────
        if ($responseId && TallyQueue::responseExists($responseId)) {
            $this->ack();
        }

        // ── 4. Extraction des champs du formulaire ────────────────────────────
        $accountNumber  = $this->extractAccountNumber($fields);
        $policyNumber   = $this->extractField($fields, 'police', 'policy_number', 'policy');
        $occurrenceDate = $this->extractDate($fields);
        $branche        = $this->extractField($fields, 'branche', 'branch');
        $insurer        = $this->extractField($fields, 'assureur', 'insurer');
        $description    = $this->extractField($fields, 'description', 'circonstance', 'détail');

        // ── 5. Identification du client ───────────────────────────────────────
        $client = $accountNumber ? Client::findByAccountNumber($accountNumber) : null;

        if ($client) {
            try {
                // ── 6. Contrat lié (optionnel) ────────────────────────────────
                $contract = ($policyNumber)
                    ? Contract::findByPolicyForClient($policyNumber, (int)$client['id'])
                    : null;

                $claimBranche = $branche  ?: ($contract['branche']  ?? 'Non précisé');
                $claimInsurer = $insurer  ?: ($contract['insurer']  ?? 'Non précisé');
                $claimDesc    = $description
                    ?: 'Déclaration reçue via formulaire Tally (réf. ' . $responseId . ').';

                // ── 7. Création du sinistre ───────────────────────────────────
                $claimId = Claim::create([
                    'client_id'       => (int)$client['id'],
                    'contract_id'     => $contract ? (int)$contract['id'] : null,
                    'claim_number'    => 'PENDING',
                    'insurer'         => $claimInsurer,
                    'branche'         => $claimBranche,
                    'occurrence_date' => $occurrenceDate,
                    'status'          => 'ouvert',
                    'description'     => $claimDesc,
                ]);

                $claimNumber = 'SIN-' . date('Y') . '-' . str_pad((string)$claimId, 4, '0', STR_PAD_LEFT);
                Claim::setNumber($claimId, $claimNumber);

                // ── 8. Archiver le payload JSON comme déclaration ─────────────
                $this->saveDeclarationDoc(
                    $client, $payload, $data, $responseId,
                    $claimId,
                    $contract ? (int)$contract['id'] : null
                );

                AuditLogger::log('system', null, 'tally_claim_created',
                    "client:{$client['id']} claim:{$claimId} response:{$responseId}", $this->ip());

            } catch (\Throwable $e) {
                error_log('[TallyClaim] Erreur création sinistre: ' . $e->getMessage());
                $this->enqueue($payload, $data, $responseId, $rawBody);
                AuditLogger::log('system', null, 'tally_claim_error',
                    "response:{$responseId} err:" . $e->getMessage(), $this->ip());
            }
        } else {
            // ── Client introuvable → file d'attente ───────────────────────────
            $this->enqueue($payload, $data, $responseId, $rawBody);
            AuditLogger::log('system', null, 'tally_claim_unmatched',
                "response:{$responseId} account:{$accountNumber}", $this->ip());
        }

        $this->ack();
    }

    // ── Extraction des champs Tally ───────────────────────────────────────────

    private function extractAccountNumber(array $fields): string
    {
        // Priorité : champ dont le label/key contient "compte" ou "account"
        foreach ($fields as $f) {
            $label = strtolower((string)($f['label'] ?? ''));
            $key   = strtolower((string)($f['key']   ?? ''));
            $value = trim((string)($f['value'] ?? ''));
            if ((str_contains($label, 'compte') || str_contains($key, 'account'))
                && preg_match('/\d{6}/', $value, $m)) {
                return $m[0];
            }
        }
        // Fallback : première valeur exactement à 6 chiffres
        foreach ($fields as $f) {
            $value = trim((string)($f['value'] ?? ''));
            if (preg_match('/^\d{6}$/', $value)) {
                return $value;
            }
        }
        return '';
    }

    private function extractField(array $fields, string ...$keywords): string
    {
        foreach ($fields as $f) {
            $label = strtolower((string)($f['label'] ?? ''));
            $key   = strtolower((string)($f['key']   ?? ''));
            $value = trim((string)($f['value'] ?? ''));
            if (!$value) continue;
            foreach ($keywords as $kw) {
                if (str_contains($label, $kw) || str_contains($key, $kw)) {
                    return $value;
                }
            }
        }
        return '';
    }

    private function extractDate(array $fields): string
    {
        foreach ($fields as $f) {
            $label = strtolower((string)($f['label'] ?? ''));
            $key   = strtolower((string)($f['key']   ?? ''));
            $value = trim((string)($f['value'] ?? ''));
            if (!$value) continue;
            if (str_contains($label, 'date') || str_contains($key, 'date')
                || str_contains($label, 'survenance')) {
                // Tally renvoie les dates en ISO 8601 ou en timestamp ms
                $ts = is_numeric($value) ? (int)($value / 1000) : strtotime($value);
                if ($ts && $ts <= time()) {
                    return date('Y-m-d', $ts);
                }
            }
        }
        return date('Y-m-d');
    }

    // ── Persistance ───────────────────────────────────────────────────────────

    private function saveDeclarationDoc(
        array $client, array $payload, array $data,
        string $responseId, int $claimId, ?int $contractId
    ): void {
        $config  = require CONFIG_PATH;
        $baseDir = rtrim($config['storage']['documents'], '/') . '/tally';

        if (!is_dir($baseDir) && !mkdir($baseDir, 0755, true)) {
            throw new \RuntimeException('Impossible de créer le répertoire tally.');
        }

        $safeId   = preg_replace('/[^a-zA-Z0-9_-]/', '', $responseId) ?: bin2hex(random_bytes(8));
        $path     = $baseDir . '/tally_claim_' . $safeId . '.json';
        $content  = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if (file_put_contents($path, $content) === false) {
            throw new \RuntimeException("Impossible d'écrire le fichier de déclaration.");
        }

        $formName     = (string)($data['formName'] ?? 'declaration_sinistre');
        $originalName = $this->sanitize($formName) . '_' . $safeId . '.json';

        Document::create([
            'client_id'         => (int)$client['id'],
            'contract_id'       => $contractId,
            'claim_id'          => $claimId,
            'scope'             => 'sinistre',
            'category'          => 'declaration',
            'doc_type'          => 'declaration_sinistre',
            'original_filename' => $originalName,
            'stored_path'       => $path,
            'mime_type'         => 'application/json',
            'file_size'         => strlen($content),
            'source'            => 'tally',
            'status'            => 'valide',
        ]);
    }

    private function enqueue(array $payload, array $data, string $responseId, string $rawBody): void
    {
        if ($responseId && TallyQueue::responseExists($responseId)) {
            return;
        }
        TallyQueue::create([
            'event_id'    => (string)($payload['eventId'] ?? ''),
            'response_id' => $responseId ?: bin2hex(random_bytes(8)),
            'form_id'     => (string)($data['formId']   ?? '') ?: null,
            'form_name'   => '[Sinistre] ' . (string)($data['formName'] ?? 'Déclaration'),
            'payload'     => $rawBody,
        ]);
    }

    private function sanitize(string $name): string
    {
        $name = preg_replace('/[^a-zA-Z0-9_\-]/u', '_', $name) ?? 'declaration';
        return strtolower(substr($name, 0, 50));
    }

    private function tallySecret(): string
    {
        $config = require CONFIG_PATH;
        return (string)($config['tally']['secret'] ?? '');
    }

    private function ack(): never
    {
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode(['ok' => true]);
        exit;
    }
}
