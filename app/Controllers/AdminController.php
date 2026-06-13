<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Middleware\AdminMiddleware;
use App\Models\Admin;
use App\Models\LoginAttempt;
use App\Services\AuditLogger;
use App\Services\Database;

class AdminController extends BaseController
{
    private const MAX_ATTEMPTS    = 5;
    private const LOCKOUT_MINUTES = 15;

    // ── Redirection racine ────────────────────────────────────────────────────

    public function index(): void
    {
        if (!empty($_SESSION['admin_id'])) {
            $this->redirect('/admin/dashboard');
        }
        $this->redirect('/admin/login');
    }

    // ── Connexion admin ───────────────────────────────────────────────────────

    public function showLogin(): void
    {
        if (!empty($_SESSION['admin_id'])) {
            $this->redirect('/admin/dashboard');
        }
        $this->render('admin.login', ['csrf' => $this->csrfToken()]);
    }

    public function login(): void
    {
        $this->verifyCsrf();

        $email    = trim(strtolower($_POST['email']    ?? ''));
        $password = $_POST['password'] ?? '';
        $ip       = $this->ip();
        $key      = 'admin:' . $email;

        // Rate limiting (réutilise la même table login_attempts)
        $failures = LoginAttempt::recentFailures($key, $ip, self::LOCKOUT_MINUTES);
        if ($failures >= self::MAX_ATTEMPTS) {
            $this->render('admin.login', [
                'error' => 'Trop de tentatives. Réessayez dans ' . self::LOCKOUT_MINUTES . ' minutes.',
                'csrf'  => $this->csrfToken(),
            ]);
            return;
        }

        $admin = Admin::findByEmail($email);

        if (!$admin || !password_verify($password, $admin['password_hash'])) {
            LoginAttempt::record($key, $ip, false);
            AuditLogger::log('admin', null, 'admin_login_failed', "email:{$email}", $ip);
            $this->render('admin.login', [
                'error' => 'Email ou mot de passe incorrect.',
                'email' => $email,
                'csrf'  => $this->csrfToken(),
            ]);
            return;
        }

        LoginAttempt::record($key, $ip, true);
        AuditLogger::log('admin', (int)$admin['id'], 'admin_login_success', "email:{$email}", $ip);

        if (password_needs_rehash($admin['password_hash'], PASSWORD_BCRYPT)) {
            Database::get()->prepare(
                'UPDATE admins SET password_hash = ? WHERE id = ?'
            )->execute([password_hash($password, PASSWORD_BCRYPT), $admin['id']]);
        }

        session_regenerate_id(true);
        $_SESSION['admin_id']   = (int)$admin['id'];
        $_SESSION['admin_name'] = $admin['name'];
        $_SESSION['admin_role'] = $admin['role'];

        $this->redirect('/admin/dashboard');
    }

    public function logout(): void
    {
        AuditLogger::log(
            'admin',
            (int)($_SESSION['admin_id'] ?? 0),
            'admin_logout',
            '',
            $this->ip()
        );

        unset($_SESSION['admin_id'], $_SESSION['admin_name'], $_SESSION['admin_role']);
        session_regenerate_id(true);
        $this->redirect('/admin/login');
    }

    // ── Tableau de bord admin ─────────────────────────────────────────────────

    public function dashboard(): void
    {
        AdminMiddleware::check();

        $db = Database::get();

        $stats = [
            'clients'         => (int)$db->query('SELECT COUNT(*) FROM clients')->fetchColumn(),
            'contracts'       => (int)$db->query('SELECT COUNT(*) FROM contracts')->fetchColumn(),
            'contracts_actif' => (int)$db->query("SELECT COUNT(*) FROM contracts WHERE status='actif'")->fetchColumn(),
            'claims'          => (int)$db->query('SELECT COUNT(*) FROM claims')->fetchColumn(),
            'claims_ouverts'  => (int)$db->query("SELECT COUNT(*) FROM claims WHERE status='ouvert'")->fetchColumn(),
            'documents'       => (int)$db->query('SELECT COUNT(*) FROM documents')->fetchColumn(),
            'docs_attente'    => (int)$db->query("SELECT COUNT(*) FROM documents WHERE status='en_attente'")->fetchColumn(),
        ];

        $recentAttempts = $db->query(
            "SELECT identifier, ip, success, created_at
             FROM login_attempts
             ORDER BY created_at DESC LIMIT 20"
        )->fetchAll();

        $this->render('admin.dashboard', [
            'stats'          => $stats,
            'recentAttempts' => $recentAttempts,
        ]);
    }
}
