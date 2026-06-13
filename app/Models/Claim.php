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
}
