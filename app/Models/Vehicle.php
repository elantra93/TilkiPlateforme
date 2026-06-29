<?php
declare(strict_types=1);
namespace App\Models;

use App\Services\Database;

class Vehicle
{
    public const ENERGIES = ['essence', 'diesel', 'hybride', 'electrique', 'autre'];
    public const USAGES   = ['personnel', 'commercial', 'mixte'];

    public static function forContract(int $contractId): array
    {
        $stmt = Database::get()->prepare(
            'SELECT * FROM vehicles WHERE contract_id = ? ORDER BY created_at ASC'
        );
        $stmt->execute([$contractId]);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::get()->prepare('SELECT * FROM vehicles WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $data): int
    {
        $db = Database::get();
        $db->prepare(
            'INSERT INTO vehicles
             (contract_id, client_id, immatriculation, marque, modele, annee, energie, usage, valeur_venale)
             VALUES
             (:contract_id, :client_id, :immatriculation, :marque, :modele, :annee, :energie, :usage, :valeur_venale)'
        )->execute($data);
        return (int)$db->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $data['id'] = $id;
        Database::get()->prepare(
            'UPDATE vehicles
             SET immatriculation=:immatriculation, marque=:marque, modele=:modele,
                 annee=:annee, energie=:energie, usage=:usage, valeur_venale=:valeur_venale
             WHERE id=:id'
        )->execute($data);
    }

    public static function delete(int $id): void
    {
        Database::get()->prepare('DELETE FROM vehicles WHERE id = ?')->execute([$id]);
    }

    // Retourne un tableau [contract_id => count] pour un client donné
    public static function countByContractForClient(int $clientId): array
    {
        $stmt = Database::get()->prepare(
            'SELECT contract_id, COUNT(*) AS cnt FROM vehicles WHERE client_id = ? GROUP BY contract_id'
        );
        $stmt->execute([$clientId]);
        $map = [];
        foreach ($stmt->fetchAll() as $row) {
            $map[(int)$row['contract_id']] = (int)$row['cnt'];
        }
        return $map;
    }
}
