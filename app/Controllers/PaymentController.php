<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Models\Payment;
use App\Services\Auth;

class PaymentController extends BaseController
{
    public function index(): void
    {
        $this->requireAuth();
        $clientId = (int)$_SESSION['client_id'];

        $this->render('payments.index', [
            'client'   => Auth::client(),
            'payments' => Payment::listByClient($clientId),
        ]);
    }
}
