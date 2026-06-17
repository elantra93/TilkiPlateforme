<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Models\Claim;
use App\Models\Contract;
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

    public function view(string $id): void
    {
        $this->requireAuth();
        $clientId = (int)$_SESSION['client_id'];
        $doc      = Document::findForClient((int)$id, $clientId);

        if (!$doc) {
            http_response_code(403);
            require APP_PATH . '/Views/errors/403.php';
            return;
        }

        $real    = realpath($doc['stored_path']);
        $baseDir = realpath(ROOT_PATH . '/storage');

        if ($real === false || $baseDir === false || strncmp($real, $baseDir, strlen($baseDir)) !== 0) {
            http_response_code(404);
            exit('Fichier introuvable.');
        }

        AuditLogger::log('client', $clientId, 'view', "document:{$id}", $this->ip());
        header('Content-Type: '        . $doc['mime_type']);
        header('Content-Disposition: inline; filename="' . rawurlencode($doc['original_filename']) . '"');
        header('Content-Length: '      . filesize($real));
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, no-store');
        readfile($real);
        exit;
    }

    private const CLIENT_DOC_TYPES = [
        'declaration_sinistre' => 'declaration',
        'devis_reparation'     => 'expertise_devis',
        'rapport_expertise'    => 'expertise_devis',
        'constat_police'       => 'expertise_devis',
        'courrier_client'      => 'correspondances',
    ];

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

        $docType = $_POST['doc_type'] ?? '';
        if (!array_key_exists($docType, self::CLIENT_DOC_TYPES)) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Type de document invalide.'];
            $this->redirect('/claims/' . $claimId);
            return;
        }

        if (empty($_FILES['document']) || $_FILES['document']['error'] === UPLOAD_ERR_NO_FILE) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Aucun fichier sélectionné.'];
            $this->redirect('/claims/' . $claimId);
            return;
        }

        try {
            $mime = mime_content_type($_FILES['document']['tmp_name']);
            if (!in_array($mime, ['application/pdf', 'image/jpeg', 'image/png', 'image/gif'], true)) {
                throw new \RuntimeException('Seuls les formats PDF, JPG et PNG sont acceptés.');
            }

            $stored = FileStorage::store($_FILES['document'], 'sinistres/' . $claimId);
            Document::create([
                'client_id'         => $clientId,
                'contract_id'       => $claim['contract_id'],
                'claim_id'          => (int)$claimId,
                'scope'             => 'sinistre',
                'category'          => self::CLIENT_DOC_TYPES[$docType],
                'doc_type'          => $docType,
                'original_filename' => $stored['original_filename'],
                'stored_path'       => $stored['stored_path'],
                'mime_type'         => $stored['mime_type'],
                'file_size'         => $stored['file_size'],
                'source'            => 'client',
                'status'            => 'en_attente',
            ]);

            AuditLogger::log('client', $clientId, 'claim_upload', "claim:{$claimId} type:{$docType}", $this->ip());
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Document déposé. Il sera visible après validation par TILKI.'];
        } catch (\Throwable $e) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => $e->getMessage()];
        }

        $this->redirect('/claims/' . $claimId);
    }

    public function uploadContract(string $contractId): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $clientId = (int)$_SESSION['client_id'];
        $contract = Contract::findForClient((int)$contractId, $clientId);

        if (!$contract) {
            http_response_code(403);
            require APP_PATH . '/Views/errors/403.php';
            return;
        }

        if (empty($_FILES['document']) || $_FILES['document']['error'] === UPLOAD_ERR_NO_FILE) {
            $this->redirect('/contracts/' . $contractId);
            return;
        }

        try {
            $mime = mime_content_type($_FILES['document']['tmp_name']);
            if (!in_array($mime, ['application/pdf', 'image/jpeg', 'image/png'], true)) {
                throw new \RuntimeException('Seuls les formats PDF, JPG et PNG sont acceptés.');
            }

            $stored = FileStorage::store($_FILES['document'], 'contrats/' . $contractId);
            Document::create([
                'client_id'         => $clientId,
                'contract_id'       => (int)$contractId,
                'claim_id'          => null,
                'scope'             => 'contrat',
                'category'          => 'souscription',
                'doc_type'          => 'preuve_reglement',
                'original_filename' => $stored['original_filename'],
                'stored_path'       => $stored['stored_path'],
                'mime_type'         => $stored['mime_type'],
                'file_size'         => $stored['file_size'],
                'source'            => 'client',
                'status'            => 'en_attente',
            ]);

            AuditLogger::log('client', $clientId, 'upload', "contract:{$contractId}", $this->ip());
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Document déposé, il sera validé par TILKI.'];
        } catch (\Throwable $e) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => $e->getMessage()];
        }

        $this->redirect('/contracts/' . $contractId);
    }
}
