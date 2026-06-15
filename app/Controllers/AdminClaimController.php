<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Middleware\AdminMiddleware;
use App\Models\Claim;
use App\Models\ClaimStep;
use App\Models\Client;
use App\Models\Contract;
use App\Services\AuditLogger;

class AdminClaimController extends BaseController
{
    private const STATUSES = ['ouvert', 'clos'];

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
