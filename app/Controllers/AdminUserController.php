<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Middleware\AdminMiddleware;
use App\Models\Admin;
use App\Services\AuditLogger;

class AdminUserController extends BaseController
{
    private const ROLES = ['admin' => 'Admin', 'support' => 'Support'];

    private function requireSuperadmin(): void
    {
        AdminMiddleware::check();
        if (($_SESSION['admin_role'] ?? '') !== 'superadmin') {
            http_response_code(403);
            require APP_PATH . '/Views/errors/403.php';
            exit;
        }
    }

    public function index(): void
    {
        $this->requireSuperadmin();
        $this->render('admin.admins.index', [
            'admins' => Admin::all(),
        ]);
    }

    public function showCreate(): void
    {
        $this->requireSuperadmin();
        $this->render('admin.admins.form', [
            'csrf'  => $this->csrfToken(),
            'admin' => null,
            'old'   => [],
            'roles' => self::ROLES,
        ]);
    }

    public function create(): void
    {
        $this->requireSuperadmin();
        $this->verifyCsrf();

        $name     = trim($_POST['name']             ?? '');
        $email    = trim(strtolower($_POST['email'] ?? ''));
        $role     = $_POST['role']                  ?? 'admin';
        $password = $_POST['password']              ?? '';
        $confirm  = $_POST['confirm_password']      ?? '';
        $old      = compact('name', 'email', 'role');

        $renderForm = fn(string $error) => $this->render('admin.admins.form', [
            'csrf'  => $this->csrfToken(),
            'admin' => null,
            'old'   => $old,
            'roles' => self::ROLES,
            'error' => $error,
        ]);

        if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $renderForm('Nom et adresse e-mail valide sont obligatoires.');
            return;
        }
        if (!array_key_exists($role, self::ROLES)) {
            $renderForm('Rôle invalide.');
            return;
        }
        if (strlen($password) < 8) {
            $renderForm('Le mot de passe doit contenir au moins 8 caractères.');
            return;
        }
        if ($password !== $confirm) {
            $renderForm('Les mots de passe ne correspondent pas.');
            return;
        }
        if (Admin::isEmailTaken($email)) {
            $renderForm("L'adresse e-mail « {$email} » est déjà utilisée.");
            return;
        }

        $id = Admin::create([
            'email'         => $email,
            'password_hash' => password_hash($password, PASSWORD_BCRYPT),
            'name'          => $name,
            'role'          => $role,
        ]);

        AuditLogger::log('admin', (int)$_SESSION['admin_id'], 'admin_user_created', "admin:{$id}", $this->ip());
        $_SESSION['admin_flash'] = ['type' => 'success', 'msg' => "Compte « {$name} » créé avec succès."];
        $this->redirect('/admin/admins');
    }

    public function showEdit(string $id): void
    {
        $this->requireSuperadmin();
        $admin = Admin::findById((int)$id);
        if (!$admin || $admin['role'] === 'superadmin') {
            http_response_code(404);
            require APP_PATH . '/Views/errors/404.php';
            return;
        }
        $this->render('admin.admins.form', [
            'csrf'  => $this->csrfToken(),
            'admin' => $admin,
            'old'   => [],
            'roles' => self::ROLES,
        ]);
    }

    public function edit(string $id): void
    {
        $this->requireSuperadmin();
        $this->verifyCsrf();

        $admin = Admin::findById((int)$id);
        if (!$admin || $admin['role'] === 'superadmin') {
            http_response_code(404);
            require APP_PATH . '/Views/errors/404.php';
            return;
        }

        $name    = trim($_POST['name']              ?? '');
        $email   = trim(strtolower($_POST['email']) ?? '');
        $role    = $_POST['role']                   ?? 'admin';
        $old     = compact('name', 'email', 'role');

        $renderForm = fn(string $error) => $this->render('admin.admins.form', [
            'csrf'  => $this->csrfToken(),
            'admin' => $admin,
            'old'   => $old,
            'roles' => self::ROLES,
            'error' => $error,
        ]);

        if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $renderForm('Nom et adresse e-mail valide sont obligatoires.');
            return;
        }
        if (!array_key_exists($role, self::ROLES)) {
            $renderForm('Rôle invalide.');
            return;
        }
        if (Admin::isEmailTaken($email, (int)$id)) {
            $renderForm("L'adresse e-mail « {$email} » est déjà utilisée.");
            return;
        }

        Admin::update((int)$id, ['name' => $name, 'email' => $email, 'role' => $role]);

        $newPassword = $_POST['new_password'] ?? '';
        if ($newPassword !== '') {
            if (strlen($newPassword) < 8) {
                $renderForm('Le nouveau mot de passe doit contenir au moins 8 caractères.');
                return;
            }
            Admin::updatePassword((int)$id, password_hash($newPassword, PASSWORD_BCRYPT));
        }

        AuditLogger::log('admin', (int)$_SESSION['admin_id'], 'admin_user_updated', "admin:{$id}", $this->ip());
        $_SESSION['admin_flash'] = ['type' => 'success', 'msg' => 'Compte mis à jour.'];
        $this->redirect('/admin/admins');
    }
}
