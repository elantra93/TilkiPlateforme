<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Middleware\AdminMiddleware;
use App\Models\Contract;
use App\Models\Vehicle;
use App\Services\AuditLogger;

class AdminVehicleController extends BaseController
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
        if (!$data['immatriculation'] || !$data['marque']) {
            $_SESSION['admin_flash'] = ['type' => 'danger', 'msg' => "L'immatriculation et la marque sont obligatoires."];
            $this->redirect('/admin/contracts/' . $contractId . '/edit');
            return;
        }

        $data['contract_id'] = (int)$contractId;
        $data['client_id']   = (int)$contract['client_id'];

        $id = Vehicle::create($data);
        AuditLogger::log('admin', (int)$_SESSION['admin_id'], 'vehicle_created',
            "contract:{$contractId} vehicle:{$id}", $this->ip());
        $_SESSION['admin_flash'] = ['type' => 'success', 'msg' => 'Véhicule ' . htmlspecialchars($data['immatriculation']) . ' ajouté.'];
        $this->redirect('/admin/contracts/' . $contractId . '/edit');
    }

    public function showEdit(string $id): void
    {
        AdminMiddleware::check();
        $vehicle = Vehicle::find((int)$id);
        if (!$vehicle) {
            http_response_code(404);
            require APP_PATH . '/Views/errors/404.php';
            return;
        }
        $contract = Contract::find((int)$vehicle['contract_id']);
        $this->render('admin.vehicles.edit', [
            'csrf'     => $this->csrfToken(),
            'vehicle'  => $vehicle,
            'contract' => $contract,
            'old'      => [],
        ]);
    }

    public function edit(string $id): void
    {
        AdminMiddleware::check();
        $this->verifyCsrf();

        $vehicle = Vehicle::find((int)$id);
        if (!$vehicle) {
            http_response_code(404);
            require APP_PATH . '/Views/errors/404.php';
            return;
        }

        $data = $this->extractPost();
        if (!$data['immatriculation'] || !$data['marque']) {
            $contract = Contract::find((int)$vehicle['contract_id']);
            $this->render('admin.vehicles.edit', [
                'csrf'     => $this->csrfToken(),
                'vehicle'  => $vehicle,
                'contract' => $contract,
                'old'      => $data,
                'error'    => "L'immatriculation et la marque sont obligatoires.",
            ]);
            return;
        }

        Vehicle::update((int)$id, $data);
        AuditLogger::log('admin', (int)$_SESSION['admin_id'], 'vehicle_updated',
            "vehicle:{$id}", $this->ip());
        $_SESSION['admin_flash'] = ['type' => 'success', 'msg' => 'Véhicule mis à jour.'];
        $this->redirect('/admin/contracts/' . $vehicle['contract_id'] . '/edit');
    }

    public function delete(string $id): void
    {
        AdminMiddleware::check();
        $this->verifyCsrf();

        $vehicle = Vehicle::find((int)$id);
        if (!$vehicle) {
            http_response_code(404);
            require APP_PATH . '/Views/errors/404.php';
            return;
        }

        $contractId = $vehicle['contract_id'];
        Vehicle::delete((int)$id);
        AuditLogger::log('admin', (int)$_SESSION['admin_id'], 'vehicle_deleted',
            "vehicle:{$id} contract:{$contractId}", $this->ip());
        $_SESSION['admin_flash'] = ['type' => 'success', 'msg' => 'Véhicule supprimé.'];
        $this->redirect('/admin/contracts/' . $contractId . '/edit');
    }

    private function extractPost(): array
    {
        $energie = trim($_POST['energie'] ?? '');
        $usage   = trim($_POST['usage']   ?? '');
        $annee   = trim($_POST['annee']   ?? '');
        $valeur  = trim($_POST['valeur_venale'] ?? '');
        return [
            'immatriculation' => strtoupper(trim($_POST['immatriculation'] ?? '')),
            'marque'          => trim($_POST['marque']  ?? ''),
            'modele'          => trim($_POST['modele']  ?? '') ?: null,
            'annee'           => $annee ? (int)$annee : null,
            'energie'         => in_array($energie, Vehicle::ENERGIES, true) ? $energie : null,
            'usage'           => in_array($usage, Vehicle::USAGES, true) ? $usage : 'personnel',
            'valeur_venale'   => $valeur !== '' ? (float)$valeur : null,
        ];
    }
}
