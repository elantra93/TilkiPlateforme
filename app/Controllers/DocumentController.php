<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Models\Claim;
use App\Models\Document;
use App\Services\AuditLogger;
use App\Services\FileStorage;

class DocumentController extends BaseController
{
    public function download(string $id): void
    {
        $this->requireAuth();
        $clientId = (int)$_SESSION['client_id'];
        $doc      = Document::findForClient((int)$id, $clientId);

        if (!$doc) {
            http_response_code(403);
            require APP_PATH . '/Views/errors/403.php';
            return;
        }

        AuditLogger::log('client', $clientId, 'download', "document:{$id}", $this->ip());
        FileStorage::serve($doc['stored_path'], $doc['original_filename'], $doc['mime_type']);
    }

    public function upload(string $claimId): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $clientId = (int)$_SESSION['client_id'];
        $claim    = Claim::findForClient((int)$claimId, $clientId);

        if (!$claim) {
            http_response_code(403);
            require APP_PATH . '/Views/errors/403.php';
            return;
        }

        if (empty($_FILES['document']) || $_FILES['document']['error'] === UPLOAD_ERR_NO_FILE) {
            $this->redirect('/claims/' . $claimId);
            return;
        }

        try {
            $stored = FileStorage::store($_FILES['document'], 'sinistres/' . $claimId);
            Document::create([
                'client_id'         => $clientId,
                'contract_id'       => $claim['contract_id'],
                'claim_id'          => (int)$claimId,
                'scope'             => 'sinistre',
                'category'          => 'souscription',
                'doc_type'          => 'preuve_reglement',
                'original_filename' => $stored['original_filename'],
                'stored_path'       => $stored['stored_path'],
                'mime_type'         => $stored['mime_type'],
                'file_size'         => $stored['file_size'],
                'source'            => 'client',
                'status'            => 'en_attente',
            ]);

            AuditLogger::log('client', $clientId, 'upload', "claim:{$claimId}", $this->ip());
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Document déposé avec succès.'];
        } catch (\Throwable $e) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => $e->getMessage()];
        }

        $this->redirect('/claims/' . $claimId);
    }
}
