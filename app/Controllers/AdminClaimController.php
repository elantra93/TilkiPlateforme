<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Middleware\AdminMiddleware;
use App\Models\Claim;
use App\Models\ClaimStep;
use App\Models\Client;
use App\Models\Contract;
use App\Models\Document;
use App\Services\AuditLogger;
use App\Services\FileStorage;

class AdminClaimController extends BaseController
{
    private const STATUSES = ['ouvert', 'clos'];

    private const DOC_TYPES = [
        'declaration'               => ['declaration_sinistre', 'rapport_circonstances', 'constat_amiable', 'plainte'],
        'expertise_devis'           => ['rapport_expertise', 'devis_reparation', 'contre_expertise', 'estimation_perte'],
        'correspondances'           => ['courrier_assureur', 'courrier_expert', 'courrier_client', 'mise_en_demeure'],
        'reglements_remboursements' => ['virement', 'cheque', 'quittance_reglement', 'decompte_indemnite'],
    ];

    public function index(): void
    {
        AdminMiddleware::check();
        $this->render('admin.claims.index', [
            'claims' => Claim::all(),
        ]);
    }

    public function showCreate(): void
    {
        AdminMiddleware::check();
        $this->render('admin.claims.form', [
            'csrf'      => $this->csrfToken(),
            'claim'     => null,
            'steps'     => [],
            'clients'   => Client::all(),
            'contracts' => Contract::all(),
            'old'       => [],
        ]);
    }

    public function create(): void
    {
        AdminMiddleware::check();
        $this->verifyCsrf();

        $data = $this->collectFields();
        $old  = $data;

        if (!$data['client_id'] || !$data['claim_number'] || !$data['insurer'] ||
            !$data['branche'] || !$data['occurrence_date']) {
            $this->render('admin.claims.form', [
                'csrf'      => $this->csrfToken(),
                'claim'     => null,
                'steps'     => [],
                'clients'   => Client::all(),
                'contracts' => Contract::all(),
                'old'       => $old,
                'error'     => 'Tous les champs obligatoires doivent être remplis.',
            ]);
            return;
        }

        try {
            $id = Claim::create($data);
            ClaimStep::initForClaim($id, (bool)$data['is_auto_rc']);
            AuditLogger::log('admin', (int)$_SESSION['admin_id'], 'claim_created', "claim:{$id}", $this->ip());
            $_SESSION['admin_flash'] = ['type' => 'success', 'msg' => 'Sinistre créé avec succès.'];
            $this->redirect('/admin/claims');
        } catch (\Throwable $e) {
            $this->render('admin.claims.form', [
                'csrf'      => $this->csrfToken(),
                'claim'     => null,
                'steps'     => [],
                'clients'   => Client::all(),
                'contracts' => Contract::all(),
                'old'       => $old,
                'error'     => 'Erreur : ' . $e->getMessage(),
            ]);
        }
    }

    public function showEdit(string $id): void
    {
        AdminMiddleware::check();
        $claim = Claim::find((int)$id);
        if (!$claim) {
            http_response_code(404);
            require APP_PATH . '/Views/errors/404.php';
            return;
        }

        // Ensure steps exist (lazy init for claims created before migration)
        ClaimStep::initForClaim((int)$id, (bool)$claim['is_auto_rc']);

        $this->render('admin.claims.form', [
            'csrf'      => $this->csrfToken(),
            'claim'     => $claim,
            'steps'     => ClaimStep::forClaim((int)$id),
            'documents' => Document::forClaimAdmin((int)$id),
            'docTypes'  => self::DOC_TYPES,
            'clients'   => Client::all(),
            'contracts' => Contract::all(),
            'old'       => [],
        ]);
    }

    public function edit(string $id): void
    {
        AdminMiddleware::check();
        $this->verifyCsrf();

        $claim = Claim::find((int)$id);
        if (!$claim) {
            http_response_code(404);
            require APP_PATH . '/Views/errors/404.php';
            return;
        }

        $data = $this->collectFields();
        unset($data['client_id']); // client non modifiable

        try {
            $oldAutoRc = (bool)$claim['is_auto_rc'];
            $newAutoRc = (bool)$data['is_auto_rc'];

            Claim::update((int)$id, $data);

            if ($oldAutoRc !== $newAutoRc) {
                ClaimStep::rebuildForClaim((int)$id, $newAutoRc);
            }

            AuditLogger::log('admin', (int)$_SESSION['admin_id'], 'claim_updated', "claim:{$id}", $this->ip());
            $_SESSION['admin_flash'] = ['type' => 'success', 'msg' => 'Sinistre mis à jour.'];
            $this->redirect('/admin/claims/' . $id . '/edit');
        } catch (\Throwable $e) {
            $this->render('admin.claims.form', [
                'csrf'      => $this->csrfToken(),
                'claim'     => $claim,
                'steps'     => ClaimStep::forClaim((int)$id),
                'clients'   => Client::all(),
                'contracts' => Contract::all(),
                'old'       => $data,
                'error'     => 'Erreur : ' . $e->getMessage(),
            ]);
        }
    }

