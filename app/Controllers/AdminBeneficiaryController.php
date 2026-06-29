<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Middleware\AdminMiddleware;
use App\Models\Beneficiary;
use App\Models\Contract;
use App\Services\AuditLogger;

class AdminBeneficiaryController extends BaseController
{
    public function create(string $contractId): void
    {
        AdminMiddleware::check();
        $this->verifyCsrf();

        $contract = Contract::find((int)$contractId);
        if (!$contract) {
            http_response_code(404);
            require APP_PATH . '/Views/errors/404.php';
            return;
        }

        $data = $this->extractPost();
        if (!$data['first_name'] || !$data['last_name']) {
            $_SESSION['admin_flash'] = ['type' => 'danger', 'msg' => 'Le prénom et le nom sont obligatoires.'];
            $this->redirect('/admin/contracts/' . $contractId . '/edit');
            return;
        }

        $data['contract_id'] = (int)$contractId;
        $data['client_id']   = (int)$contract['client_id'];

        $id = Beneficiary::create($data);
        AuditLogger::log('admin', (int)$_SESSION['admin_id'], 'beneficiary_created',
            "contract:{$contractId} beneficiary:{$id}", $this->ip());

        $name = htmlspecialchars($data['first_name'] . ' ' . $data['last_name']);
        $_SESSION['admin_flash'] = ['type' => 'success', 'msg' => "Bénéficiaire {$name} ajouté."];
        $this->redirect('/admin/contracts/' . $contractId . '/edit');
    }

    public function showEdit(string $id): void
    {
        AdminMiddleware::check();
        $beneficiary = Beneficiary::find((int)$id);
        if (!$beneficiary) {
            http_response_code(404);
            require APP_PATH . '/Views/errors/404.php';
            return;
        }
        $contract = Contract::find((int)$beneficiary['contract_id']);
        $this->render('admin.beneficiaries.edit', [
            'csrf'        => $this->csrfToken(),
            'beneficiary' => $beneficiary,
            'contract'    => $contract,
            'old'         => [],
        ]);
    }

    public function edit(string $id): void
    {
        AdminMiddleware::check();
        $this->verifyCsrf();

        $beneficiary = Beneficiary::find((int)$id);
        if (!$beneficiary) {
            http_response_code(404);
            require APP_PATH . '/Views/errors/404.php';
            return;
        }

        $data = $this->extractPost();
        if (!$data['first_name'] || !$data['last_name']) {
            $contract = Contract::find((int)$beneficiary['contract_id']);
            $this->render('admin.beneficiaries.edit', [
                'csrf'        => $this->csrfToken(),
                'beneficiary' => $beneficiary,
                'contract'    => $contract,
                'old'         => $data,
                'error'       => 'Le prénom et le nom sont obligatoires.',
            ]);
            return;
        }

        Beneficiary::update((int)$id, $data);
        AuditLogger::log('admin', (int)$_SESSION['admin_id'], 'beneficiary_updated',
            "beneficiary:{$id}", $this->ip());
        $_SESSION['admin_flash'] = ['type' => 'success', 'msg' => 'Bénéficiaire mis à jour.'];
        $this->redirect('/admin/contracts/' . $beneficiary['contract_id'] . '/edit');
    }

    public function delete(string $id): void
    {
        AdminMiddleware::check();
        $this->verifyCsrf();

        $beneficiary = Beneficiary::find((int)$id);
        if (!$beneficiary) {
            http_response_code(404);
            require APP_PATH . '/Views/errors/404.php';
            return;
        }

        $contractId = $beneficiary['contract_id'];
        Beneficiary::delete((int)$id);
        AuditLogger::log('admin', (int)$_SESSION['admin_id'], 'beneficiary_deleted',
            "beneficiary:{$id} contract:{$contractId}", $this->ip());
        $_SESSION['admin_flash'] = ['type' => 'success', 'msg' => 'Bénéficiaire supprimé.'];
        $this->redirect('/admin/contracts/' . $contractId . '/edit');
    }

    private function extractPost(): array
    {
        $gender    = trim($_POST['gender']   ?? '');
        $relation  = trim($_POST['relation'] ?? '');
        $birthDate = trim($_POST['birth_date'] ?? '');
        return [
            'first_name'   => trim($_POST['first_name']  ?? ''),
            'last_name'    => strtoupper(trim($_POST['last_name'] ?? '')),
            'birth_date'   => $birthDate ?: null,
            'gender'       => array_key_exists($gender, Beneficiary::GENDERS) ? $gender : null,
            'relation'     => in_array($relation, Beneficiary::RELATIONS, true) ? $relation : 'autre',
            'is_principal' => isset($_POST['is_principal']) ? 1 : 0,
            'matricule'    => trim($_POST['matricule'] ?? '') ?: null,
        ];
    }
}
