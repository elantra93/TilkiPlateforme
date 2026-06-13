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

        $contracts   = Contract::forClient($clientId);
        $openClaims  = Claim::openForClient($clientId);
        $totalDue    = array_sum(array_column($contracts, 'premium_due'));

        $this->render('dashboard.index', [
            'client'     => Auth::client(),
            'contracts'  => $contracts,
            'openClaims' => $openClaims,
            'totalDue'   => (float)$totalDue,
        ]);
    }
}
