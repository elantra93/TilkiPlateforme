-- Migration 013 — Table beneficiaries (bénéficiaires santé)
-- Compatible Hostinger mutualisé et VPS : pas de CREATE DATABASE, USE, DEFINER
-- Exécuter : mysql -u tilki_user -p tilki_portal < database/migration_013_beneficiaries.sql

CREATE TABLE IF NOT EXISTS beneficiaries (
    id              INT UNSIGNED   AUTO_INCREMENT PRIMARY KEY,
    contract_id     INT UNSIGNED   NOT NULL,
    client_id       INT UNSIGNED   NOT NULL,
    first_name      VARCHAR(100)   NOT NULL,
    last_name       VARCHAR(100)   NOT NULL,
    birth_date      DATE           DEFAULT NULL,
    gender          ENUM('M','F')  DEFAULT NULL,
    relation        ENUM('souscripteur','conjoint','enfant','parent','autre') NOT NULL DEFAULT 'autre',
    is_principal    TINYINT(1)     NOT NULL DEFAULT 0  COMMENT '1 = souscripteur principal',
    matricule       VARCHAR(50)    DEFAULT NULL        COMMENT 'Matricule ou N° d\'adhérent',
    created_at      DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_ben_contract FOREIGN KEY (contract_id) REFERENCES contracts(id) ON DELETE CASCADE,
    CONSTRAINT fk_ben_client   FOREIGN KEY (client_id)   REFERENCES clients(id)   ON DELETE CASCADE,
    INDEX idx_ben_contract (contract_id),
    INDEX idx_ben_client   (client_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
