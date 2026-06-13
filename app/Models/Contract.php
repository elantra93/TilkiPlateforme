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
}
