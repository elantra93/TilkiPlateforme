<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Models\Contract;
use App\Models\Document;
use App\Models\Payment;
use App\Services\AuditLogger;
use App\Services\Auth;
use App\Services\ContractDocTypes;
use App\Services\FileStorage;
use App\Services\TallyUrlBuilder;

class ContractController extends BaseController
{
    public function index(): void
    {
        $this->requireAuth();
        $clientId = (int)$_SESSION['client_id'];

        $this->render('contracts.index', [
            'client'    => Auth::client(),
            'contracts' => Contract::forClient($clientId),
        ]);
    }

    public function show(string $id): void
    {
        $this->requireAuth();
        $clientId = (int)$_SESSION['client_id'];
        $client   = Auth::client();
        $contract = Contract::findForClient((int)$id, $clientId);

        if (!$contract) {
            http_response_code(404);
            require APP_PATH . '/Views/errors/404.php';
            return;
        }

        $config      = file_exists(CONFIG_PATH) ? (require CONFIG_PATH) : [];
        $appUrl      = rtrim((string)($config['app']['url'] ?? ''), '/');
        $branche     = strtolower(trim($contract['branche'] ?? ''));
        $attestUrl   = '';
        if (in_array($branche, ['automobile', 'auto'], true) && $appUrl) {
            $attest = Document::attestationForContract((int)$id);
            if ($attest) {
                $attestUrl = $appUrl . '/documents/' . (int)$attest['id'] . '/download';
            }
        }
        $tallyClaimUrl = TallyUrlBuilder::claimFormUrl($client, $contract, $attestUrl);

        $this->render('contracts.show', [
            'client'         => $client,
            'contract'       => $contract,
            'documents'      => Document::forContract((int)$id, $clientId),
            'payments'       => Payment::listByContract((int)$id),
            'csrf'           => $this->csrfToken(),
            'tallyClaimUrl'  => $tallyClaimUrl,
            'branchDocTypes' => ContractDocTypes::forBranche($contract['branche']),
        ]);
    }

    private const PAYMENT_METHODS = [
        'especes'      => 'Espèces',
        'virement'     => 'Virement',
        'cheque'       => 'Chèque',
        'mobile_money' => 'Mobile Money',
        'carte'        => 'Carte',
    ];

    public function submitPayment(string $id): void
    {
        $this->requireAuth();
        $this->verifyCsrf();

        $clientId = (int)$_SESSION['client_id'];
        $contract = Contract::findForClient((int)$id, $clientId);

        if (!$contract) {
            http_response_code(403);
            require APP_PATH . '/Views/errors/403.php';
            return;
        }

        $amount = trim($_POST['amount'] ?? '');
        $method = trim($_POST['method'] ?? '');

        if (!is_numeric($amount) || (float)$amount <= 0) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Le montant doit être un nombre supérieur à 0.'];
            $this->redirect('/contracts/' . $id);
            return;
        }

        if (!array_key_exists($method, self::PAYMENT_METHODS)) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Mode de paiement invalide.'];
            $this->redirect('/contracts/' . $id);
            return;
        }

        if (empty($_FILES['document']) || $_FILES['document']['error'] === UPLOAD_ERR_NO_FILE) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Veuillez joindre la preuve de paiement.'];
            $this->redirect('/contracts/' . $id);
            return;
        }

        try {
            $mime = mime_content_type($_FILES['document']['tmp_name']);
            if (!in_array($mime, ['application/pdf', 'image/jpeg', 'image/png'], true)) {
                throw new \RuntimeException('Seuls les formats PDF, JPG et PNG sont acceptés.');
            }

            $stored = FileStorage::store($_FILES['document'], 'paiements/' . $id);
            $docId  = Document::create([
                'client_id'         => $clientId,
                'contract_id'       => (int)$id,
                'claim_id'          => null,
                'scope'             => 'paiement',
                'category'          => 'paiement',
                'doc_type'          => 'preuve_paiement',
                'original_filename' => $stored['original_filename'],
                'stored_path'       => $stored['stored_path'],
                'mime_type'         => $stored['mime_type'],
                'file_size'         => $stored['file_size'],
                'source'            => 'client',
                'status'            => 'en_attente',
            ]);

            Payment::create([
                'contract_id' => (int)$id,
                'client_id'   => $clientId,
                'amount'      => (float)$amount,
                'method'      => $method,
                'document_id' => $docId,
                'status'      => 'en_attente',
                'created_by'  => 'client',
            ]);

            AuditLogger::log('client', $clientId, 'payment_submitted', "contract:{$id}", $this->ip());
            $_SESSION['flash'] = [
                'type' => 'success',
                'msg'  => 'Votre paiement a bien été envoyé et est en attente de validation par TILKI.',
            ];
        } catch (\Throwable $e) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => $e->getMessage()];
        }

        $this->redirect('/contracts/' . $id);
    }
}
