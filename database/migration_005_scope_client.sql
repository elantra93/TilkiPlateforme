-- Migration 005 — Ajout scope='client' et category='client' pour les documents client
-- Exécuter : mysql -u tilki_user -pTilkiDB_2024! tilki_portal < database/migration_005_scope_client.sql

ALTER TABLE documents
    MODIFY scope ENUM('contrat','sinistre','carte','paiement','client') NOT NULL;

ALTER TABLE documents
    MODIFY category ENUM(
        'cotation','souscription',
        'declaration','expertise_devis',
        'correspondances','reglements_remboursements',
        'carte',
        'paiement',
        'client'
    ) NOT NULL DEFAULT 'souscription';
