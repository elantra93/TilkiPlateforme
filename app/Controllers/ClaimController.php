<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Models\Claim;
use App\Models\Document;
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
            'documents' => Document::forClaim((int)$id, $clientId),
            'csrf'      => $this->csrfToken(),
        ]);
    }
}
