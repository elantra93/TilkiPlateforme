<?php
declare(strict_types=1);
namespace App\Models;

use App\Services\Database;

class Beneficiary
{
    public const RELATIONS = ['souscripteur', 'conjoint', 'enfant', 'parent', 'autre'];
    public const GENDERS   = ['M' => 'Masculin', 'F' => 'Féminin'];

    public static function forContract(int $contractId): array
    {
        $stmt = Database::get()->prepare(
            'SELECT * FROM beneficiaries WHERE contract_id = ?
             ORDER BY is_principal DESC, relation ASC, last_name ASC'
        );
        $stmt->execute([$contractId]);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::get()->prepare('SELECT * FROM beneficiaries WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): int
    {
        $db = Database::get();
        if ($data['is_principal']) {
            $db->prepare(
                'UPDATE beneficiaries SET is_principal = 0 WHERE contract_id = ?'
            )->execute([$data['contract_id']]);
        }
        $db->prepare(
            'INSERT INTO beneficiaries
             (contract_id, client_id, first_name, last_name, birth_date, gender, relation, is_principal, matricule)
             VALUES
             (:contract_id, :client_id, :first_name, :last_name, :birth_date, :gender, :relation, :is_principal, :matricule)'
        )->execute($data);
        return (int)$db->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $db = Database::get();
        if ($data['is_principal']) {
            $existing = self::find($id);
            if ($existing) {
                $db->prepare(
                    'UPDATE beneficiaries SET is_principal = 0 WHERE contract_id = ? AND id != ?'
                )->execute([$existing['contract_id'], $id]);
            }
        }
        $data['id'] = $id;
        $db->prepare(
            'UPDATE beneficiaries
             SET first_name=:first_name, last_name=:last_name, birth_date=:birth_date,
                 gender=:gender, relation=:relation, is_principal=:is_principal, matricule=:matricule
             WHERE id=:id'
        )->execute($data);
    }

    public static function delete(int $id): void
    {
        Database::get()->prepare('DELETE FROM beneficiaries WHERE id = ?')->execute([$id]);
    }
}
