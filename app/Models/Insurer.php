<?php
declare(strict_types=1);
namespace App\Models;

use App\Services\Database;

class Insurer
{
    public static function all(): array
    {
        return Database::get()
            ->query('SELECT * FROM insurers ORDER BY name ASC')
            ->fetchAll();
    }

    public static function allActive(): array
    {
        return Database::get()
            ->query("SELECT * FROM insurers WHERE is_active = 1 ORDER BY name ASC")
            ->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::get()->prepare('SELECT * FROM insurers WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): int
    {
        $db = Database::get();
        $db->prepare(
            'INSERT INTO insurers (name, short_name, country, is_active)
             VALUES (:name, :short_name, :country, :is_active)'
        )->execute($data);
        return (int)$db->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $data['id'] = $id;
        Database::get()->prepare(
            'UPDATE insurers SET name=:name, short_name=:short_name,
             country=:country, is_active=:is_active WHERE id=:id'
        )->execute($data);
    }

    public static function toggleActive(int $id): void
    {
        Database::get()->prepare(
            'UPDATE insurers SET is_active = 1 - is_active WHERE id = ?'
        )->execute([$id]);
    }

    public static function isNameTaken(string $name, ?int $excludeId = null): bool
    {
        if ($excludeId) {
            $stmt = Database::get()->prepare(
                'SELECT COUNT(*) FROM insurers WHERE name = ? AND id != ?'
            );
            $stmt->execute([$name, $excludeId]);
        } else {
            $stmt = Database::get()->prepare('SELECT COUNT(*) FROM insurers WHERE name = ?');
            $stmt->execute([$name]);
        }
        return (int)$stmt->fetchColumn() > 0;
    }
}
