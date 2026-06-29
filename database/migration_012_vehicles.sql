-- Migration 012 — Table vehicles (flotte automobile)
-- Compatible Hostinger mutualisé et VPS : pas de CREATE DATABASE, USE, DEFINER
-- Exécuter : mysql -u tilki_user -p tilki_portal < database/migration_012_vehicles.sql

CREATE TABLE IF NOT EXISTS vehicles (
    id              INT UNSIGNED   AUTO_INCREMENT PRIMARY KEY,
    contract_id     INT UNSIGNED   NOT NULL,
    client_id       INT UNSIGNED   NOT NULL,
    immatriculation VARCHAR(50)    NOT NULL          COMMENT 'N° d\'immatriculation',
    marque          VARCHAR(100)   NOT NULL,
    modele          VARCHAR(100)   DEFAULT NULL,
    annee           YEAR           DEFAULT NULL,
    energie         ENUM('essence','diesel','hybride','electrique','autre') DEFAULT NULL,
    `usage`         ENUM('personnel','commercial','mixte') NOT NULL DEFAULT 'personnel',
    valeur_venale   DECIMAL(12,2)  DEFAULT NULL      COMMENT 'Valeur vénale en XOF',
    created_at      DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_veh_contract FOREIGN KEY (contract_id) REFERENCES contracts(id) ON DELETE CASCADE,
    CONSTRAINT fk_veh_client   FOREIGN KEY (client_id)   REFERENCES clients(id)   ON DELETE CASCADE,
    INDEX idx_veh_contract (contract_id),
    INDEX idx_veh_client   (client_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
