<?php
declare(strict_types=1);
namespace App\Models;

use App\Services\Database;

class Payment
{
    public static function create(array $data): int
    {
        $db = Database::get();
        $db->prepare(
            'INSERT INTO payments
             (client_id, contract_id, amount, method, proof_document_id, reference, paid_at, note, created_by)
             VALUES
             (:client_id, :contract_id, :amount, :method, :proof_document_id, :reference, :paid_at, :note, :created_by)'
        )->execute($data);
        return (int)$db->lastInsertId();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::get()->prepare('SELECT * FROM payments WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function listByClient(int $clientId): array
    {
        $stmt = Database::get()->prepare(
            'SELECT p.*, co.policy_number, co.branche, co.insurer,
                    d.id AS doc_id, d.original_filename AS proof_filename
             FROM payments p
             JOIN contracts co ON p.contract_id = co.id
             LEFT JOIN documents d ON p.proof_document_id = d.id
             WHERE p.client_id = ?
             ORDER BY p.paid_at DESC, p.created_at DESC'
        );
        $stmt->execute([$clientId]);
        return $stmt->fetchAll();
    }

    public static function listByContract(int $contractId): array
    {
        $stmt = Database::get()->prepare(
            'SELECT p.*, d.id AS doc_id, d.original_filename AS proof_filename
             FROM payments p
             LEFT JOIN documents d ON p.proof_document_id = d.id
             WHERE p.contract_id = ?
             ORDER BY p.paid_at DESC, p.created_at DESC'
        );
        $stmt->execute([$contractId]);
        return $stmt->fetchAll();
    }

    public static function listAll(): array
    {
        return Database::get()->query(
            'SELECT p.*, cl.first_name, cl.last_name, cl.account_number,
                    co.policy_number, co.branche, co.insurer,
                    d.id AS doc_id, d.original_filename AS proof_filename
             FROM payments p
             JOIN clients cl ON p.client_id = cl.id
             JOIN contracts co ON p.contract_id = co.id
             LEFT JOIN documents d ON p.proof_document_id = d.id
             ORDER BY p.paid_at DESC, p.created_at DESC'
        )->fetchAll();
    }
}
