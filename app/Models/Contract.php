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

    public static function findByPolicyForClient(string $policyNumber, int $clientId): ?array
    {
        $stmt = Database::get()->prepare(
            'SELECT * FROM contracts WHERE policy_number = ? AND client_id = ? LIMIT 1'
        );
        $stmt->execute([$policyNumber, $clientId]);
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

    /**
     * Active contracts expiring within [today - $overdueMax .. today + $daysAhead].
     * Used by the relance échéancier view and RelanceEngine.
     */
    public static function expiringSoon(int $daysAhead = 60, int $overdueMax = 30): array
    {
        $stmt = Database::get()->prepare(
            "SELECT c.*, cl.first_name, cl.last_name, cl.email, cl.account_number,
                    DATEDIFF(c.expiry_date, CURDATE()) AS days_until_expiry
             FROM contracts c
             JOIN clients cl ON c.client_id = cl.id
             WHERE c.status = 'actif'
               AND c.expiry_date BETWEEN DATE_SUB(CURDATE(), INTERVAL :overdue DAY)
                                     AND DATE_ADD(CURDATE(), INTERVAL :ahead DAY)
             ORDER BY c.expiry_date ASC"
        );
        $stmt->execute(['overdue' => $overdueMax, 'ahead' => $daysAhead]);
        return $stmt->fetchAll();
    }

    public static function updateRelanceStatus(int $id, string $status): void
    {
        Database::get()->prepare(
            "UPDATE contracts
             SET relance_statut=:status, relance_derniere_at=:at
             WHERE id=:id"
        )->execute(['status' => $status, 'at' => date('Y-m-d H:i:s'), 'id' => $id]);
    }

    public static function create(array $data): int
    {
        $db = Database::get();
        $db->prepare(
            'INSERT INTO contracts
             (client_id, branche, policy_number, insurer, insurer_id,
              effective_date, expiry_date, emission_date,
              premium_total, premium_due, premium_net, premium_fees,
              currency, status)
             VALUES
             (:client_id, :branche, :policy_number, :insurer, :insurer_id,
              :effective_date, :expiry_date, :emission_date,
              :premium_total, :premium_due, :premium_net, :premium_fees,
              :currency, :status)'
        )->execute($data);
        return (int)$db->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        Database::get()->prepare(
            'UPDATE contracts
             SET branche=:branche, policy_number=:policy_number, insurer=:insurer, insurer_id=:insurer_id,
                 effective_date=:effective_date, expiry_date=:expiry_date, emission_date=:emission_date,
                 premium_total=:premium_total, premium_net=:premium_net, premium_fees=:premium_fees,
                 currency=:currency, status=:status
             WHERE id=:id'
        )->execute(array_merge($data, ['id' => $id]));
    }
}
