-- Migration 004 — Table payments + extension ENUM documents
-- Exécuter : mysql -u tilki_user -pTilkiDB_2024! tilki_portal < database/migration_004_payments.sql

-- 1) Ajouter la valeur 'paiement' à documents.scope
ALTER TABLE documents
    MODIFY scope ENUM('contrat','sinistre','carte','paiement') NOT NULL;

-- 2) Ajouter la valeur 'paiement' à documents.category
ALTER TABLE documents
    MODIFY category ENUM(
        'cotation','souscription',
        'declaration','expertise_devis',
        'correspondances','reglements_remboursements',
        'carte',
        'paiement'
    ) NOT NULL DEFAULT 'souscription';

-- 3) Table des paiements
CREATE TABLE IF NOT EXISTS payments (
    id                INT UNSIGNED   AUTO_INCREMENT PRIMARY KEY,
    client_id         INT UNSIGNED   NOT NULL,
    contract_id       INT UNSIGNED   NOT NULL,
    amount            DECIMAL(12,2)  NOT NULL,
    method            ENUM('cheque','virement','caisse','mobile_money') NOT NULL,
    proof_document_id INT UNSIGNED   NULL,
    reference         VARCHAR(100)   NULL,
    paid_at           DATE           NOT NULL,
    note              TEXT           NULL,
    created_by        INT UNSIGNED   NULL,
    created_at        TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pay_client    FOREIGN KEY (client_id)         REFERENCES clients(id)   ON DELETE CASCADE,
    CONSTRAINT fk_pay_contract  FOREIGN KEY (contract_id)       REFERENCES contracts(id) ON DELETE CASCADE,
    CONSTRAINT fk_pay_proof     FOREIGN KEY (proof_document_id) REFERENCES documents(id) ON DELETE SET NULL,
    INDEX idx_pay_client   (client_id),
    INDEX idx_pay_contract (contract_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
