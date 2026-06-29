<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Models\Client;
use App\Models\Document;
use App\Models\TallyQueue;
use App\Services\AuditLogger;

class TallyWebhookController extends BaseController
{
    public function handle(): never
    {
        $rawBody = (string)file_get_contents('php://input');

        // ── 1. Vérification de la signature HMAC-SHA256 ──────────────────────
        $secret    = $this->tallySecret();
        $header    = $_SERVER['HTTP_TALLY_SIGNATURE'] ?? '';
        // Tally envoie "sha256=<hex>" ou directement "<hex>"
        $received  = str_starts_with($header, 'sha256=') ? substr($header, 7) : $header;
        $expected  = hash_hmac('sha256', $rawBody, $secret);

        if (!$secret || !hash_equals($expected, $received)) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invalid signature']);
            exit;
        }

        // ── 2. Parse JSON ────────────────────────────────────────────────────
        $payload = json_decode($rawBody, true);
        if (!is_array($payload)) {
            $this->ack();
        }

        // On n'agit que sur les soumissions de formulaire
        if (($payload['eventType'] ?? '') !== 'FORM_RESPONSE') {
            $this->ack();
        }

        $data       = $payload['data'] ?? [];
        $responseId = (string)($data['responseId'] ?? '');
        $fields     = (array)($data['fields']    ?? []);

        // ── 3. Idempotence ───────────────────────────────────────────────────
        if ($responseId && TallyQueue::responseExists($responseId)) {
            $this->ack();
        }

        // ── 4. Identification du client ──────────────────────────────────────
        $client = $this->findClient($fields);

        if ($client) {
            // ── 5a. Sauvegarde du document ───────────────────────────────────
            try {
                $this->saveDocument($client, $payload, $data, $responseId);
                AuditLogger::log('system', null, 'tally_matched',
                    "client:{$client['id']} response:{$responseId}", $this->ip());
            } catch (\Throwable $e) {
                error_log('[Tally] saveDocument failed: ' . $e->getMessage());
                // On met quand même en queue pour ne pas perdre la soumission
                $this->enqueue($payload, $data, $responseId, $rawBody);
            }
        } else {
            // ── 5b. File d'attente pour rattachement manuel ──────────────────
            $this->enqueue($payload, $data, $responseId, $rawBody);
            AuditLogger::log('system', null, 'tally_unmatched',
                "response:{$responseId}", $this->ip());
        }

        $this->ack();
    }

    // ── Helpers privés ───────────────────────────────────────────────────────

    private function findClient(array $fields): ?array
    {
        // Passe 1 : recherche par numéro de compte (6 chiffres)
        foreach ($fields as $f) {
            $value = trim((string)($f['value'] ?? ''));
            if (preg_match('/^\d{6}$/', $value)) {
                $client = Client::findByAccountNumber($value);
                if ($client) return $client;
            }
        }

        // Passe 2 : recherche par champ dont le label évoque le numéro de compte
        foreach ($fields as $f) {
            $label = strtolower((string)($f['label'] ?? ''));
            $value = trim((string)($f['value'] ?? ''));
            if ((str_contains($label, 'compte') || str_contains($label, 'account'))
                && preg_match('/\d{6}/', $value, $m)) {
                $client = Client::findByAccountNumber($m[0]);
                if ($client) return $client;
            }
        }

        // Passe 3 : recherche par email
        foreach ($fields as $f) {
            $value = trim((string)($f['value'] ?? ''));
            if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $client = Client::findByEmail($value);
                if ($client) return $client;
            }
        }

        return null;
    }

    private function saveDocument(array $client, array $payload, array $data, string $responseId): void
    {
        $config  = require CONFIG_PATH;
        $baseDir = rtrim($config['storage']['documents'], '/') . '/tally';

        if (!is_dir($baseDir) && !mkdir($baseDir, 0755, true)) {
            throw new \RuntimeException('Impossible de créer le répertoire tally.');
        }

        $safeId   = preg_replace('/[^a-zA-Z0-9_-]/', '', $responseId) ?: bin2hex(random_bytes(8));
        $filename = 'tally_' . $safeId . '.json';
        $path     = $baseDir . '/' . $filename;
        $content  = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        if (file_put_contents($path, $content) === false) {
            throw new \RuntimeException('Impossible d'écrire le fichier.');
        }

        $formName     = (string)($data['formName'] ?? 'questionnaire');
        $originalName = $this->sanitizeFilename($formName) . '_' . $safeId . '.json';

        Document::create([
            'client_id'         => (int)$client['id'],
            'contract_id'       => null,
            'claim_id'          => null,
            'scope'             => 'contrat',
            'category'          => 'cotation',
            'doc_type'          => 'questionnaire',
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
        // Idempotence : si déjà en queue on ne re-crée pas
        if ($responseId && TallyQueue::responseExists($responseId)) {
            return;
        }

        TallyQueue::create([
            'event_id'    => (string)($payload['eventId'] ?? ''),
            'response_id' => $responseId ?: bin2hex(random_bytes(8)),
            'form_id'     => (string)($data['formId']   ?? '') ?: null,
            'form_name'   => (string)($data['formName'] ?? '') ?: null,
            'payload'     => $rawBody,
        ]);
    }

    private function sanitizeFilename(string $name): string
    {
        $name = preg_replace('/[^a-zA-Z0-9_\-]/u', '_', $name) ?? 'questionnaire';
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
