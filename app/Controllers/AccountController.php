<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Models\Client;
use App\Models\Document;
use App\Services\AuditLogger;

class AccountController extends BaseController
{
    public function show(): void
    {
        $this->requireAuth();
        $clientId = (int)$_SESSION['client_id'];

        $this->render('account.settings', [
            'csrf'  => $this->csrfToken(),
            'carte' => Document::carteAssurance($clientId),
        ]);
    }

    public function changePin(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $clientId = (int)$_SESSION['client_id'];
        $current  = $_POST['current_password'] ?? '';
        $new      = $_POST['new_password']     ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        $error = null;

        if ($new !== $confirm) {
            $error = 'Les codes PIN ne correspondent pas.';
        } elseif (!preg_match('/^[0-9]{4,8}$/', $new)) {
            $error = 'Le code PIN doit contenir entre 4 et 8 chiffres (chiffres uniquement).';
        } else {
            $client = Client::findById($clientId);
            if (!$client || !password_verify($current, $client['password_hash'])) {
                $error = 'Code PIN actuel incorrect.';
            }
        }

        if ($error) {
            $this->render('account.settings', [
                'csrf'      => $this->csrfToken(),
                'carte'     => Document::carteAssurance($clientId),
                'pinError'  => $error,
            ]);
            return;
        }

        Client::updatePasswordHash($clientId, password_hash($new, PASSWORD_BCRYPT));
        AuditLogger::log('client', $clientId, 'pin_changed', 'via_account', $this->ip());
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Code PIN modifié avec succès.'];
        $this->redirect('/account');
    }
}