    public function updateStep(string $claimId, string $stepId): void
    {
        AdminMiddleware::check();
        $this->verifyCsrf();

        $claim = Claim::find((int)$claimId);
        $step  = ClaimStep::find((int)$stepId);

        if (!$claim || !$step || (int)$step['claim_id'] !== (int)$claimId) {
            http_response_code(404);
            require APP_PATH . '/Views/errors/404.php';
            return;
        }

        $completed     = isset($_POST['completed']);
        $completedDate = trim($_POST['completed_date'] ?? '');
        if ($completed && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $completedDate)) {
            $completedDate = date('Y-m-d');
        }

        ClaimStep::update((int)$stepId, $completed, $completed ? $completedDate : null);
        AuditLogger::log('admin', (int)$_SESSION['admin_id'], 'claim_step_updated', "claim:{$claimId}/step:{$stepId}", $this->ip());

        $_SESSION['admin_flash'] = ['type' => 'success', 'msg' => 'Étape mise à jour.'];
        $this->redirect('/admin/claims/' . $claimId . '/edit');
    }

    public function uploadDoc(string $id): void
    {
        AdminMiddleware::check();
        $this->verifyCsrf();

        $claim = Claim::find((int)$id);
        if (!$claim) {
            http_response_code(404);
            require APP_PATH . '/Views/errors/404.php';
            return;
        }

        $category = $_POST['category'] ?? '';
        $docType  = trim($_POST['doc_type'] ?? '');

        if (!array_key_exists($category, self::DOC_TYPES) || !$docType) {
            $_SESSION['admin_flash'] = ['type' => 'danger', 'msg' => 'Catégorie ou type de document manquant.'];
            $this->redirect('/admin/claims/' . $id . '/edit');
            return;
        }

        if (empty($_FILES['document']) || $_FILES['document']['error'] === UPLOAD_ERR_NO_FILE) {
            $_SESSION['admin_flash'] = ['type' => 'danger', 'msg' => 'Aucun fichier sélectionné.'];
            $this->redirect('/admin/claims/' . $id . '/edit');
            return;
        }

        try {
            $stored = FileStorage::store($_FILES['document'], 'sinistres/' . $id);
            Document::create([
                'client_id'         => $claim['client_id'],
                'contract_id'       => $claim['contract_id'],
                'claim_id'          => (int)$id,
                'scope'             => 'sinistre',
                'category'          => $category,
                'doc_type'          => $docType,
                'original_filename' => $stored['original_filename'],
                'stored_path'       => $stored['stored_path'],
                'mime_type'         => $stored['mime_type'],
                'file_size'         => $stored['file_size'],
                'source'            => 'admin',
                'status'            => 'valide',
            ]);
            AuditLogger::log('admin', (int)$_SESSION['admin_id'], 'claim_doc_upload', "claim:{$id}", $this->ip());
            $_SESSION['admin_flash'] = ['type' => 'success', 'msg' => 'Document ajouté au sinistre.'];
        } catch (\Throwable $e) {
            $_SESSION['admin_flash'] = ['type' => 'danger', 'msg' => $e->getMessage()];
        }

        $this->redirect('/admin/claims/' . $id . '/edit');
    }

    private function collectFields(): array
    {
        $status     = $_POST['status'] ?? 'ouvert';
        $contractId = (int)($_POST['contract_id'] ?? 0) ?: null;
        return [
            'client_id'       => (int)($_POST['client_id'] ?? 0),
            'contract_id'     => $contractId,
            'claim_number'    => trim($_POST['claim_number']    ?? ''),
            'insurer'         => trim($_POST['insurer']          ?? ''),
            'branche'         => trim($_POST['branche']          ?? ''),
            'occurrence_date' => $_POST['occurrence_date']  ?? '',
            'status'          => in_array($status, self::STATUSES, true) ? $status : 'ouvert',
            'description'     => trim($_POST['description'] ?? '') ?: null,
            'is_auto_rc'      => isset($_POST['is_auto_rc']) ? 1 : 0,
        ];
    }
}
