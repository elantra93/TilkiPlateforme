<?php
declare(strict_types=1);
namespace App\Models;

use App\Services\Database;

class TallyQueue
{
    public static function responseExists(string $responseId): bool
    {
        $stmt = Database::get()->prepare(
            'SELECT COUNT(*) FROM tally_queue WHERE response_id = ?'
        );
        $stmt->execute([$responseId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public static function create(array $data): int
    {
        $db = Database::get();
        $db->prepare(
            'INSERT INTO tally_queue (event_id, response_id, form_id, form_name, payload)
             VALUES (:event_id, :response_id, :form_id, :form_name, :payload)'
        )->execute($data);
        return (int)$db->lastInsertId();
    }

    public static function pending(): array
    {
        return Database::get()->query(
            "SELECT tq.*, c.first_name, c.last_name, c.account_number
             FROM tally_queue tq
             LEFT JOIN clients c ON tq.client_id = c.id
             WHERE tq.status = 'pending'
             ORDER BY tq.created_at DESC"
        )->fetchAll();
    }

    public static function all(): array
    {
        return Database::get()->query(
            'SELECT tq.*, c.first_name, c.last_name, c.account_number
             FROM tally_queue tq
             LEFT JOIN clients c ON tq.client_id = c.id
             ORDER BY tq.created_at DESC'
        )->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::get()->prepare('SELECT * FROM tally_queue WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function match(int $id, int $clientId): void
    {
        Database::get()->prepare(
            "UPDATE tally_queue SET status = 'matched', client_id = ? WHERE id = ?"
        )->execute([$clientId, $id]);
    }

    public static function ignore(int $id): void
    {
        Database::get()->prepare(
            "UPDATE tally_queue SET status = 'ignored' WHERE id = ?"
        )->execute([$id]);
    }

    public static function pendingCount(): int
    {
        return (int)Database::get()
            ->query("SELECT COUNT(*) FROM tally_queue WHERE status = 'pending'")
            ->fetchColumn();
    }
}
