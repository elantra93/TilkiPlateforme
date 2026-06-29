<?php
declare(strict_types=1);
namespace App\Middleware;

class AdminMiddleware
{
    public static function check(): void
    {
        if (empty($_SESSION['admin_id'])) {
            header('Location: /admin/login');
            exit;
        }
    }
}
