<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Models\Contract;
use App\Models\Document;
use App\Services\Auth;
use App\Services\ContractDocTypes;
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
            'csrf'           => $this->csrfToken(),
            'tallyClaimUrl'  => $tallyClaimUrl,
            'branchDocTypes' => ContractDocTypes::forBranche($contract['branche']),
        ]);
    }
}
