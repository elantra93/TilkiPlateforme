<?php
declare(strict_types=1);
namespace App\Models;

use App\Services\Database;

class Insurer
{
    public static function all(): array
    {
        $rows = Database::get()
            ->query('SELECT * FROM insurers ORDER BY name ASC')
            ->fetchAll();
        return array_map([self::class, 'decode'], $rows);
    }

    public static function allActive(): array
    {
        $rows = Database::get()
            ->query("SELECT * FROM insurers WHERE is_active = 1 ORDER BY name ASC")
            ->fetchAll();
        return array_map([self::class, 'decode'], $rows);
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::get()->prepare('SELECT * FROM insurers WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ? self::decode($row) : null;
    }

    public static function create(array $data): int
    {
        $db = Database::get();
        $db->prepare(
            'INSERT INTO insurers (name, short_name, country, branches, is_active)
             VALUES (:name, :short_name, :country, :branches, :is_active)'
        )->execute([
            'name'       => $data['name'],
            'short_name' => $data['short_name'] ?? null,
            'country'    => $data['country'],
            'branches'   => self::encodeBranches($data['branches'] ?? []),
            'is_active'  => $data['is_active'],
        ]);
        return (int)$db->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        Database::get()->prepare(
            'UPDATE insurers SET name=:name, short_name=:short_name,
             country=:country, branches=:branches, is_active=:is_active WHERE id=:id'
        )->execute([
            'name'       => $data['name'],
            'short_name' => $data['short_name'] ?? null,
            'country'    => $data['country'],
            'branches'   => self::encodeBranches($data['branches'] ?? []),
            'is_active'  => $data['is_active'],
            'id'         => $id,
        ]);
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

    // Decode branches JSON → array
    private static function decode(array $row): array
    {
        $row['branches'] = isset($row['branches']) && $row['branches'] !== ''
            ? (json_decode($row['branches'], true) ?? [])
            : [];
        return $row;
    }

    private static function encodeBranches(array $branches): ?string
    {
        $clean = array_values(array_filter(array_map('trim', $branches)));
        return $clean ? json_encode($clean, JSON_UNESCAPED_UNICODE) : null;
    }
}
