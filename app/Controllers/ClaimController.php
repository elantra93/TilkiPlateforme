<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Models\Claim;
use App\Models\ClaimStep;
use App\Models\Contract;
use App\Models\Document;
use App\Services\AuditLogger;
use App\Services\Auth;
use App\Services\TallyUrlBuilder;

class ClaimController extends BaseController
{
    public function index(): void
    {
        $this->requireAuth();
        $clientId = (int)$_SESSION['client_id'];

        $this->render('claims.index', [
            'client'    => Auth::client(),
            'claims'    => Claim::forClient($clientId),
            'contracts' => Contract::forClient($clientId),
        ]);
    }

    /**
     * GET /claims/declare
     *
     * Sans contract_id → affiche le sélecteur de contrat.
     * Avec contract_id  → construit l'URL Tally et redirige vers le formulaire.
     */
    public function declare(): void
    {
        $this->requireAuth();
        $clientId   = (int)$_SESSION['client_id'];
        $contractId = (int)($_GET['contract_id'] ?? 0);

        if (!$contractId) {
            $this->render('claims.declare', [
                'contracts' => Contract::forClient($clientId),
            ]);
            return;
        }

        $client   = Auth::client();
        $contract = Contract::findForClient($contractId, $clientId);

        if (!$client || !$contract) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Contrat introuvable.'];
            $this->redirect('/claims/declare');
            return;
        }

        $config    = require CONFIG_PATH;
        $appUrl    = rtrim((string)($config['app']['url'] ?? ''), '/');
        $branche   = strtolower(trim($contract['branche'] ?? ''));
        $attestUrl = '';
        if (in_array($branche, ['automobile', 'auto'], true) && $appUrl) {
            $attest = Document::attestationForContract($contractId);
            if ($attest) {
                $attestUrl = $appUrl . '/documents/' . (int)$attest['id'] . '/download';
            }
        }

        $tallyUrl = TallyUrlBuilder::claimFormUrl($client, $contract, $attestUrl);
        if (!$tallyUrl) {
            $_SESSION['flash'] = ['type' => 'danger', 'msg' => 'Formulaire de déclaration non configuré. Contactez TILKI.'];
            $this->redirect('/claims/declare');
            return;
        }

        AuditLogger::log('client', $clientId, 'tally_claim_redirect',
            "contract:{$contractId}", $this->ip());

        header('Location: ' . $tallyUrl);
        exit;
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

        // Réponses Tally : parse le JSON pour affichage client
        $tallyDecl   = Document::tallyDeclarationForClaim((int)$id);
        $tallyFields = [];
        if ($tallyDecl && file_exists($tallyDecl['stored_path'])) {
            $json = json_decode((string)file_get_contents($tallyDecl['stored_path']), true);
            if (is_array($json)) {
                foreach ($json['data']['fields'] ?? [] as $f) {
                    $type  = $f['type']  ?? '';
                    $val   = $f['value'] ?? null;
                    if (in_array($type, ['HIDDEN_FIELDS', 'FILE_UPLOAD'], true)) continue;
                    if ($val === null || $val === '' || $val === []) continue;
                    $tallyFields[] = [
                        'label' => (string)($f['label'] ?? $f['key'] ?? ''),
                        'value' => is_array($val) ? implode(', ', $val) : (string)$val,
                    ];
                }
            }
        }

        $this->render('claims.show', [
            'client'      => Auth::client(),
            'claim'       => $claim,
            'steps'       => ClaimStep::forClaim((int)$id),
            'documents'   => Document::forClaim((int)$id, $clientId),
            'tallyFields' => $tallyFields,
            'csrf'        => $this->csrfToken(),
        ]);
    }
}
