<?php
declare(strict_types=1);
namespace App\Middleware;

class AuthMiddleware
{
    public static function check(): void
    {
        if (empty($_SESSION['client_id'])) {
            header('Location: /login');
            exit;
        }

        if (!empty($_SESSION['must_change_password'])) {
            $path = (string)parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
            if (!in_array($path, ['/password/change', '/logout'], true)) {
                header('Location: /password/change');
                exit;
            }
        }
    }
}
