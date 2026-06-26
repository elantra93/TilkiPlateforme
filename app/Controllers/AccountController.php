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
            'csrf'   => $this->csrfToken(),
            'carte'  => Document::carteAssurance($clientId),
            'client' => Client::findById($clientId),
        ]);
    }

    public function updateEntreprise(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $clientId = (int)$_SESSION['client_id'];
        $client   = Client::findById($clientId);

        if (!$client || ($client['account_type'] ?? 'individuel') !== 'entreprise') {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Action non autorisée.'];
            $this->redirect('/account');
            return;
        }

        $companyName         = trim($_POST['company_name']          ?? '');
        $companyRccm         = trim($_POST['company_rccm']          ?? '') ?: null;
        $companyDfe          = trim($_POST['company_dfe']           ?? '') ?: null;
        $companyAddress      = trim($_POST['company_address']       ?? '') ?: null;
        $companyCity         = trim($_POST['company_city']          ?? '') ?: null;
        $companyCountry      = trim($_POST['company_country']       ?? '') ?: "Côte d'Ivoire";
        $companyContactName  = trim($_POST['company_contact_name']  ?? '') ?: null;
        $companyContactPhone = trim($_POST['company_contact_phone'] ?? '') ?: null;

        if (!$companyName) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'La raison sociale est obligatoire.'];
            $this->redirect('/account');
            return;
        }

        Client::updateEntreprise($clientId, [
            'company_name'          => $companyName,
            'company_rccm'          => $companyRccm,
            'company_dfe'           => $companyDfe,
            'company_address'       => $companyAddress,
            'company_city'          => $companyCity,
            'company_country'       => $companyCountry,
            'company_contact_name'  => $companyContactName,
            'company_contact_phone' => $companyContactPhone,
        ]);

        AuditLogger::log('client', $clientId, 'entreprise_updated', 'via_account', $this->ip());
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Informations entreprise mises à jour.'];
        $this->redirect('/account');
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
