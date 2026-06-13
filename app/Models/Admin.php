<?php
declare(strict_types=1);
namespace App\Models;

use App\Services\Database;

class Admin
{
    public static function findByEmail(string $email): ?array
    {
        $stmt = Database::get()->prepare(
            'SELECT * FROM admins WHERE email = ? LIMIT 1'
        );
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public static function findById(int $id): ?array
    {
        $stmt = Database::get()->prepare('SELECT * FROM admins WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
}
