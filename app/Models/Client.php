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

    public static function findByEmail(string $email): ?array
    {
        $stmt = Database::get()->prepare(
            "SELECT * FROM clients WHERE email = ? AND status = 'actif' LIMIT 1"
        );
        $stmt->execute([$email]);
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

    public static function setResetToken(int $id, string $token): void
    {
        Database::get()->prepare(
            'UPDATE clients SET reset_token = ?, reset_token_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR), updated_at = NOW() WHERE id = ?'
        )->execute([$token, $id]);
    }

    public static function findByResetToken(string $token): ?array
    {
        $stmt = Database::get()->prepare(
            "SELECT * FROM clients WHERE reset_token = ? AND reset_token_expires > NOW() AND status = 'actif' LIMIT 1"
        );
        $stmt->execute([$token]);
        return $stmt->fetch() ?: null;
    }

    public static function clearResetToken(int $id): void
    {
        Database::get()->prepare(
            'UPDATE clients SET reset_token = NULL, reset_token_expires = NULL, updated_at = NOW() WHERE id = ?'
        )->execute([$id]);
    }
}
