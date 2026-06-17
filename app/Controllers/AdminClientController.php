<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Middleware\AdminMiddleware;
use App\Models\Client;
use App\Models\Document;
use App\Services\AuditLogger;
use App\Services\FileStorage;

class AdminClientController extends BaseController
{
    public function index(): void
    {
        AdminMiddleware::check();
        $this->render('admin.clients.index', [
            'clients' => Client::all(),
        ]);
    }

    public function showCreate(): void
    {
        AdminMiddleware::check();
        $this->render('admin.clients.create', [
            'csrf'               => $this->csrfToken(),
            'credentials'        => null,
            'old'                => [],
            'nextAccountNumber'  => Client::nextAccountNumber(),
        ]);
    }

    public function create(): void
    {
        AdminMiddleware::check();
        $this->verifyCsrf();

        $firstName     = trim($_POST['first_name']     ?? '');
        $lastName      = trim($_POST['last_name']      ?? '');
        $email         = trim(strtolower($_POST['email'] ?? ''));
        $phone         = trim($_POST['phone']          ?? '') ?: null;
        $status        = $_POST['status']              ?? 'actif';
        $accountNumber = trim($_POST['account_number'] ?? '');
        $old           = compact('firstName', 'lastName', 'email', 'phone', 'status', 'accountNumber');

        if (!$firstName || !$lastName || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->render('admin.clients.create', [
                'csrf'              => $this->csrfToken(),
                'credentials'       => null,
                'old'               => $old,
                'nextAccountNumber' => Client::nextAccountNumber(),
                'error'             => 'Prénom, nom et adresse e-mail valide sont obligatoires.',
            ]);
            return;
        }

        if (!preg_match('/^\d{6}$/', $accountNumber)) {
            $this->render('admin.clients.create', [
                'csrf'              => $this->csrfToken(),
                'credentials'       => null,
                'old'               => $old,
                'nextAccountNumber' => Client::nextAccountNumber(),
                'error'             => 'Le numéro de compte doit contenir exactement 6 chiffres.',
            ]);
            return;
        }

        if (Client::isAccountNumberTaken($accountNumber)) {
            $this->render('admin.clients.create', [
                'csrf'              => $this->csrfToken(),
                'credentials'       => null,
                'old'               => $old,
                'nextAccountNumber' => Client::nextAccountNumber(),
                'error'             => "Le numéro de compte « {$accountNumber} » est déjà utilisé. Un nouveau numéro a été suggéré.",
            ]);
            return;
        }

        if (!in_array($status, ['actif', 'inactif', 'suspendu'], true)) {
            $status = 'actif';
        }

        try {
            $id = Client::create([
                'account_number'       => $accountNumber,
                'first_name'           => $firstName,
                'last_name'            => $lastName,
                'email'                => $email,
                'phone'                => $phone,
                'password_hash'        => password_hash('12345678', PASSWORD_BCRYPT),
                'must_change_password' => 1,
                'status'               => $status,
            ]);

            AuditLogger::log('admin', (int)$_SESSION['admin_id'], 'client_created', "client:{$id}", $this->ip());

            $this->render('admin.clients.create', [
                'csrf'        => $this->csrfToken(),
                'credentials' => [
                    'account_number' => $accountNumber,
                    'pin'            => '12345678',
                    'name'           => $firstName . ' ' . $lastName,
                    'email'          => $email,
                ],
                'old' => [],
            ]);
        } catch (\Throwable $e) {
            $this->render('admin.clients.create', [
                'csrf'              => $this->csrfToken(),
                'credentials'       => null,
                'old'               => $old,
                'nextAccountNumber' => Client::nextAccountNumber(),
                'error'             => 'Erreur : ' . $e->getMessage(),
            ]);
        }
    }

    public function showEdit(string $id): void
    {
        AdminMiddleware::check();
        $client = Client::findById((int)$id);
        if (!$client) {
            http_response_code(404);
            require APP_PATH . '/Views/errors/404.php';
            return;
        }
        $this->render('admin.clients.edit', [
            'csrf'   => $this->csrfToken(),
            'client' => $client,
            'carte'  => Document::carteAssurance((int)$id),
        ]);
    }

    public function uploadCarte(string $id): void
    {
        AdminMiddleware::check();
        $this->verifyCsrf();

        $client = Client::findById((int)$id);
        if (!$client) {
            http_response_code(404);
            require APP_PATH . '/Views/errors/404.php';
            return;
        }

        if (empty($_FILES['document']) || $_FILES['document']['error'] === UPLOAD_ERR_NO_FILE) {
            $_SESSION['admin_flash'] = ['type' => 'danger', 'msg' => 'Aucun fichier sélectionné.'];
            $this->redirect('/admin/clients/' . $id . '/edit');
            return;
        }

        try {
            $mime = mime_content_type($_FILES['document']['tmp_name']);
            if (!in_array($mime, ['application/pdf', 'image/jpeg', 'image/png'], true)) {
                throw new \RuntimeException('Seuls les formats PDF, JPG et PNG sont acceptés.');
            }

            Document::archiveCarteAssurance((int)$id);

            $stored = FileStorage::store($_FILES['document'], 'clients/' . $id);
            Document::create([
                'client_id'         => (int)$id,
                'contract_id'       => null,
                'claim_id'          => null,
                'scope'             => 'carte',
                'category'          => 'carte',
                'doc_type'          => 'carte_assurance',
                'original_filename' => $stored['original_filename'],
                'stored_path'       => $stored['stored_path'],
                'mime_type'         => $stored['mime_type'],
                'file_size'         => $stored['file_size'],
                'source'            => 'admin',
                'status'            => 'valide',
            ]);

            AuditLogger::log('admin', (int)$_SESSION['admin_id'], 'carte_uploaded', "client:{$id}", $this->ip());
            $_SESSION['admin_flash'] = ['type' => 'success', 'msg' => "Carte d'assurance uploadée avec succès."];
        } catch (\Throwable $e) {
            $_SESSION['admin_flash'] = ['type' => 'danger', 'msg' => $e->getMessage()];
        }

        $this->redirect('/admin/clients/' . $id . '/edit');
    }
}
