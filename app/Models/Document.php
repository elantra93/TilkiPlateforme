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
