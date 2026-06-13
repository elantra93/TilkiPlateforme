<?php
declare(strict_types=1);
namespace App\Models;

use App\Services\Database;

class Client
{
    public static function findByAccountNumber(string $accountNumber): ?array
    {
        $stmt = Database::get()->prepare(
            "SELECT * FROM clients WHERE account_number = ? AND status = 'actif' LIMIT 1"
        );
        $stmt->execute([$accountNumber]);
        return $stmt->fetch() ?: null;
    }

    public static function findById(int $id): ?array
    {
        $stmt = Database::get()->prepare('SELECT * FROM clients WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function updatePasswordHash(int $id, string $hash): void
    {
        Database::get()->prepare(
            'UPDATE clients SET password_hash = ?, must_change_password = 0, updated_at = NOW() WHERE id = ?'
        )->execute([$hash, $id]);
    }
}
