<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Models\Claim;
use App\Models\ClaimStep;
use App\Models\Client;
use App\Models\Contract;
use App\Models\Document;
use App\Models\TallyQueue;
use App\Services\AuditLogger;

class TallyClaimWebhookController extends BaseController
{
    // Domaines autorisés pour le téléchargement des pièces jointes Tally
    private const TALLY_HOSTS = ['storage.tally.so', 'tally.so'];

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
        $accountNumber = $this->field($fields, 'compte', 'account');
        $policyNumber  = $this->field($fields, 'police', 'policy_number', 'policy');
        $insurerField  = $this->field($fields, 'assureur', 'insurer');
        $attestation   = $this->fileField($fields, 'attestation');

        // ── 5. Identification du client ───────────────────────────────────────
        $client = $accountNumber ? Client::findByAccountNumber($accountNumber) : null;

        if (!$client) {
            $this->enqueue($payload, $data, $responseId, $rawBody);
            AuditLogger::log('system', null, 'tally_claim_unmatched',
                "response:{$responseId} compte:{$accountNumber}", $this->ip());
            $this->ack();
        }

        try {
            // ── 6. Contrat lié ────────────────────────────────────────────────
            $contract = ($policyNumber)
                ? Contract::findByPolicyForClient($policyNumber, (int)$client['id'])
                : null;

            $claimInsurer = $insurerField ?: ($contract['insurer'] ?? 'Non précisé');
            $claimBranche = $contract['branche'] ?? 'Non précisé';

            // ── 7. Création du sinistre ───────────────────────────────────────
            $claimId = Claim::create([
                'client_id'       => (int)$client['id'],
                'contract_id'     => $contract ? (int)$contract['id'] : null,
                'claim_number'    => 'PENDING',
                'insurer'         => $claimInsurer,
                'branche'         => $claimBranche,
                'occurrence_date' => date('Y-m-d'),
                'status'          => 'ouvert',
                'description'     => null,
                'is_auto_rc'      => 0,
            ]);

            $claimNumber = 'SIN-' . date('Y') . '-' . str_pad((string)$claimId, 4, '0', STR_PAD_LEFT);
            Claim::setNumber($claimId, $claimNumber);
            ClaimStep::initForClaim($claimId, false);

            // ── 8. Archive JSON de la déclaration (doc sinistre) ─────────────
            $this->saveDeclarationDoc(
                $client, $payload, $data, $responseId,
                $claimId,
                $contract ? (int)$contract['id'] : null
            );

            // ── 9. Attestation d'assurance → doc CONTRAT ─────────────────────
            if ($attestation && $contract) {
                $this->saveAttestationDoc($attestation, $client, (int)$contract['id']);
            }

            AuditLogger::log('system', null, 'tally_claim_created',
                "client:{$client['id']} claim:{$claimId} response:{$responseId}", $this->ip());

        } catch (\Throwable $e) {
            error_log('[TallyClaim] Erreur: ' . $e->getMessage());
            $this->enqueue($payload, $data, $responseId, $rawBody);
            AuditLogger::log('system', null, 'tally_claim_error',
                "response:{$responseId} err:" . $e->getMessage(), $this->ip());
        }

        $this->ack();
    }

    // ── Extraction des champs texte ───────────────────────────────────────────

    private function field(array $fields, string ...$keywords): string
    {
        foreach ($fields as $f) {
            $label = strtolower((string)($f['label'] ?? ''));
            $key   = strtolower((string)($f['key']   ?? ''));
            $value = trim((string)($f['value'] ?? ''));
            if (!$value) continue;
            foreach ($keywords as $kw) {
                if (str_contains($label, $kw) || str_contains($key, $kw)) {
                    // Pour le numéro de compte : extraire 6 chiffres consécutifs
                    if ($kw === 'compte' || $kw === 'account') {
                        if (preg_match('/\d{6}/', $value, $m)) return $m[0];
                        continue;
                    }
                    return $value;
                }
            }
        }
        // Fallback compte : première valeur à 6 chiffres exactement
        if (in_array('compte', $keywords, true) || in_array('account', $keywords, true)) {
            foreach ($fields as $f) {
                $v = trim((string)($f['value'] ?? ''));
                if (preg_match('/^\d{6}$/', $v)) return $v;
            }
        }
        return '';
    }

    // ── Extraction d'un champ fichier Tally ──────────────────────────────────

    private function fileField(array $fields, string ...$keywords): ?array
    {
        foreach ($fields as $f) {
            $label = strtolower((string)($f['label'] ?? ''));
            $type  = strtolower((string)($f['type']  ?? ''));
            if ($type !== 'file_upload') continue;
            foreach ($keywords as $kw) {
                if (str_contains($label, strtolower($kw))) {
                    $files = (array)($f['value'] ?? []);
                    return !empty($files[0]) ? $files[0] : null;
                }
            }
        }
        return null;
    }

    // ── Téléchargement et enregistrement de l'attestation ───────────────────

    private function saveAttestationDoc(array $fileData, array $client, int $contractId): void
    {
        $url  = (string)($fileData['url']      ?? '');
        $name = (string)($fileData['name']     ?? 'attestation');
        $mime = (string)($fileData['mimeType'] ?? '');

        // Sécurité : domaine Tally uniquement
        $host = (string)(parse_url($url, PHP_URL_HOST) ?? '');
        if (!in_array($host, self::TALLY_HOSTS, true)) {
            error_log('[TallyClaim] URL attestation rejetée : ' . $host);
            return;
        }

        $ctx     = stream_context_create(['http' => ['timeout' => 30, 'follow_location' => true]]);
        $content = @file_get_contents($url, false, $ctx);
        if ($content === false || $content === '') {
            error_log('[TallyClaim] Téléchargement attestation échoué : ' . $url);
            return;
        }

        $config  = require CONFIG_PATH;
        $baseDir = rtrim($config['storage']['documents'], '/') . '/contrats/' . $contractId;
        if (!is_dir($baseDir) && !mkdir($baseDir, 0755, true)) {
            error_log('[TallyClaim] Impossible de créer le dossier : ' . $baseDir);
            return;
        }

        $ext    = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $stored = bin2hex(random_bytes(16)) . ($ext ? '.' . $ext : '');
        $path   = $baseDir . '/' . $stored;

        if (file_put_contents($path, $content) === false) {
            error_log('[TallyClaim] Écriture attestation échouée : ' . $path);
            return;
        }

        Document::create([
            'client_id'         => (int)$client['id'],
            'contract_id'       => $contractId,
            'claim_id'          => null,
            'scope'             => 'contrat',
            'category'          => 'souscription',
            'doc_type'          => 'attestation_assurance',
            'original_filename' => basename($name),
            'stored_path'       => $path,
            'mime_type'         => $mime ?: 'application/octet-stream',
            'file_size'         => strlen($content),
            'source'            => 'tally',
            'status'            => 'valide',
        ]);
    }

    // ── Archive JSON de la déclaration ────────────────────────────────────────

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

    // ── File d'attente (client introuvable ou erreur) ─────────────────────────

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
