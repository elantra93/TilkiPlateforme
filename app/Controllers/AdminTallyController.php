<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Middleware\AdminMiddleware;
use App\Models\Client;
use App\Models\Document;
use App\Models\TallyQueue;
use App\Services\AuditLogger;

class AdminTallyController extends BaseController
{
    public function queue(): void
    {
        AdminMiddleware::check();
        $this->render('admin.tally.queue', [
            'entries'      => TallyQueue::all(),
            'clients'      => Client::all(),
            'csrf'         => $this->csrfToken(),
            'pendingCount' => TallyQueue::pendingCount(),
        ]);
    }

    public function match(string $id): void
    {
        AdminMiddleware::check();
        $this->verifyCsrf();

        $entry    = TallyQueue::find((int)$id);
        $clientId = (int)($_POST['client_id'] ?? 0);
        $client   = $clientId ? Client::findById($clientId) : null;

        if (!$entry || !$client) {
            $_SESSION['admin_flash'] = ['type' => 'danger', 'msg' => 'Entrée ou client introuvable.'];
            $this->redirect('/admin/tally/queue');
            return;
        }

        if ($entry['status'] !== 'pending') {
            $_SESSION['admin_flash'] = ['type' => 'warning', 'msg' => 'Cette soumission est déjà traitée.'];
            $this->redirect('/admin/tally/queue');
            return;
        }

        try {
            $payload    = json_decode($entry['payload'], true) ?? [];
            $data       = $payload['data'] ?? [];
            $responseId = $entry['response_id'];

            $this->saveDocument($client, $payload, $data, $responseId);
            TallyQueue::match((int)$id, $clientId);

            AuditLogger::log('admin', (int)$_SESSION['admin_id'], 'tally_manual_match',
                "queue:{$id} client:{$clientId}", $this->ip());

            $_SESSION['admin_flash'] = ['type' => 'success', 'msg' => 'Soumission rattachée au client et document créé.'];
        } catch (\Throwable $e) {
            $_SESSION['admin_flash'] = ['type' => 'danger', 'msg' => 'Erreur : ' . $e->getMessage()];
        }

        $this->redirect('/admin/tally/queue');
    }

    public function ignore(string $id): void
    {
        AdminMiddleware::check();
        $this->verifyCsrf();

        $entry = TallyQueue::find((int)$id);
        if (!$entry) {
            $_SESSION['admin_flash'] = ['type' => 'danger', 'msg' => 'Entrée introuvable.'];
            $this->redirect('/admin/tally/queue');
            return;
        }

        TallyQueue::ignore((int)$id);
        AuditLogger::log('admin', (int)$_SESSION['admin_id'], 'tally_ignored',
            "queue:{$id}", $this->ip());

        $_SESSION['admin_flash'] = ['type' => 'success', 'msg' => 'Soumission ignorée.'];
        $this->redirect('/admin/tally/queue');
    }

    // ── Même logique que TallyWebhookController::saveDocument() ─────────────

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

        // Si le fichier existe déjà (soumission déjà traitée), on pointe dessus
        if (!file_exists($path)) {
            $content = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            if (file_put_contents($path, $content) === false) {
                throw new \RuntimeException('Impossible d'écrire le fichier.');
            }
        } else {
            $content = (string)file_get_contents($path);
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

    private function sanitizeFilename(string $name): string
    {
        $name = preg_replace('/[^a-zA-Z0-9_\-]/u', '_', $name) ?? 'questionnaire';
        return strtolower(substr($name, 0, 50));
    }
}
