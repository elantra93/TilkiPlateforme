<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Middleware\AdminMiddleware;
use App\Models\Contract;
use App\Models\Relance;
use App\Services\RelanceEngine;

class AdminRelanceController extends BaseController
{
    public function index(): void
    {
        AdminMiddleware::check();
        $contracts = Contract::expiringSoon(60, 30);

        foreach ($contracts as &$c) {
            $c['due_type']    = Relance::dueTypeForExpiry($c['expiry_date']);
            $c['already_sent'] = $c['due_type']
                ? Relance::hasSentType((int)$c['id'], $c['due_type'])
                : false;
        }
        unset($c);

        $this->render('admin.relances.index', [
            'contracts' => $contracts,
            'csrf'      => $this->csrfToken(),
            'typeLabels'=> Relance::TYPE_LABELS,
        ]);
    }

    public function forContract(string $id): void
    {
        AdminMiddleware::check();
        $contract = Contract::find((int)$id);
        if (!$contract) {
            http_response_code(404);
            require APP_PATH . '/Views/errors/404.php';
            return;
        }
        $this->render('admin.relances.contract', [
            'contract'  => $contract,
            'relances'  => Relance::forContract((int)$id),
            'csrf'      => $this->csrfToken(),
            'typeLabels'=> Relance::TYPE_LABELS,
        ]);
    }

    public function send(string $id): void
    {
        AdminMiddleware::check();
        $this->verifyCsrf();

        $contract = Contract::find((int)$id);
        if (!$contract) {
            http_response_code(404);
            require APP_PATH . '/Views/errors/404.php';
            return;
        }

        $type = trim($_POST['type'] ?? '');
        if (!array_key_exists($type, Relance::TYPES)) {
            $_SESSION['admin_flash'] = ['type' => 'danger', 'msg' => 'Type de relance invalide.'];
            $this->redirect('/admin/relances/' . $id);
            return;
        }

        $ok = RelanceEngine::sendManual($contract, $type, (int)$_SESSION['admin_id']);

        $_SESSION['admin_flash'] = $ok
            ? ['type' => 'success', 'msg' => 'Relance envoyée avec succès.']
            : ['type' => 'danger',  'msg' => 'Échec de l\'envoi. Vérifiez l\'email du client et la config SMTP.'];

        $this->redirect('/admin/relances/' . $id);
    }

    public function runAll(): void
    {
        AdminMiddleware::check();
        $this->verifyCsrf();

        $summary = RelanceEngine::processAll();
        $msg = sprintf(
            '%d relance(s) envoyée(s), %d déjà traitée(s), %d échouée(s).',
            $summary['sent'],
            $summary['skipped'],
            $summary['failed']
        );
        $type = $summary['failed'] > 0 ? 'warning' : 'success';

        $_SESSION['admin_flash'] = ['type' => $type, 'msg' => $msg];
        $this->redirect('/admin/relances');
    }
}
