<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Middleware\AdminMiddleware;
use App\Models\Claim;
use App\Models\Client;
use App\Models\Contract;
use App\Models\Document;
use App\Services\AuditLogger;
use App\Services\ContractDocTypes;
use App\Services\FileStorage;

class AdminDocumentController extends BaseController
{
    private const DOC_TYPES = [
        // Contrat
        'cotation'                   => ['questionnaire', 'cotation', 'bordereau', 'note_de_couverture'],
        'souscription'               => ['contrat', 'avenant', 'preuve_paiement', 'quittance', 'attestation', 'decompte'],
        // Sinistre
        'declaration'                => ['declaration_sinistre', 'rapport_circonstances', 'constat_amiable', 'plainte'],
        'expertise_devis'            => ['rapport_expertise', 'devis_reparation', 'contre_expertise', 'estimation_perte'],
        'correspondances'            => ['courrier_assureur', 'courrier_expert', 'courrier_client', 'mise_en_demeure'],
        'reglements_remboursements'  => ['virement', 'cheque', 'quittance_reglement', 'decompte_indemnite'],
    ];

    private const CATEGORIES_CONTRAT  = ['cotation', 'souscription'];
    private const CATEGORIES_SINISTRE = ['declaration', 'expertise_devis', 'correspondances', 'reglements_remboursements'];

    public function showUpload(): void
    {
        AdminMiddleware::check();

        $clients   = Client::all();
        $contracts = Contract::all();
        $claims    = Claim::all();

        // Groupe par client_id pour la cascade JS (inclut la branche pour les types branch-aware)
        $contractsByClient = [];
        foreach ($contracts as $c) {
            $contractsByClient[$c['client_id']][] = [
                'id'     => $c['id'],
                'label'  => $c['policy_number'] . ' — ' . $c['branche'],
                'branche'=> strtolower(trim($c['branche'])),
            ];
        }
        $claimsByClient = [];
        foreach ($claims as $cl) {
            $claimsByClient[$cl['client_id']][] = [
                'id'         => $cl['id'],
                'label'      => $cl['claim_number'] . ' — ' . $cl['branche'],
                'contractId' => $cl['contract_id'],
            ];
        }

        $this->render('admin.documents.upload', [
            'csrf'              => $this->csrfToken(),
            'clients'           => $clients,
            'contractsByClient' => $contractsByClient,
            'claimsByClient'    => $claimsByClient,
            'docTypes'          => self::DOC_TYPES,
            'catContrat'        => self::CATEGORIES_CONTRAT,
            'catSinistre'       => self::CATEGORIES_SINISTRE,
            'branchDocTypes'    => ContractDocTypes::all(),
            'genericSouscription' => ContractDocTypes::GENERIC_SOUSCRIPTION,
        ]);
    }

    public function upload(): void
    {
        AdminMiddleware::check();
        $this->verifyCsrf();

        $clientId   = (int)($_POST['client_id']   ?? 0);
        $scope      = $_POST['scope']              ?? '';
        $contractId = (int)($_POST['contract_id']  ?? 0) ?: null;
        $claimId    = (int)($_POST['claim_id']     ?? 0) ?: null;
        $category   = $_POST['category']           ?? '';
        $docType    = trim($_POST['doc_type']      ?? '');

        $validCategories = $scope === 'contrat' ? self::CATEGORIES_CONTRAT : self::CATEGORIES_SINISTRE;
        if (!$clientId || !in_array($scope, ['contrat', 'sinistre'], true) ||
            !in_array($category, $validCategories, true) || !$docType) {
            $_SESSION['admin_flash'] = ['type' => 'danger', 'msg' => 'Tous les champs sont obligatoires.'];
            $this->redirect('/admin/documents/upload');
            return;
        }

        if ($scope === 'contrat' && !$contractId) {
            $_SESSION['admin_flash'] = ['type' => 'danger', 'msg' => 'Veuillez sélectionner un contrat.'];
            $this->redirect('/admin/documents/upload');
            return;
        }
        if ($scope === 'sinistre' && !$claimId) {
            $_SESSION['admin_flash'] = ['type' => 'danger', 'msg' => 'Veuillez sélectionner un sinistre.'];
            $this->redirect('/admin/documents/upload');
            return;
        }

        if (empty($_FILES['document']) || $_FILES['document']['error'] === UPLOAD_ERR_NO_FILE) {
            $_SESSION['admin_flash'] = ['type' => 'danger', 'msg' => 'Aucun fichier sélectionné.'];
            $this->redirect('/admin/documents/upload');
            return;
        }

        // Pour un sinistre, récupérer le contract_id associé
        if ($scope === 'sinistre' && $claimId) {
            $claim      = Claim::find($claimId);
            $contractId = $claim ? $claim['contract_id'] : null;
        }

        try {
            $subdir = $scope === 'contrat'
                ? 'contrats/' . $contractId
                : 'sinistres/' . $claimId;

            $stored = FileStorage::store($_FILES['document'], $subdir);

            Document::create([
                'client_id'         => $clientId,
                'contract_id'       => $scope === 'contrat' ? $contractId : ($claim['contract_id'] ?? null),
                'claim_id'          => $scope === 'sinistre' ? $claimId : null,
                'scope'             => $scope,
                'category'          => $category,
                'doc_type'          => $docType,
                'original_filename' => $stored['original_filename'],
                'stored_path'       => $stored['stored_path'],
                'mime_type'         => $stored['mime_type'],
                'file_size'         => $stored['file_size'],
                'source'            => 'admin',
                'status'            => 'valide',
            ]);

            AuditLogger::log('admin', (int)$_SESSION['admin_id'], 'admin_upload', "client:{$clientId}", $this->ip());
            $_SESSION['admin_flash'] = ['type' => 'success', 'msg' => 'Document uploadé et validé.'];
        } catch (\Throwable $e) {
            $_SESSION['admin_flash'] = ['type' => 'danger', 'msg' => $e->getMessage()];
        }

        $this->redirect('/admin/documents/upload');
    }

    public function pending(): void
    {
        AdminMiddleware::check();
        $this->render('admin.documents.pending', [
            'csrf' => $this->csrfToken(),
            'docs' => Document::pending(),
        ]);
    }

    public function validate(string $id): void
    {
        AdminMiddleware::check();
        $this->verifyCsrf();

        $doc = Document::find((int)$id);
        if (!$doc) {
            $_SESSION['admin_flash'] = ['type' => 'danger', 'msg' => 'Document introuvable.'];
            $this->redirect('/admin/documents/pending');
            return;
        }

        Document::validateDoc((int)$id);
        AuditLogger::log('admin', (int)$_SESSION['admin_id'], 'doc_validated', "document:{$id}", $this->ip());
        $_SESSION['admin_flash'] = ['type' => 'success', 'msg' => 'Document validé.'];
        $this->redirect('/admin/documents/pending');
    }
}
