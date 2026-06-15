<?php
declare(strict_types=1);
namespace App\Models;

use App\Services\Database;

class ClaimStep
{
    public const ALL_STEPS = [
        ['step_key' => 'declaration',               'label' => 'Déclaration du sinistre',                          'position' => 1, 'auto_rc_only' => false],
        ['step_key' => 'instruction',               'label' => 'Instruction du sinistre',                          'position' => 2, 'auto_rc_only' => false],
        ['step_key' => 'mise_en_cause',             'label' => "Mise en cause de l'adversaire",                    'position' => 3, 'auto_rc_only' => true],
        ['step_key' => 'reconnaissance_resp',       'label' => "Reconnaissance de responsabilité de l'adversaire", 'position' => 4, 'auto_rc_only' => true],
        ['step_key' => 'proposition_indemnisation', 'label' => "Proposition d'indemnisation",                      'position' => 5, 'auto_rc_only' => false],
        ['step_key' => 'indemnisation',             'label' => 'Indemnisation',                                    'position' => 6, 'auto_rc_only' => false],
    ];

    public static function forClaim(int $claimId): array
    {
        $stmt = Database::get()->prepare(
            'SELECT * FROM claim_steps WHERE claim_id = ? ORDER BY position ASC'
        );
        $stmt->execute([$claimId]);
        return $stmt->fetchAll();
    }

    public static function initForClaim(int $claimId, bool $isAutoRc): void
    {
        $db   = Database::get();
        $stmt = $db->prepare(
            'INSERT IGNORE INTO claim_steps (claim_id, step_key, label, position, completed)
             VALUES (:claim_id, :step_key, :label, :position, 0)'
        );
        foreach (self::ALL_STEPS as $s) {
            if ($s['auto_rc_only'] && !$isAutoRc) {
                continue;
            }
            $stmt->execute([
                'claim_id' => $claimId,
                'step_key' => $s['step_key'],
                'label'    => $s['label'],
                'position' => $s['position'],
            ]);
        }
    }

    public static function rebuildForClaim(int $claimId, bool $isAutoRc): void
    {
        $db = Database::get();
        if ($isAutoRc) {
            $stmt = $db->prepare(
                'INSERT IGNORE INTO claim_steps (claim_id, step_key, label, position, completed)
                 VALUES (:claim_id, :step_key, :label, :position, 0)'
            );
            foreach (self::ALL_STEPS as $s) {
                if (!$s['auto_rc_only']) {
                    continue;
                }
                $stmt->execute([
                    'claim_id' => $claimId,
                    'step_key' => $s['step_key'],
                    'label'    => $s['label'],
                    'position' => $s['position'],
                ]);
            }
        } else {
            $db->prepare(
                "DELETE FROM claim_steps
                 WHERE claim_id = ? AND step_key IN ('mise_en_cause', 'reconnaissance_resp')"
            )->execute([$claimId]);
        }
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::get()->prepare('SELECT * FROM claim_steps WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function update(int $id, bool $completed, ?string $completedDate): void
    {
        Database::get()->prepare(
            'UPDATE claim_steps SET completed = :completed, completed_date = :completed_date WHERE id = :id'
        )->execute([
            'completed'      => (int)$completed,
            'completed_date' => $completed ? $completedDate : null,
            'id'             => $id,
        ]);
    }
}
