<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Middleware\AdminMiddleware;
use App\Models\Client;
use App\Models\Contract;
use App\Models\Document;
use App\Models\Payment;
use App\Services\AuditLogger;
use App\Services\FileStorage;

class AdminPaymentController extends BaseController
{
    private const METHODS = ['cheque', 'virement', 'caisse', 'mobile_money'];

    public function index(): void
    {
        AdminMiddleware::check();
        $this->render('admin.payments.index', [
            'payments' => Payment::listAll(),
        ]);
    }

    public function showCreate(): void
    {
        AdminMiddleware::check();

        $preClientId   = (int)($_GET['client_id']   ?? 0) ?: null;
        $preContractId = (int)($_GET['contract_id']  ?? 0) ?: null;

        $clients   = Client::all();
        $contracts = Contract::all();

        $contractsByClient = [];
        foreach ($contracts as $c) {
            $contractsByClient[$c['client_id']][] = [
                'id'    => $c['id'],
                'label' => $c['policy_number'] . ' — ' . $c['branche'] . ' (' . $c['insurer'] . ')',
            ];
        }

        $this->render('admin.payments.create', [
            'csrf'             => $this->csrfToken(),
            'clients'          => $clients,
            'contractsByClient'=> $contractsByClient,
            'preClientId'      => $preClientId,
            'preContractId'    => $preContractId,
            'old'              => [],
            'methods'          => self::METHODS,
        ]);
    }

    public function create(): void
    {
        AdminMiddleware::check();
        $this->verifyCsrf();

        $clientId   = (int)($_POST['client_id']   ?? 0);
        $contractId = (int)($_POST['contract_id']  ?? 0);
        $amount     = trim($_POST['amount']        ?? '');
        $method     = trim($_POST['method']        ?? '');
        $reference  = trim($_POST['reference']     ?? '') ?: null;
        $paidAt     = trim($_POST['paid_at']       ?? '');
        $note       = trim($_POST['note']          ?? '') ?: null;

        $old = compact('clientId', 'contractId', 'amount', 'method', 'reference', 'paidAt', 'note');

        $clients   = Client::all();
        $contracts = Contract::all();
        $contractsByClient = [];
        foreach ($contracts as $c) {
            $contractsByClient[$c['client_id']][] = [
                'id'    => $c['id'],
                'label' => $c['policy_number'] . ' — ' . $c['branche'] . ' (' . $c['insurer'] . ')',
            ];
        }

        $renderCreate = function (string $error) use ($clients, $contracts, $contractsByClient, $old): void {
            $this->render('admin.payments.create', [
                'csrf'              => $this->csrfToken(),
                'clients'           => $clients,
                'contractsByClient' => $contractsByClient,
                'preClientId'       => $old['clientId'] ?: null,
                'preContractId'     => $old['contractId'] ?: null,
                'old'               => $old,
                'methods'           => self::METHODS,
                'error'             => $error,
            ]);
        };

        if (!$clientId || !Client::findById($clientId)) {
            $renderCreate('Client invalide.');
            return;
        }
        if (!$contractId || !Contract::findForClient($contractId, $clientId)) {
            $renderCreate('Contrat invalide ou n\'appartenant pas à ce client.');
            return;
        }
        if (!is_numeric($amount) || (float)$amount <= 0) {
            $renderCreate('Le montant doit être un nombre supérieur à 0.');
            return;
        }
        if (!in_array($method, self::METHODS, true)) {
            $renderCreate('Mode de paiement invalide.');
            return;
        }
        if (!$paidAt || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $paidAt)) {
            $renderCreate('Date de paiement invalide.');
            return;
        }

        $proofDocId = null;
        $hasFile = !empty($_FILES['proof']) && $_FILES['proof']['error'] !== UPLOAD_ERR_NO_FILE;
        if ($hasFile) {
            try {
                $stored = FileStorage::store($_FILES['proof'], 'paiements/' . $contractId);
                $proofDocId = Document::create([
                    'client_id'         => $clientId,
                    'contract_id'       => $contractId,
                    'claim_id'          => null,
                    'scope'             => 'paiement',
                    'category'          => 'paiement',
                    'doc_type'          => 'preuve_paiement',
                    'original_filename' => $stored['original_filename'],
                    'stored_path'       => $stored['stored_path'],
                    'mime_type'         => $stored['mime_type'],
                    'file_size'         => $stored['file_size'],
                    'source'            => 'admin',
                    'status'            => 'valide',
                ]);
            } catch (\Throwable $e) {
                $renderCreate('Erreur fichier : ' . $e->getMessage());
                return;
            }
        }

        $payId = Payment::create([
            'client_id'         => $clientId,
            'contract_id'       => $contractId,
            'amount'            => (float)$amount,
            'method'            => $method,
            'proof_document_id' => $proofDocId,
            'reference'         => $reference,
            'paid_at'           => $paidAt,
            'note'              => $note,
            'created_by'        => (int)$_SESSION['admin_id'],
        ]);

        AuditLogger::log('admin', (int)$_SESSION['admin_id'], 'payment_created',
            "payment:{$payId} client:{$clientId}", $this->ip());

        $_SESSION['admin_flash'] = ['type' => 'success', 'msg' => 'Paiement enregistré avec succès.'];
        $this->redirect('/admin/payments');
    }
}
