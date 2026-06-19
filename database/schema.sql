-- TILKI Portail Client – Schema
-- MySQL 8+ / utf8mb4 / InnoDB
-- Usage : mysql -u root -p < database/schema.sql

CREATE DATABASE IF NOT EXISTS tilki_portal
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE tilki_portal;

-- --------------------------------------------------------
-- Clients
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS clients (
    id                   INT UNSIGNED     AUTO_INCREMENT PRIMARY KEY,
    account_number       CHAR(6)          NOT NULL,
    first_name           VARCHAR(100)     NOT NULL,
    last_name            VARCHAR(100)     NOT NULL,
    email                VARCHAR(191)     NOT NULL,
    phone                VARCHAR(30)      DEFAULT NULL,
    password_hash        VARCHAR(255)     NOT NULL,
    must_change_password TINYINT(1)       NOT NULL DEFAULT 1,
    reset_token          VARCHAR(100)     DEFAULT NULL,
    reset_token_expires  DATETIME         DEFAULT NULL,
    status               ENUM('actif','inactif','suspendu') NOT NULL DEFAULT 'actif',
    created_at           DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at           DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_account_number (account_number),
    UNIQUE KEY uq_email          (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Contracts
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS contracts (
    id             INT UNSIGNED   AUTO_INCREMENT PRIMARY KEY,
    client_id      INT UNSIGNED   NOT NULL,
    branche        VARCHAR(100)   NOT NULL,
    policy_number  VARCHAR(100)   NOT NULL,
    insurer        VARCHAR(150)   NOT NULL,
    effective_date DATE           NOT NULL,
    expiry_date    DATE           NOT NULL,
    premium_total  DECIMAL(12,2)  NOT NULL DEFAULT 0,
    premium_due    DECIMAL(12,2)  NOT NULL DEFAULT 0,
    currency       CHAR(3)        NOT NULL DEFAULT 'XOF',
    status         ENUM('actif','expiré','résilié','suspendu') NOT NULL DEFAULT 'actif',
    created_at     DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    INDEX idx_client (client_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Claims
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS claims (
    id              INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    client_id       INT UNSIGNED  NOT NULL,
    contract_id     INT UNSIGNED  DEFAULT NULL,
    claim_number    VARCHAR(100)  NOT NULL,
    insurer         VARCHAR(150)  NOT NULL,
    branche         VARCHAR(100)  NOT NULL,
    occurrence_date DATE          NOT NULL,
    status          ENUM('ouvert','clos') NOT NULL DEFAULT 'ouvert',
    description     TEXT          DEFAULT NULL,
    is_auto_rc      TINYINT(1)    NOT NULL DEFAULT 0,
    created_at      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id)   REFERENCES clients(id)   ON DELETE CASCADE,
    FOREIGN KEY (contract_id) REFERENCES contracts(id) ON DELETE SET NULL,
    INDEX idx_client   (client_id),
    INDEX idx_contract (contract_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Claim steps (suivi d'avancement)
-- --------------------------------------------------------
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
    UNIQUE KEY uq_claim_step     (claim_id, step_key),
    INDEX idx_claim_position     (claim_id, position)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Documents
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS documents (
    id                INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    client_id         INT UNSIGNED  NOT NULL,
    contract_id       INT UNSIGNED  DEFAULT NULL,
    claim_id          INT UNSIGNED  DEFAULT NULL,
    scope             ENUM('contrat','sinistre','carte') NOT NULL,
    category          ENUM(
                          'cotation','souscription',
                          'declaration','expertise_devis',
                          'correspondances','reglements_remboursements',
                          'carte'
                      ) NOT NULL DEFAULT 'souscription',
    doc_type          VARCHAR(100)  NOT NULL,
    original_filename VARCHAR(255)  NOT NULL,
    stored_path       VARCHAR(500)  NOT NULL,
    mime_type         VARCHAR(100)  NOT NULL,
    file_size         INT UNSIGNED  NOT NULL,
    source            ENUM('admin','tally','client') NOT NULL DEFAULT 'admin',
    status            ENUM('valide','en_attente')    NOT NULL DEFAULT 'en_attente',
    created_at        DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id)   REFERENCES clients(id)   ON DELETE CASCADE,
    FOREIGN KEY (contract_id) REFERENCES contracts(id) ON DELETE SET NULL,
    FOREIGN KEY (claim_id)    REFERENCES claims(id)    ON DELETE SET NULL,
    INDEX idx_client   (client_id),
    INDEX idx_contract (contract_id),
    INDEX idx_claim    (claim_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tally webhook queue (soumissions non rattachées)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS tally_queue (
    id           INT UNSIGNED   AUTO_INCREMENT PRIMARY KEY,
    event_id     VARCHAR(100)   NOT NULL DEFAULT '',
    response_id  VARCHAR(100)   NOT NULL,
    form_id      VARCHAR(100)   DEFAULT NULL,
    form_name    VARCHAR(255)   DEFAULT NULL,
    payload      JSON           NOT NULL,
    status       ENUM('pending','matched','ignored') NOT NULL DEFAULT 'pending',
    client_id    INT UNSIGNED   DEFAULT NULL,
    created_at   DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_response (response_id),
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL,
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Admins
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS admins (
    id            INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    email         VARCHAR(191)  NOT NULL,
    password_hash VARCHAR(255)  NOT NULL,
    name          VARCHAR(150)  NOT NULL,
    role          ENUM('superadmin','admin','support') NOT NULL DEFAULT 'admin',
    created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_admin_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Login attempts
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS login_attempts (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    identifier VARCHAR(20)  NOT NULL,
    ip         VARCHAR(45)  NOT NULL,
    success    TINYINT(1)   NOT NULL DEFAULT 0,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_identifier_created (identifier, created_at),
    INDEX idx_ip_created         (ip, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Audit log
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS audit_log (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    actor_type ENUM('client','admin','system') NOT NULL,
    actor_id   INT UNSIGNED DEFAULT NULL,
    action     VARCHAR(100) NOT NULL,
    target     VARCHAR(255) NOT NULL,
    ip         VARCHAR(45)  NOT NULL,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_actor   (actor_type, actor_id),
    INDEX idx_action  (action),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
