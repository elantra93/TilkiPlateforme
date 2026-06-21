<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Middleware\AdminMiddleware;
use App\Models\Client;
use App\Models\Contract;
use App\Models\Document;
use App\Models\Payment;
use App\Services\AuditLogger;
use App\Services\Branches;
use App\Services\ContractDocTypes;
use App\Services\FileStorage;

class AdminContractController extends BaseController
{
    private const STATUSES       = ['actif', 'expiré', 'résilié', 'suspendu'];
    private const PAYMENT_METHODS = ['especes', 'virement', 'cheque', 'mobile_money', 'carte'];

    public function index(): void
    {
        AdminMiddleware::check();
        $contracts = Contract::all();
        $paidMap   = Payment::sumValidatedMap();
        foreach ($contracts as &$c) {
            $c['premium_due'] = max(0.0, (float)$c['premium_total'] - ($paidMap[(int)$c['id']] ?? 0.0));
        }
        unset($c);
        $this->render('admin.contracts.index', [
            'contracts' => $contracts,
        ]);
    }

    public function showCreate(): void
    {
        AdminMiddleware::check();
        $this->render('admin.contracts.form', [
            'csrf'     => $this->csrfToken(),
            'contract' => null,
            'clients'  => Client::all(),
            'branches' => Branches::BRANCHES,
            'insurers' => Branches::INSURERS,
            'old'      => [],
        ]);
    }

    public function create(): void
    {
        AdminMiddleware::check();
        $this->verifyCsrf();

        $data = $this->collectFields();
        $old  = $data;

        if (!$data['client_id'] || !$data['branche'] || !$data['policy_number'] ||
            !$data['insurer'] || !$data['effective_date'] || !$data['expiry_date']) {
            $this->render('admin.contracts.form', [
                'csrf'     => $this->csrfToken(),
                'contract' => null,
                'clients'  => Client::all(),
                'branches' => Branches::BRANCHES,
                'insurers' => Branches::INSURERS,
                'old'      => $old,
                'error'    => 'Tous les champs obligatoires doivent être remplis.',
            ]);
            return;
        }

        try {
            $data['premium_due']   = $data['premium_total']; // initial = total (aucun paiement validé)
            $data['emission_date'] = $data['emission_date'] ?: null;
            $id = Contract::create($data);
            AuditLogger::log('admin', (int)$_SESSION['admin_id'], 'contract_created', "contract:{$id}", $this->ip());
            $_SESSION['admin_flash'] = ['type' => 'success', 'msg' => 'Contrat créé avec succès.'];
            $this->redirect('/admin/contracts');
        } catch (\Throwable $e) {
            $this->render('admin.contracts.form', [
                'csrf'     => $this->csrfToken(),
                'contract' => null,
                'clients'  => Client::all(),
                'branches' => Branches::BRANCHES,
                'insurers' => Branches::INSURERS,
                'old'      => $old,
                'error'    => 'Erreur : ' . $e->getMessage(),
            ]);
        }
    }

    private const CAT_CONTRAT = ['cotation', 'souscription'];

    private const DOC_TYPES_COTATION = [
        'questionnaire', 'cotation', 'bordereau', 'note_de_couverture',
    ];

    private function contractDocTypesForView(array $contract): array
    {
        $branche  = strtolower(trim($contract['branche'] ?? ''));
        $specific = ContractDocTypes::forBranche($branche);
        $sous     = $specific
            ? array_column($specific, 'key')
            : ContractDocTypes::GENERIC_SOUSCRIPTION;
        return [
            'cotation'     => self::DOC_TYPES_COTATION,
            'souscription' => $sous,
        ];
    }

    public function showEdit(string $id): void
    {
        AdminMiddleware::check();
        $contract = Contract::find((int)$id);
        if (!$contract) {
            http_response_code(404);
            require APP_PATH . '/Views/errors/404.php';
            return;
        }
        $premiumDue = max(0.0, (float)$contract['premium_total'] - Payment::sumValidated((int)$id));
        $this->render('admin.contracts.form', [
            'csrf'             => $this->csrfToken(),
            'contract'         => $contract,
            'clients'          => Client::all(),
            'branches'         => Branches::BRANCHES,
            'insurers'         => Branches::INSURERS,
            'old'              => [],
            'premiumDue'       => $premiumDue,
            'payments'         => Payment::listByContract((int)$id),
            'paymentMethods'   => self::PAYMENT_METHODS,
            'documents'        => Document::forContractAdmin((int)$id),
            'contractDocTypes' => $this->contractDocTypesForView($contract),
        ]);
    }

