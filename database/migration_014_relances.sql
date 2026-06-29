-- Migration 014 — Table relances (journal de relances échéances)
-- Compatible Hostinger mutualisé et VPS : pas de CREATE DATABASE, USE, DEFINER
-- Exécuter : mysql -u tilki_user -p tilki_portal < database/migration_014_relances.sql

CREATE TABLE IF NOT EXISTS relances (
    id            INT UNSIGNED   AUTO_INCREMENT PRIMARY KEY,
    contract_id   INT UNSIGNED   NOT NULL,
    client_id     INT UNSIGNED   NOT NULL,
    -- Type de palier de relance (J = jours par rapport à la date d'échéance)
    type          ENUM('j-60','j-30','j-15','j-7','echeance','j+7','j+30') NOT NULL,
    channel       ENUM('email','sms','manuel') NOT NULL DEFAULT 'email',
    status        ENUM('planifiee','envoyee','echouee') NOT NULL DEFAULT 'planifiee',
    sent_at       DATETIME       DEFAULT NULL,
    error_message VARCHAR(500)   DEFAULT NULL,
    admin_id      INT UNSIGNED   DEFAULT NULL  COMMENT 'Admin ayant déclenché la relance manuelle',
    created_at    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_rel_contract FOREIGN KEY (contract_id) REFERENCES contracts(id) ON DELETE CASCADE,
    CONSTRAINT fk_rel_client   FOREIGN KEY (client_id)   REFERENCES clients(id)   ON DELETE CASCADE,
    INDEX idx_rel_contract (contract_id),
    INDEX idx_rel_status   (status),
    INDEX idx_rel_type     (type),
    INDEX idx_rel_created  (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
