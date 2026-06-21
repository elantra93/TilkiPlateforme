<?php
declare(strict_types=1);
namespace App\Models;

use App\Services\Database;

class Admin
{
    public static function findByEmail(string $email): ?array
    {
        $stmt = Database::get()->prepare('SELECT * FROM admins WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public static function findById(int $id): ?array
    {
        $stmt = Database::get()->prepare('SELECT * FROM admins WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function all(): array
    {
        return Database::get()
            ->query('SELECT id, email, name, role, created_at FROM admins ORDER BY role ASC, name ASC')
            ->fetchAll();
    }

    public static function create(array $data): int
    {
        $db = Database::get();
        $db->prepare(
            'INSERT INTO admins (email, password_hash, name, role)
             VALUES (:email, :password_hash, :name, :role)'
        )->execute($data);
        return (int)$db->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        Database::get()->prepare(
            'UPDATE admins SET name=:name, email=:email, role=:role WHERE id=:id'
        )->execute(array_merge($data, ['id' => $id]));
    }

    public static function updatePassword(int $id, string $hash): void
    {
        Database::get()->prepare('UPDATE admins SET password_hash = ? WHERE id = ?')
            ->execute([$hash, $id]);
    }

    public static function isEmailTaken(string $email, int $excludeId = 0): bool
    {
        $stmt = Database::get()->prepare('SELECT COUNT(*) FROM admins WHERE email = ? AND id != ?');
        $stmt->execute([$email, $excludeId]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