    public function edit(string $id): void
    {
        AdminMiddleware::check();
        $this->verifyCsrf();

        $contract = Contract::find((int)$id);
        if (!$contract) {
            http_response_code(404);
            require APP_PATH . '/Views/errors/404.php';
            return;
        }

        $data = $this->collectFields();
        unset($data['client_id']); // client non modifiable après création

        try {
            Contract::update((int)$id, $data);
            AuditLogger::log('admin', (int)$_SESSION['admin_id'], 'contract_updated', "contract:{$id}", $this->ip());
            $_SESSION['admin_flash'] = ['type' => 'success', 'msg' => 'Contrat mis à jour.'];
            $this->redirect('/admin/contracts/' . $id . '/edit');
        } catch (\Throwable $e) {
            $premiumDue = max(0.0, (float)$contract['premium_total'] - Payment::sumValidated((int)$id));
            $this->render('admin.contracts.form', [
                'csrf'             => $this->csrfToken(),
                'contract'         => $contract,
                'clients'          => Client::all(),
                'branches'         => Branches::BRANCHES,
                'insurers'         => Branches::INSURERS,
                'old'              => $data,
                'premiumDue'       => $premiumDue,
                'error'            => 'Erreur : ' . $e->getMessage(),
                'payments'         => Payment::listByContract((int)$id),
                'paymentMethods'   => self::PAYMENT_METHODS,
                'documents'        => Document::forContractAdmin((int)$id),
                'contractDocTypes' => $this->contractDocTypesForView($contract),
            ]);
        }
    }

