<?php
declare(strict_types=1);
namespace App\Models;

use App\Services\Database;

class LoginAttempt
{
    public static function record(string $identifier, string $ip, bool $success): void
    {
        Database::get()->prepare(
            'INSERT INTO login_attempts (identifier, ip, success, created_at) VALUES (?, ?, ?, NOW())'
        )->execute([$identifier, $ip, $success ? 1 : 0]);
    }

    public static function recentFailures(string $identifier, string $ip, int $minutes): int
    {
        $stmt = Database::get()->prepare(
            'SELECT COUNT(*) FROM login_attempts
             WHERE (identifier = ? OR ip = ?)
               AND success = 0
               AND created_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)'
        );
        $stmt->execute([$identifier, $ip, $minutes]);
        return (int)$stmt->fetchColumn();
    }
}
