<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Models\Contract;
use App\Models\Claim;
use App\Services\Auth;

class DashboardController extends BaseController
{
    public function index(): void
    {
        $this->requireAuth();
        $clientId = (int)$_SESSION['client_id'];

        $this->render('dashboard.index', [
            'client'    => Auth::client(),
            'contracts' => Contract::forClient($clientId),
            'claims'    => Claim::forClient($clientId),
        ]);
    }
}
