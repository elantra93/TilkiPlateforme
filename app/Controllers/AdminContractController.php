<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Middleware\AdminMiddleware;
use App\Models\Client;
use App\Models\Contract;
use App\Models\Document;
use App\Services\AuditLogger;
use App\Services\ContractDocTypes;
use App\Services\FileStorage;

class AdminContractController extends BaseController
{
    private const STATUSES  = ['actif', 'expiré', 'résilié', 'suspendu'];

    public function index(): void
    {
        AdminMiddleware::check();
        $this->render('admin.contracts.index', [
            'contracts' => Contract::all(),
        ]);
    }

    public function showCreate(): void
    {
        AdminMiddleware::check();
        $this->render('admin.contracts.form', [
            'csrf'     => $this->csrfToken(),
            'contract' => null,
            'clients'  => Client::all(),
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
                'old'      => $old,
                'error'    => 'Tous les champs obligatoires doivent être remplis.',
            ]);
            return;
        }

        try {
            $id = Contract::create($data);
            AuditLogger::log('admin', (int)$_SESSION['admin_id'], 'contract_created', "contract:{$id}", $this->ip());
            $_SESSION['admin_flash'] = ['type' => 'success', 'msg' => 'Contrat créé avec succès.'];
            $this->redirect('/admin/contracts');
        } catch (\Throwable $e) {
            $this->render('admin.contracts.form', [
                'csrf'     => $this->csrfToken(),
                'contract' => null,
                'clients'  => Client::all(),
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
        $this->render('admin.contracts.form', [
            'csrf'          => $this->csrfToken(),
            'contract'      => $contract,
            'clients'       => Client::all(),
            'old'           => [],
            'documents'     => Document::forContractAdmin((int)$id),
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
            $this->render('admin.contracts.form', [
                'csrf'             => $this->csrfToken(),
                'contract'         => $contract,
                'clients'          => Client::all(),
                'old'              => $data,
                'error'            => 'Erreur : ' . $e->getMessage(),
                'documents'        => Document::forContractAdmin((int)$id),
                'contractDocTypes' => $this->contractDocTypesForView($contract),
            ]);
        }
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
        $status = $_POST['status'] ?? 'actif';
        return [
            'client_id'      => (int)($_POST['client_id'] ?? 0),
            'branche'        => trim($_POST['branche']        ?? ''),
            'policy_number'  => trim($_POST['policy_number']  ?? ''),
            'insurer'        => trim($_POST['insurer']         ?? ''),
            'effective_date' => $_POST['effective_date']  ?? '',
            'expiry_date'    => $_POST['expiry_date']     ?? '',
            'premium_total'  => (float)($_POST['premium_total'] ?? 0),
            'premium_due'    => (float)($_POST['premium_due']   ?? 0),
            'currency'       => strtoupper(trim($_POST['currency'] ?? 'XOF')) ?: 'XOF',
            'status'         => in_array($status, self::STATUSES, true) ? $status : 'actif',
        ];
    }
}
