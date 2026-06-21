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
             (contract_id, client_id, amount, method, document_id, status, created_by,
              validated_by, validated_at, reference, paid_at, note)
             VALUES
             (:contract_id, :client_id, :amount, :method, :document_id, :status, :created_by,
              :validated_by, :validated_at, :reference, :paid_at, :note)'
        )->execute([
            'contract_id'  => $data['contract_id'],
            'client_id'    => $data['client_id'],
            'amount'       => $data['amount'],
            'method'       => $data['method'],
            'document_id'  => $data['document_id']  ?? null,
            'status'       => $data['status']        ?? 'en_attente',
            'created_by'   => $data['created_by'],
            'validated_by' => $data['validated_by'] ?? null,
            'validated_at' => $data['validated_at'] ?? null,
            'reference'    => $data['reference']    ?? null,
            'paid_at'      => $data['paid_at']      ?? null,
            'note'         => $data['note']         ?? null,
        ]);
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
             LEFT JOIN documents d ON p.document_id = d.id
             WHERE p.client_id = ?
             ORDER BY p.created_at DESC'
        );
        $stmt->execute([$clientId]);
        return $stmt->fetchAll();
    }

    public static function listByContract(int $contractId): array
    {
        $stmt = Database::get()->prepare(
            'SELECT p.*, d.id AS doc_id, d.original_filename AS proof_filename
             FROM payments p
             LEFT JOIN documents d ON p.document_id = d.id
             WHERE p.contract_id = ?
             ORDER BY p.created_at DESC'
        );
        $stmt->execute([$contractId]);
        return $stmt->fetchAll();
    }

    public static function sumValidated(int $contractId): float
    {
        $stmt = Database::get()->prepare(
            "SELECT COALESCE(SUM(amount), 0)
             FROM payments
             WHERE contract_id = ? AND status = 'valide'"
        );
        $stmt->execute([$contractId]);
        return (float)$stmt->fetchColumn();
    }

    public static function sumValidatedMap(): array
    {
        $rows = Database::get()
            ->query("SELECT contract_id, COALESCE(SUM(amount), 0) AS total
                     FROM payments WHERE status = 'valide'
                     GROUP BY contract_id")
            ->fetchAll();
        $map = [];
        foreach ($rows as $r) {
            $map[(int)$r['contract_id']] = (float)$r['total'];
        }
        return $map;
    }

    public static function validate(int $id, float $amount, int $adminId): bool
    {
        $stmt = Database::get()->prepare(
            "UPDATE payments
             SET status='valide', amount=:amount,
                 validated_by=:validated_by, validated_at=NOW()
             WHERE id=:id AND status='en_attente'"
        );
        $stmt->execute(['id' => $id, 'amount' => $amount, 'validated_by' => $adminId]);
        return $stmt->rowCount() > 0;
    }

    public static function findForContract(int $id, int $contractId): ?array
    {
        $stmt = Database::get()->prepare(
            'SELECT * FROM payments WHERE id = ? AND contract_id = ? LIMIT 1'
        );
        $stmt->execute([$id, $contractId]);
        return $stmt->fetch() ?: null;
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
             LEFT JOIN documents d ON p.document_id = d.id
             ORDER BY p.created_at DESC'
        )->fetchAll();
    }
}
