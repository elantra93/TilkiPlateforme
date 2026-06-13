<?php
declare(strict_types=1);
namespace App\Models;

use App\Services\Database;

class Contract
{
    public static function forClient(int $clientId): array
    {
        $stmt = Database::get()->prepare(
            'SELECT * FROM contracts WHERE client_id = ? ORDER BY effective_date DESC'
        );
        $stmt->execute([$clientId]);
        return $stmt->fetchAll();
    }

    public static function findForClient(int $id, int $clientId): ?array
    {
        $stmt = Database::get()->prepare(
            'SELECT * FROM contracts WHERE id = ? AND client_id = ? LIMIT 1'
        );
        $stmt->execute([$id, $clientId]);
        return $stmt->fetch() ?: null;
    }

    // ── Admin ─────────────────────────────────────────────────────────────────

    public static function all(): array
    {
        return Database::get()->query(
            'SELECT c.*, cl.first_name, cl.last_name, cl.account_number
             FROM contracts c
             JOIN clients cl ON c.client_id = cl.id
             ORDER BY c.created_at DESC'
        )->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::get()->prepare('SELECT * FROM contracts WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): int
    {
        $db = Database::get();
        $db->prepare(
            'INSERT INTO contracts (client_id, branche, policy_number, insurer, effective_date, expiry_date, premium_total, premium_due, currency, status)
             VALUES (:client_id, :branche, :policy_number, :insurer, :effective_date, :expiry_date, :premium_total, :premium_due, :currency, :status)'
        )->execute($data);
        return (int)$db->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        Database::get()->prepare(
            'UPDATE contracts SET branche=:branche, policy_number=:policy_number, insurer=:insurer,
             effective_date=:effective_date, expiry_date=:expiry_date, premium_total=:premium_total,
             premium_due=:premium_due, currency=:currency, status=:status WHERE id=:id'
        )->execute(array_merge($data, ['id' => $id]));
    }
}