    public function addPayment(string $id): void
    {
        AdminMiddleware::check();
        $this->verifyCsrf();

        $contract = Contract::find((int)$id);
        if (!$contract) {
            http_response_code(404);
            require APP_PATH . '/Views/errors/404.php';
            return;
        }

        $amount    = trim($_POST['amount']    ?? '');
        $method    = trim($_POST['method']    ?? '');
        $paidAt    = trim($_POST['paid_at']   ?? '') ?: null;
        $reference = trim($_POST['reference'] ?? '') ?: null;
        $note      = trim($_POST['note']      ?? '') ?: null;

        if (!is_numeric($amount) || (float)$amount <= 0) {
            $_SESSION['admin_flash'] = ['type' => 'danger', 'msg' => 'Le montant doit être supérieur à 0.'];
            $this->redirect('/admin/contracts/' . $id . '/edit');
            return;
        }
        if (!in_array($method, self::PAYMENT_METHODS, true)) {
            $_SESSION['admin_flash'] = ['type' => 'danger', 'msg' => 'Mode de paiement invalide.'];
            $this->redirect('/admin/contracts/' . $id . '/edit');
            return;
        }

        $docId   = null;
        $hasFile = !empty($_FILES['proof']) && $_FILES['proof']['error'] !== UPLOAD_ERR_NO_FILE;
        if ($hasFile) {
            try {
                $stored = FileStorage::store($_FILES['proof'], 'paiements/' . $id);
                $docId  = Document::create([
                    'client_id'         => $contract['client_id'],
                    'contract_id'       => (int)$id,
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
                $_SESSION['admin_flash'] = ['type' => 'danger', 'msg' => 'Erreur fichier : ' . $e->getMessage()];
                $this->redirect('/admin/contracts/' . $id . '/edit');
                return;
            }
        }

        $payId = Payment::create([
            'contract_id'  => (int)$id,
            'client_id'    => $contract['client_id'],
            'amount'       => (float)$amount,
            'method'       => $method,
            'document_id'  => $docId,
            'status'       => 'valide',
            'created_by'   => 'admin',
            'validated_by' => (int)$_SESSION['admin_id'],
            'validated_at' => date('Y-m-d H:i:s'),
            'reference'    => $reference,
            'paid_at'      => $paidAt,
            'note'         => $note,
        ]);

        AuditLogger::log('admin', (int)$_SESSION['admin_id'], 'contract_payment_added',
            "contract:{$id} payment:{$payId}", $this->ip());
        $_SESSION['admin_flash'] = ['type' => 'success', 'msg' => 'Paiement enregistré.'];
        $this->redirect('/admin/contracts/' . $id . '/edit');
    }

    public function validatePayment(string $contractId, string $paymentId): void
    {
        AdminMiddleware::check();
        $this->verifyCsrf();

        $payment = Payment::findForContract((int)$paymentId, (int)$contractId);
        if (!$payment || $payment['status'] !== 'en_attente') {
            $_SESSION['admin_flash'] = ['type' => 'danger', 'msg' => 'Paiement introuvable ou déjà traité.'];
            $this->redirect('/admin/contracts/' . $contractId . '/edit');
            return;
        }

        $amount = trim($_POST['amount'] ?? '');
        if (!is_numeric($amount) || (float)$amount <= 0) {
            $_SESSION['admin_flash'] = ['type' => 'danger', 'msg' => 'Le montant doit être supérieur à 0.'];
            $this->redirect('/admin/contracts/' . $contractId . '/edit');
            return;
        }

        Payment::validate((int)$paymentId, (float)$amount, (int)$_SESSION['admin_id']);
        AuditLogger::log('admin', (int)$_SESSION['admin_id'], 'payment_validated',
            "contract:{$contractId} payment:{$paymentId}", $this->ip());
        $_SESSION['admin_flash'] = ['type' => 'success', 'msg' => 'Paiement validé.'];
        $this->redirect('/admin/contracts/' . $contractId . '/edit');
    }

    public function uploadDoc(string $id): void
    {
        AdminMiddleware::check();
        $this->verifyCsrf();

        $contract = Contract::find((int)$id);
        if (!$contract) {
            http_response_code(404);
            require APP_PATH . '/Views/errors/404.php';
            return;
        }

        $category = trim($_POST['category'] ?? '');
        $docType  = trim($_POST['doc_type']  ?? '');

        if (!in_array($category, self::CAT_CONTRAT, true) || !$docType) {
            $_SESSION['admin_flash'] = ['type' => 'danger', 'msg' => 'Catégorie ou type de document manquant.'];
            $this->redirect('/admin/contracts/' . $id . '/edit');
            return;
        }

        if (empty($_FILES['document']) || $_FILES['document']['error'] === UPLOAD_ERR_NO_FILE) {
            $_SESSION['admin_flash'] = ['type' => 'danger', 'msg' => 'Aucun fichier sélectionné.'];
            $this->redirect('/admin/contracts/' . $id . '/edit');
            return;
        }

        try {
            $stored = FileStorage::store($_FILES['document'], 'contrats/' . $id);
            Document::create([
                'client_id'         => (int)$contract['client_id'],
                'contract_id'       => (int)$id,
                'claim_id'          => null,
                'scope'             => 'contrat',
                'category'          => $category,
                'doc_type'          => $docType,
                'original_filename' => $stored['original_filename'],
                'stored_path'       => $stored['stored_path'],
                'mime_type'         => $stored['mime_type'],
                'file_size'         => $stored['file_size'],
                'source'            => 'admin',
                'status'            => 'valide',
            ]);
            AuditLogger::log('admin', (int)$_SESSION['admin_id'], 'contract_doc_upload', "contract:{$id}", $this->ip());
            $_SESSION['admin_flash'] = ['type' => 'success', 'msg' => 'Document ajouté au contrat.'];
        } catch (\Throwable $e) {
            $_SESSION['admin_flash'] = ['type' => 'danger', 'msg' => $e->getMessage()];
        }

        $this->redirect('/admin/contracts/' . $id . '/edit');
    }

    private function collectFields(): array
    {
        $status        = $_POST['status']  ?? 'actif';
        $emissionDate  = trim($_POST['emission_date'] ?? '');
        $branche       = trim($_POST['branche']       ?? '');
        $insurer       = trim($_POST['insurer']        ?? '');

        return [
            'client_id'      => (int)($_POST['client_id'] ?? 0),
            'branche'        => in_array($branche, Branches::BRANCHES, true) ? $branche : $branche,
            'policy_number'  => trim($_POST['policy_number'] ?? ''),
            'insurer'        => in_array($insurer, Branches::INSURERS, true) ? $insurer : $insurer,
            'effective_date' => trim($_POST['effective_date'] ?? ''),
            'expiry_date'    => trim($_POST['expiry_date']    ?? ''),
            'emission_date'  => $emissionDate ?: null,
            'premium_total'  => (float)($_POST['premium_total'] ?? 0),
            'premium_net'    => (float)($_POST['premium_net']   ?? 0),
            'premium_fees'   => (float)($_POST['premium_fees']  ?? 0),
            'currency'       => strtoupper(trim($_POST['currency'] ?? 'XOF')) ?: 'XOF',
            'status'         => in_array($status, self::STATUSES, true) ? $status : 'actif',
        ];
    }
}
