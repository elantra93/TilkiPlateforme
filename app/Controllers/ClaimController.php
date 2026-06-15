<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Models\Claim;
use App\Models\ClaimStep;
use App\Models\Contract;
use App\Models\Document;
use App\Services\AuditLogger;
use App\Services\Auth;

class ClaimController extends BaseController
{
    public function index(): void
    {
        $this->requireAuth();
        $clientId = (int)$_SESSION['client_id'];

        $this->render('claims.index', [
            'client' => Auth::client(),
            'claims' => Claim::forClient($clientId),
        ]);
    }

    public function showDeclare(): void
    {
        $this->requireAuth();
        $clientId = (int)$_SESSION['client_id'];

        $this->render('claims.declare', [
            'contracts' => Contract::forClient($clientId),
            'old'       => $_SESSION['_old'] ?? [],
            'error'     => $_SESSION['_error'] ?? null,
            'csrf'      => $this->csrfToken(),
        ]);
        unset($_SESSION['_old'], $_SESSION['_error']);
    }

    public function declare(): void
    {
        $this->requireAuth();
        $this->verifyCsrf();
        $clientId = (int)$_SESSION['client_id'];

        $contractId     = (int)($_POST['contract_id'] ?? 0) ?: null;
        $insurerInput   = trim($_POST['insurer']          ?? '');
        $brancheInput   = trim($_POST['branche']          ?? '');
        $occurrenceDate = trim($_POST['occurrence_date']   ?? '');
        $description    = trim($_POST['description']       ?? '');

        // Si un contrat est choisi, vérifier qu'il appartient bien au client
        if ($contractId) {
            $contract = Contract::findForClient($contractId, $clientId);
            if (!$contract) {
                $contractId = null;
            } else {
                $insurerInput = $contract['insurer'];
                $brancheInput = $contract['branche'];
            }
        }

        if (!$insurerInput || !$brancheInput || !$occurrenceDate || !$description) {
            $_SESSION['_old']   = $_POST;
            $_SESSION['_error'] = 'Tous les champs obligatoires doivent être remplis.';
            $this->redirect('/claims/declare');
            return;
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $occurrenceDate) ||
            strtotime($occurrenceDate) > time()) {
            $_SESSION['_old']   = $_POST;
            $_SESSION['_error'] = 'La date de survenance est invalide ou dans le futur.';
            $this->redirect('/claims/declare');
            return;
        }

        $id = Claim::create([
            'client_id'       => $clientId,
            'contract_id'     => $contractId,
            'claim_number'    => 'PENDING',
            'insurer'         => $insurerInput,
            'branche'         => $brancheInput,
            'occurrence_date' => $occurrenceDate,
            'status'          => 'ouvert',
            'description'     => $description,
            'is_auto_rc'      => 0,
        ]);
        ClaimStep::initForClaim($id, false);

        $claimNumber = 'SIN-' . date('Y') . '-' . str_pad((string)$id, 4, '0', STR_PAD_LEFT);
        Claim::setNumber($id, $claimNumber);

        AuditLogger::log('client', $clientId, 'claim_declared', "claim:{$id}", $this->ip());

        $_SESSION['flash'] = ['type' => 'success', 'msg' => "Sinistre {$claimNumber} déclaré. Notre équipe vous contactera prochainement."];
        $this->redirect('/claims/' . $id);
    }

    public function show(string $id): void
    {
        $this->requireAuth();
        $clientId = (int)$_SESSION['client_id'];
        $claim    = Claim::findForClient((int)$id, $clientId);

        if (!$claim) {
            http_response_code(404);
            require APP_PATH . '/Views/errors/404.php';
            return;
        }

        $this->render('claims.show', [
            'client'    => Auth::client(),
            'claim'     => $claim,
            'steps'     => ClaimStep::forClaim((int)$id),
            'documents' => Document::forClaim((int)$id, $clientId),
            'csrf'      => $this->csrfToken(),
        ]);
    }
}
