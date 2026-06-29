<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Middleware\AuthMiddleware;

abstract class BaseController
{
    protected function requireAuth(): void
    {
        AuthMiddleware::check();
    }

    protected function render(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $file = APP_PATH . '/Views/' . str_replace('.', '/', $view) . '.php';
        if (!file_exists($file)) {
            throw new \RuntimeException("Vue introuvable : {$view}");
        }
        require $file;
    }

    protected function redirect(string $path): never
    {
        header('Location: ' . $path);
        exit;
    }

    protected function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    protected function verifyCsrf(): void
    {
        $token = $_POST['_csrf'] ?? '';
        if (!hash_equals((string)($_SESSION['csrf_token'] ?? ''), $token)) {
            http_response_code(403);
            exit('Token CSRF invalide.');
        }
    }

    protected function ip(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}
