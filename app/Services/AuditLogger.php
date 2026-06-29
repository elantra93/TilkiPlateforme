<?php
declare(strict_types=1);
namespace App\Services;

class AuditLogger
{
    public static function log(
        string $actorType,
        ?int   $actorId,
        string $action,
        string $target,
        string $ip
    ): void {
        try {
            Database::get()->prepare(
                'INSERT INTO audit_log (actor_type, actor_id, action, target, ip, created_at)
                 VALUES (?, ?, ?, ?, ?, NOW())'
            )->execute([$actorType, $actorId, $action, $target, $ip]);
        } catch (\Throwable $e) {
            error_log('[AuditLog] ' . $e->getMessage());
        }
    }
}
