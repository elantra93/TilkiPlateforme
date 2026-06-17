<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Models\Client;
use App\Services\Auth;
use App\Services\AuditLogger;
use App\Services\Mailer;

class AuthController extends BaseController
{
    // ── Connexion client ──────────────────────────────────────────────────────

    public function showLogin(): void
    {
        if (!empty($_SESSION['client_id'])) {
            $this->redirect('/dashboard');
        }
        $this->render('auth.login', ['csrf' => $this->csrfToken()]);
    }

    public function login(): void
    {
        $this->verifyCsrf();

        $accountNumber = trim($_POST['account_number'] ?? '');
        $password      = $_POST['password'] ?? '';

        if (!preg_match('/^\d{6}$/', $accountNumber)) {
            $this->render('auth.login', [
                'error' => 'Le numéro de compte doit être composé de 6 chiffres.',
                'csrf'  => $this->csrfToken(),
            ]);
            return;
        }

        $result = Auth::attempt($accountNumber, $password, $this->ip());

        if (!$result['success']) {
            $this->render('auth.login', [
                'error'          => $result['error'],
                'account_number' => $accountNumber,
                'csrf'           => $this->csrfToken(),
            ]);
            return;
        }

        $this->redirect($result['must_change_password'] ? '/password/change' : '/dashboard');
    }

    public function logout(): void
    {
        Auth::logout();
    }

    // ── Changement de mot de passe (connecté) ─────────────────────────────────

    public function showChangePassword(): void
    {
        $this->requireAuth();
        $this->render('auth.change_password', ['csrf' => $this->csrfToken()]);
    }

    public function changePassword(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password']     ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if ($new !== $confirm) {
            $this->render('auth.change_password', [
                'error' => 'Les codes PIN ne correspondent pas.',
                'csrf'  => $this->csrfToken(),
            ]);
            return;
        }

        if (!preg_match('/^[0-9]{4,8}$/', $new)) {
            $this->render('auth.change_password', [
                'error' => 'Le code PIN doit contenir entre 4 et 8 chiffres (chiffres uniquement).',
                'csrf'  => $this->csrfToken(),
            ]);
            return;
        }

        $client = Client::findById((int)$_SESSION['client_id']);

        if (!$client || !password_verify($current, $client['password_hash'])) {
            $this->render('auth.change_password', [
                'error' => 'Code PIN actuel incorrect.',
                'csrf'  => $this->csrfToken(),
            ]);
            return;
        }

        Client::updatePasswordHash((int)$client['id'], password_hash($new, PASSWORD_BCRYPT));
        $_SESSION['must_change_password'] = false;

        $this->redirect('/dashboard');
    }

    // ── Mot de passe oublié ───────────────────────────────────────────────────

    public function showForgotPassword(): void
    {
        if (!empty($_SESSION['client_id'])) {
            $this->redirect('/dashboard');
        }
        $this->render('auth.forgot_password', ['csrf' => $this->csrfToken()]);
    }

    public function forgotPassword(): void
    {
        $this->verifyCsrf();

        $email = trim(strtolower($_POST['email'] ?? ''));

        // Réponse identique que le compte existe ou non (anti-énumération)
        $generic = 'Si cette adresse est associée à un compte, un email de réinitialisation a été envoyé.';

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->render('auth.forgot_password', [
                'error' => 'Adresse email invalide.',
                'csrf'  => $this->csrfToken(),
            ]);
            return;
        }

        $client = Client::findByEmail($email);

        if ($client) {
            $token = bin2hex(random_bytes(32));
            Client::setResetToken((int)$client['id'], $token);
            Mailer::sendPasswordReset($client, $token);
            AuditLogger::log('client', (int)$client['id'], 'password_reset_requested', "email:{$email}", $this->ip());
        }

        $config   = require CONFIG_PATH;
        $devUrl   = ($config['app']['env'] === 'development') ? ($_SESSION['dev_reset_url'] ?? null) : null;
        unset($_SESSION['dev_reset_url']);

        $this->render('auth.forgot_password', [
            'success' => $generic,
            'dev_url' => $devUrl,
            'csrf'    => $this->csrfToken(),
        ]);
    }

    // ── Réinitialisation de mot de passe (via token) ──────────────────────────

    public function showResetPassword(): void
    {
        $token  = trim($_GET['token'] ?? '');
        $client = $token ? Client::findByResetToken($token) : null;

        if (!$client) {
            $this->render('auth.reset_password', [
                'error' => 'Ce lien est invalide ou expiré. Veuillez faire une nouvelle demande.',
                'csrf'  => $this->csrfToken(),
            ]);
            return;
        }

        $this->render('auth.reset_password', [
            'token' => $token,
            'csrf'  => $this->csrfToken(),
        ]);
    }

    public function resetPassword(): void
    {
        $this->verifyCsrf();

        $token   = trim($_POST['token']            ?? '');
        $new     = $_POST['new_password']          ?? '';
        $confirm = $_POST['confirm_password']      ?? '';

        $client = $token ? Client::findByResetToken($token) : null;

        if (!$client) {
            $this->render('auth.reset_password', [
                'error' => 'Ce lien est invalide ou expiré. Veuillez faire une nouvelle demande.',
                'csrf'  => $this->csrfToken(),
            ]);
            return;
        }

        if ($new !== $confirm) {
            $this->render('auth.reset_password', [
                'error' => 'Les codes PIN ne correspondent pas.',
                'token' => $token,
                'csrf'  => $this->csrfToken(),
            ]);
            return;
        }

        if (!preg_match('/^[0-9]{4,8}$/', $new)) {
            $this->render('auth.reset_password', [
                'error' => 'Le code PIN doit contenir entre 4 et 8 chiffres (chiffres uniquement).',
                'token' => $token,
                'csrf'  => $this->csrfToken(),
            ]);
            return;
        }

        Client::updatePasswordHash((int)$client['id'], password_hash($new, PASSWORD_BCRYPT));
        Client::clearResetToken((int)$client['id']);
        AuditLogger::log('client', (int)$client['id'], 'password_reset_done', 'via_token', $this->ip());

        $this->render('auth.login', [
            'success' => 'Code PIN réinitialisé. Vous pouvez vous connecter.',
            'csrf'    => $this->csrfToken(),
        ]);
    }
}
