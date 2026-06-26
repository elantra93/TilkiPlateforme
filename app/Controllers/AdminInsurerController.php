<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Middleware\AdminMiddleware;
use App\Models\Insurer;
use App\Services\AuditLogger;

class AdminInsurerController extends BaseController
{
    public function index(): void
    {
        AdminMiddleware::check();
        $this->render('admin.insurers.index', [
            'insurers' => Insurer::all(),
        ]);
    }

    public function showCreate(): void
    {
        AdminMiddleware::check();
        $this->render('admin.insurers.form', [
            'csrf'    => $this->csrfToken(),
            'insurer' => null,
            'old'     => [],
        ]);
    }

    public function create(): void
    {
        AdminMiddleware::check();
        $this->verifyCsrf();

        ['name' => $name, 'short_name' => $shortName, 'country' => $country, 'is_active' => $isActive]
            = $this->extractPost();

        if (!$name) {
            $this->render('admin.insurers.form', [
                'csrf'    => $this->csrfToken(),
                'insurer' => null,
                'old'     => compact('name', 'shortName', 'country', 'isActive'),
                'error'   => 'La dénomination est obligatoire.',
            ]);
            return;
        }

        if (Insurer::isNameTaken($name)) {
            $this->render('admin.insurers.form', [
                'csrf'    => $this->csrfToken(),
                'insurer' => null,
                'old'     => compact('name', 'shortName', 'country', 'isActive'),
                'error'   => "Un assureur nommé « {$name} » existe déjà.",
            ]);
            return;
        }

        $id = Insurer::create([
            'name'       => $name,
            'short_name' => $shortName ?: null,
            'country'    => $country,
            'is_active'  => $isActive,
        ]);
        AuditLogger::log('admin', (int)$_SESSION['admin_id'], 'insurer_created', "insurer:{$id}", $this->ip());
        $_SESSION['admin_flash'] = ['type' => 'success', 'msg' => "Assureur « {$name} » créé."];
        $this->redirect('/admin/insurers');
    }

    public function showEdit(string $id): void
    {
        AdminMiddleware::check();
        $insurer = Insurer::find((int)$id);
        if (!$insurer) {
            http_response_code(404);
            require APP_PATH . '/Views/errors/404.php';
            return;
        }
        $this->render('admin.insurers.form', [
            'csrf'    => $this->csrfToken(),
            'insurer' => $insurer,
            'old'     => [],
        ]);
    }

    public function edit(string $id): void
    {
        AdminMiddleware::check();
        $this->verifyCsrf();

        $insurer = Insurer::find((int)$id);
        if (!$insurer) {
            http_response_code(404);
            require APP_PATH . '/Views/errors/404.php';
            return;
        }

        ['name' => $name, 'short_name' => $shortName, 'country' => $country, 'is_active' => $isActive]
            = $this->extractPost();

        if (!$name) {
            $this->render('admin.insurers.form', [
                'csrf'    => $this->csrfToken(),
                'insurer' => $insurer,
                'old'     => compact('name', 'shortName', 'country', 'isActive'),
                'error'   => 'La dénomination est obligatoire.',
            ]);
            return;
        }

        if (Insurer::isNameTaken($name, (int)$id)) {
            $this->render('admin.insurers.form', [
                'csrf'    => $this->csrfToken(),
                'insurer' => $insurer,
                'old'     => compact('name', 'shortName', 'country', 'isActive'),
                'error'   => "Un autre assureur porte déjà ce nom.",
            ]);
            return;
        }

        Insurer::update((int)$id, [
            'name'       => $name,
            'short_name' => $shortName ?: null,
            'country'    => $country,
            'is_active'  => $isActive,
        ]);
        AuditLogger::log('admin', (int)$_SESSION['admin_id'], 'insurer_updated', "insurer:{$id}", $this->ip());
        $_SESSION['admin_flash'] = ['type' => 'success', 'msg' => 'Assureur mis à jour.'];
        $this->redirect('/admin/insurers');
    }

    public function toggleActive(string $id): void
    {
        AdminMiddleware::check();
        $this->verifyCsrf();

        $insurer = Insurer::find((int)$id);
        if (!$insurer) {
            http_response_code(404);
            require APP_PATH . '/Views/errors/404.php';
            return;
        }

        Insurer::toggleActive((int)$id);
        $label = $insurer['is_active'] ? 'désactivé' : 'réactivé';
        AuditLogger::log('admin', (int)$_SESSION['admin_id'], 'insurer_toggled', "insurer:{$id}", $this->ip());
        $_SESSION['admin_flash'] = ['type' => 'success', 'msg' => "Assureur {$label}."];
        $this->redirect('/admin/insurers');
    }

    private function extractPost(): array
    {
        return [
            'name'       => trim($_POST['name']       ?? ''),
            'short_name' => trim($_POST['short_name'] ?? ''),
            'country'    => trim($_POST['country']    ?? '') ?: "Côte d'Ivoire",
            'is_active'  => isset($_POST['is_active']) ? 1 : 0,
        ];
    }
}
