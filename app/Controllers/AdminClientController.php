<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Middleware\AdminMiddleware;
use App\Models\Client;
use App\Services\AuditLogger;

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
            'csrf'        => $this->csrfToken(),
            'credentials' => null,
            'old'         => [],
        ]);
    }

    public function create(): void
    {
        AdminMiddleware::check();
        $this->verifyCsrf();

        $firstName = trim($_POST['first_name'] ?? '');
        $lastName  = trim($_POST['last_name']  ?? '');
        $email     = trim(strtolower($_POST['email'] ?? ''));
        $phone     = trim($_POST['phone'] ?? '') ?: null;
        $status    = $_POST['status'] ?? 'actif';
        $old       = compact('firstName', 'lastName', 'email', 'phone', 'status');

        if (!$firstName || !$lastName || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->render('admin.clients.create', [
                'csrf'        => $this->csrfToken(),
                'credentials' => null,
                'old'         => $old,
                'error'       => 'Prénom, nom et adresse e-mail valide sont obligatoires.',
            ]);
            return;
        }

        if (!in_array($status, ['actif', 'inactif', 'suspendu'], true)) {
            $status = 'actif';
        }

        try {
            $accountNumber = Client::generateAccountNumber();
            $rawPassword   = $this->generatePassword();

            $id = Client::create([
                'account_number'       => $accountNumber,
                'first_name'           => $firstName,
                'last_name'            => $lastName,
                'email'                => $email,
                'phone'                => $phone,
                'password_hash'        => password_hash($rawPassword, PASSWORD_BCRYPT),
                'must_change_password' => 1,
                'status'               => $status,
            ]);

            AuditLogger::log('admin', (int)$_SESSION['admin_id'], 'client_created', "client:{$id}", $this->ip());

            $this->render('admin.clients.create', [
                'csrf'        => $this->csrfToken(),
                'credentials' => [
                    'account_number' => $accountNumber,
                    'password'       => $rawPassword,
                    'name'           => $firstName . ' ' . $lastName,
                    'email'          => $email,
                ],
                'old' => [],
            ]);
        } catch (\Throwable $e) {
            $this->render('admin.clients.create', [
                'csrf'        => $this->csrfToken(),
                'credentials' => null,
                'old'         => $old,
                'error'       => 'Erreur : ' . $e->getMessage(),
            ]);
        }
    }

    private function generatePassword(): string
    {
        $chars = 'ABCDEFGHJKMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789!@#';
        $pwd   = '';
        for ($i = 0; $i < 10; $i++) {
            $pwd .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $pwd;
    }
}
