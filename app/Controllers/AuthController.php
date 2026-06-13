<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Models\Client;
use App\Services\Auth;

class AuthController extends BaseController
{
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
                'error' => 'Les nouveaux mots de passe ne correspondent pas.',
                'csrf'  => $this->csrfToken(),
            ]);
            return;
        }

        if (strlen($new) < 8) {
            $this->render('auth.change_password', [
                'error' => 'Le mot de passe doit contenir au moins 8 caractères.',
                'csrf'  => $this->csrfToken(),
            ]);
            return;
        }

        $client = Client::findById((int)$_SESSION['client_id']);

        if (!$client || !password_verify($current, $client['password_hash'])) {
            $this->render('auth.change_password', [
                'error' => 'Mot de passe actuel incorrect.',
                'csrf'  => $this->csrfToken(),
            ]);
            return;
        }

        Client::updatePasswordHash((int)$client['id'], password_hash($new, PASSWORD_BCRYPT));
        $_SESSION['must_change_password'] = false;

        $this->redirect('/dashboard');
    }
}
