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
        $contract = Contract::findForClient((int)$id, $clientId);

        if (!$contract) {
            http_response_code(404);
            require APP_PATH . '/Views/errors/404.php';
            return;
        }

        $this->render('contracts.show', [
            'client'    => Auth::client(),
            'contract'  => $contract,
            'documents' => Document::forContract((int)$id, $clientId),
            'csrf'      => $this->csrfToken(),
        ]);
    }
}
