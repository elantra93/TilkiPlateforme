<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Models\Contract;
use App\Models\Document;
use App\Services\Auth;

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

        $config        = file_exists(CONFIG_PATH) ? (require CONFIG_PATH) : [];
        $claimFormBase = (string)($config['tally']['claim_form_url'] ?? '');
        $tallyClaimUrl = '';
        if ($claimFormBase) {
            $sep           = str_contains($claimFormBase, '?') ? '&' : '?';
            $tallyClaimUrl = $claimFormBase . $sep
                . 'compte='   . urlencode($client['account_number'])
                . '&police='  . urlencode($contract['policy_number'])
                . '&assureur=' . urlencode($contract['insurer']);
        }

        $this->render('contracts.show', [
            'client'        => $client,
            'contract'      => $contract,
            'documents'     => Document::forContract((int)$id, $clientId),
            'csrf'          => $this->csrfToken(),
            'tallyClaimUrl' => $tallyClaimUrl,
        ]);
    }
}
