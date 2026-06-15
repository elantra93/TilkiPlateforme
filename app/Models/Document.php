<?php
declare(strict_types=1);
namespace App\Models;

use App\Services\Database;

class Document
{
    public static function forContract(int $contractId, int $clientId): array
    {
        $stmt = Database::get()->prepare(
            "SELECT * FROM documents
             WHERE contract_id = ? AND client_id = ? AND scope = 'contrat'
             ORDER BY created_at DESC"
        );
        $stmt->execute([$contractId, $clientId]);
        return $stmt->fetchAll();
    }

    public static function forClaim(int $claimId, int $clientId): array
    {
        $stmt = Database::get()->prepare(
            "SELECT * FROM documents
             WHERE claim_id = ? AND client_id = ? AND scope = 'sinistre'
             ORDER BY created_at DESC"
        );
        $stmt->execute([$claimId, $clientId]);
        return $stmt->fetchAll();
    }

    public static function findForClient(int $id, int $clientId): ?array
    {
        $stmt = Database::get()->prepare(
            'SELECT * FROM documents WHERE id = ? AND client_id = ? LIMIT 1'
        );
        $stmt->execute([$id, $clientId]);
        return $stmt->fetch() ?: null;
    }

    public static function forClaimAdmin(int $claimId): array
    {
        $stmt = Database::get()->prepare(
            "SELECT * FROM documents
             WHERE claim_id = ? AND scope = 'sinistre'
             ORDER BY created_at DESC"
        );
        $stmt->execute([$claimId]);
        return $stmt->fetchAll();
    }

    public static function attestationForContract(int $contractId): ?array
    {
        $stmt = Database::get()->prepare(
            "SELECT * FROM documents
             WHERE contract_id = ? AND scope = 'contrat'
               AND doc_type = 'attestation_assurance' AND status = 'valide'
             ORDER BY created_at DESC LIMIT 1"
        );
        $stmt->execute([$contractId]);
        return $stmt->fetch() ?: null;
    }

    // ── Admin ─────────────────────────────────────────────────────────────────

    public static function find(int $id): ?array
    {
        $stmt = Database::get()->prepare('SELECT * FROM documents WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function pending(): array
    {
        return Database::get()->query(
            "SELECT d.*, c.first_name, c.last_name, c.account_number,
                    co.policy_number, cl.claim_number
             FROM documents d
             JOIN clients c ON d.client_id = c.id
             LEFT JOIN contracts co ON d.contract_id = co.id
             LEFT JOIN claims cl ON d.claim_id = cl.id
             WHERE d.status = 'en_attente'
             ORDER BY d.created_at DESC"
        )->fetchAll();
    }

    public static function validateDoc(int $id): void
    {
        Database::get()->prepare("UPDATE documents SET status = 'valide' WHERE id = ?")
            ->execute([$id]);
    }

    public static function create(array $data): int
    {
        $db = Database::get();
        $db->prepare(
            'INSERT INTO documents
             (client_id, contract_id, claim_id, scope, category, doc_type,
              original_filename, stored_path, mime_type, file_size, source, status, created_at)
             VALUES
             (:client_id, :contract_id, :claim_id, :scope, :category, :doc_type,
              :original_filename, :stored_path, :mime_type, :file_size, :source, :status, NOW())'
        )->execute($data);
        return (int)$db->lastInsertId();
    }
}
