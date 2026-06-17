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

    // ── Admin ─────────────────────────────────────────────────────────────────

    public static function all(): array
    {
        return Database::get()
            ->query('SELECT id, account_number, first_name, last_name, email, phone, status, created_at FROM clients ORDER BY created_at DESC')
            ->fetchAll();
    }

    public static function create(array $data): int
    {
        $db = Database::get();
        $db->prepare(
            'INSERT INTO clients (account_number, first_name, last_name, email, phone, password_hash, must_change_password, status)
             VALUES (:account_number, :first_name, :last_name, :email, :phone, :password_hash, :must_change_password, :status)'
        )->execute($data);
        return (int)$db->lastInsertId();
    }

    public static function nextAccountNumber(): string
    {
        $prefix = date('y'); // ex. "26" pour 2026
        $stmt = Database::get()->prepare(
            "SELECT MAX(CAST(SUBSTR(account_number, 3) AS UNSIGNED))
             FROM clients
             WHERE account_number LIKE ?"
        );
        $stmt->execute([$prefix . '%']);
        $maxSeq = (int)$stmt->fetchColumn();
        return $prefix . str_pad((string)($maxSeq + 1), 4, '0', STR_PAD_LEFT);
    }

    public static function isAccountNumberTaken(string $num): bool
    {
        $stmt = Database::get()->prepare('SELECT COUNT(*) FROM clients WHERE account_number = ?');
        $stmt->execute([$num]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
