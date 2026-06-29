<?php
declare(strict_types=1);
namespace App\Models;

use App\Services\Database;

class Relance
{
    // Days offset from expiry_date (positive = before, negative = after)
    public const TYPES = [
        'j-60'     => 60,
        'j-30'     => 30,
        'j-15'     => 15,
        'j-7'      => 7,
        'echeance' => 0,
        'j+7'      => -7,
        'j+30'     => -30,
    ];

    public const TYPE_LABELS = [
        'j-60'     => '60 j. avant échéance',
        'j-30'     => '30 j. avant échéance',
        'j-15'     => '15 j. avant échéance',
        'j-7'      => '7 j. avant échéance',
        'echeance' => 'Jour de l\'échéance',
        'j+7'      => '7 j. après échéance',
        'j+30'     => '30 j. après échéance',
    ];

    public static function forContract(int $contractId): array
    {
        $stmt = Database::get()->prepare(
            'SELECT * FROM relances WHERE contract_id = ? ORDER BY created_at DESC'
        );
        $stmt->execute([$contractId]);
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $db = Database::get();
        $db->prepare(
            'INSERT INTO relances (contract_id, client_id, type, channel, status, admin_id)
             VALUES (:contract_id, :client_id, :type, :channel, :status, :admin_id)'
        )->execute($data);
        return (int)$db->lastInsertId();
    }

    public static function markSent(int $id): void
    {
        Database::get()->prepare(
            "UPDATE relances SET status='envoyee', sent_at=NOW() WHERE id=?"
        )->execute([$id]);
    }

    public static function markFailed(int $id, string $error): void
    {
        Database::get()->prepare(
            "UPDATE relances SET status='echouee', error_message=? WHERE id=?"
        )->execute([mb_substr($error, 0, 500), $id]);
    }

    public static function hasSentType(int $contractId, string $type): bool
    {
        $stmt = Database::get()->prepare(
            "SELECT COUNT(*) FROM relances WHERE contract_id=? AND type=? AND status='envoyee'"
        );
        $stmt->execute([$contractId, $type]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /** Returns the relance type due TODAY for a given expiry date, or null if none. */
    public static function dueTypeForExpiry(string $expiryDate): ?string
    {
        $today  = (int)(new \DateTime('today'))->format('Ymd');
        $expiry = (int)(new \DateTime($expiryDate))->format('Ymd');
        $diff   = (int)round(
            ((new \DateTime($expiryDate))->getTimestamp() - (new \DateTime('today'))->getTimestamp()) / 86400
        );

        foreach (self::TYPES as $type => $offset) {
            if ($diff === $offset) {
                return $type;
            }
        }
        return null;
    }
}
