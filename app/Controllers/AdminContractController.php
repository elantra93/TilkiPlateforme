<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Middleware\AdminMiddleware;
use App\Models\Client;
use App\Models\Contract;
use App\Services\AuditLogger;

class AdminContractController extends BaseController
{
    private const STATUSES  = ['actif', 'expiré', 'résilié', 'suspendu'];

    public function index(): void
    {
        AdminMiddleware::check();
        $this->render('admin.contracts.index', [
            'contracts' => Contract::all(),
        ]);
    }

    public function showCreate(): void
    {
        AdminMiddleware::check();
        $this->render('admin.contracts.form', [
            'csrf'     => $this->csrfToken(),
            'contract' => null,
            'clients'  => Client::all(),
            'old'      => [],
        ]);
    }

    public function create(): void
    {
        AdminMiddleware::check();
        $this->verifyCsrf();

        $data = $this->collectFields();
        $old  = $data;

        if (!$data['client_id'] || !$data['branche'] || !$data['policy_number'] ||
            !$data['insurer'] || !$data['effective_date'] || !$data['expiry_date']) {
            $this->render('admin.contracts.form', [
                'csrf'     => $this->csrfToken(),
                'contract' => null,
                'clients'  => Client::all(),
                'old'      => $old,
                'error'    => 'Tous les champs obligatoires doivent être remplis.',
            ]);
            return;
        }

        try {
            $id = Contract::create($data);
            AuditLogger::log('admin', (int)$_SESSION['admin_id'], 'contract_created', "contract:{$id}", $this->ip());
            $_SESSION['admin_flash'] = ['type' => 'success', 'msg' => 'Contrat créé avec succès.'];
            $this->redirect('/admin/contracts');
        } catch (\Throwable $e) {
            $this->render('admin.contracts.form', [
                'csrf'     => $this->csrfToken(),
                'contract' => null,
                'clients'  => Client::all(),
                'old'      => $old,
                'error'    => 'Erreur : ' . $e->getMessage(),
            ]);
        }
    }

    public function showEdit(string $id): void
    {
        AdminMiddleware::check();
        $contract = Contract::find((int)$id);
        if (!$contract) {
            http_response_code(404);
            require APP_PATH . '/Views/errors/404.php';
            return;
        }
        $this->render('admin.contracts.form', [
            'csrf'     => $this->csrfToken(),
            'contract' => $contract,
            'clients'  => Client::all(),
            'old'      => [],
        ]);
    }

    public function edit(string $id): void
    {
        AdminMiddleware::check();
        $this->verifyCsrf();

        $contract = Contract::find((int)$id);
        if (!$contract) {
            http_response_code(404);
            require APP_PATH . '/Views/errors/404.php';
            return;
        }

        $data = $this->collectFields();
        unset($data['client_id']); // client non modifiable après création

        try {
            Contract::update((int)$id, $data);
            AuditLogger::log('admin', (int)$_SESSION['admin_id'], 'contract_updated', "contract:{$id}", $this->ip());
            $_SESSION['admin_flash'] = ['type' => 'success', 'msg' => 'Contrat mis à jour.'];
            $this->redirect('/admin/contracts');
        } catch (\Throwable $e) {
            $this->render('admin.contracts.form', [
                'csrf'     => $this->csrfToken(),
                'contract' => $contract,
                'clients'  => Client::all(),
                'old'      => $data,
                'error'    => 'Erreur : ' . $e->getMessage(),
            ]);
        }
    }

    private function collectFields(): array
    {
        $status = $_POST['status'] ?? 'actif';
        return [
            'client_id'      => (int)($_POST['client_id'] ?? 0),
            'branche'        => trim($_POST['branche']        ?? ''),
            'policy_number'  => trim($_POST['policy_number']  ?? ''),
            'insurer'        => trim($_POST['insurer']         ?? ''),
            'effective_date' => $_POST['effective_date']  ?? '',
            'expiry_date'    => $_POST['expiry_date']     ?? '',
            'premium_total'  => (float)($_POST['premium_total'] ?? 0),
            'premium_due'    => (float)($_POST['premium_due']   ?? 0),
            'currency'       => strtoupper(trim($_POST['currency'] ?? 'XOF')) ?: 'XOF',
            'status'         => in_array($status, self::STATUSES, true) ? $status : 'actif',
        ];
    }
}
