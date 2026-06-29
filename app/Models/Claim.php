<?php
declare(strict_types=1);
namespace App\Models;

use App\Services\Database;

class Claim
{
    public static function forClient(int $clientId): array
    {
        $stmt = Database::get()->prepare(
            'SELECT cl.*, co.policy_number
             FROM claims cl
             LEFT JOIN contracts co ON cl.contract_id = co.id
             WHERE cl.client_id = ?
             ORDER BY cl.occurrence_date DESC'
        );
        $stmt->execute([$clientId]);
        return $stmt->fetchAll();
    }

    public static function openForClient(int $clientId): array
    {
        $stmt = Database::get()->prepare(
            "SELECT cl.*, co.policy_number
             FROM claims cl
             LEFT JOIN contracts co ON cl.contract_id = co.id
             WHERE cl.client_id = ? AND cl.status = 'ouvert'
             ORDER BY cl.updated_at DESC"
        );
        $stmt->execute([$clientId]);
        return $stmt->fetchAll();
    }

    public static function findForClient(int $id, int $clientId): ?array
    {
        $stmt = Database::get()->prepare(
            'SELECT cl.*, co.policy_number
             FROM claims cl
             LEFT JOIN contracts co ON cl.contract_id = co.id
             WHERE cl.id = ? AND cl.client_id = ?
             LIMIT 1'
        );
        $stmt->execute([$id, $clientId]);
        return $stmt->fetch() ?: null;
    }

    // ── Admin ─────────────────────────────────────────────────────────────────

    public static function all(): array
    {
        return Database::get()->query(
            'SELECT cl.*, c.first_name, c.last_name, c.account_number, co.policy_number
             FROM claims cl
             JOIN clients c ON cl.client_id = c.id
             LEFT JOIN contracts co ON cl.contract_id = co.id
             ORDER BY cl.created_at DESC'
        )->fetchAll();
    }

    public static function countAll(): int
    {
        return (int)Database::get()->query('SELECT COUNT(*) FROM claims')->fetchColumn();
    }

    public static function allPaginated(int $limit, int $offset): array
    {
        $stmt = Database::get()->prepare(
            'SELECT cl.*, c.first_name, c.last_name, c.account_number, co.policy_number
             FROM claims cl
             JOIN clients c ON cl.client_id = c.id
             LEFT JOIN contracts co ON cl.contract_id = co.id
             ORDER BY cl.created_at DESC
             LIMIT ? OFFSET ?'
        );
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::get()->prepare('SELECT * FROM claims WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): int
    {
        $db = Database::get();
        $db->prepare(
            'INSERT INTO claims (client_id, contract_id, claim_number, insurer, branche, occurrence_date, status, description, is_auto_rc, vehicle_id)
             VALUES (:client_id, :contract_id, :claim_number, :insurer, :branche, :occurrence_date, :status, :description, :is_auto_rc, :vehicle_id)'
        )->execute($data);
        return (int)$db->lastInsertId();
    }

    public static function setNumber(int $id, string $number): void
    {
        Database::get()->prepare('UPDATE claims SET claim_number = ? WHERE id = ?')
            ->execute([$number, $id]);
    }

    public static function update(int $id, array $data): void
    {
        Database::get()->prepare(
            'UPDATE claims SET claim_number=:claim_number, contract_id=:contract_id, insurer=:insurer,
             branche=:branche, occurrence_date=:occurrence_date, status=:status, description=:description,
             is_auto_rc=:is_auto_rc, vehicle_id=:vehicle_id
             WHERE id=:id'
        )->execute(array_merge($data, ['id' => $id]));
    }
}
