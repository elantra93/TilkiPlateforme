-- Migration 002 : suivi d'avancement des sinistres
-- Usage : mysql -u root -p tilki_portal < database/migration_002_claim_steps.sql

USE tilki_portal;

-- Champ is_auto_rc sur les sinistres
ALTER TABLE claims
    ADD COLUMN is_auto_rc TINYINT(1) NOT NULL DEFAULT 0
    AFTER description;

-- Table des étapes de traitement
CREATE TABLE IF NOT EXISTS claim_steps (
    id              INT UNSIGNED     AUTO_INCREMENT PRIMARY KEY,
    claim_id        INT UNSIGNED     NOT NULL,
    step_key        VARCHAR(50)      NOT NULL,
    label           VARCHAR(255)     NOT NULL,
    position        TINYINT UNSIGNED NOT NULL,
    completed       TINYINT(1)       NOT NULL DEFAULT 0,
    completed_date  DATE             DEFAULT NULL,
    created_at      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (claim_id) REFERENCES claims(id) ON DELETE CASCADE,
    UNIQUE KEY uq_claim_step (claim_id, step_key),
    INDEX idx_claim_position (claim_id, position)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
