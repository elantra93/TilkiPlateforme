-- Migration 016 — Documents : scopes vehicule/beneficiaire + FK
-- Prérequis : migration_012 (vehicles) et migration_013 (beneficiaries)
-- Compatible Hostinger mutualisé et VPS : pas de CREATE DATABASE, USE, DEFINER
-- Exécuter : mysql -u tilki_user -p tilki_portal < database/migration_016_documents_scopes.sql

-- 1) Étendre le ENUM scope pour inclure 'vehicule' et 'beneficiaire'
ALTER TABLE documents
  MODIFY COLUMN scope ENUM(
    'contrat','sinistre','carte','paiement','client','vehicule','beneficiaire'
  ) NOT NULL;

-- 2) Ajouter les colonnes FK (après claim_id)
ALTER TABLE documents
  ADD COLUMN vehicle_id     INT UNSIGNED DEFAULT NULL AFTER claim_id,
  ADD COLUMN beneficiary_id INT UNSIGNED DEFAULT NULL AFTER vehicle_id;

-- 3) Contraintes FK
ALTER TABLE documents
  ADD CONSTRAINT fk_doc_vehicle
    FOREIGN KEY (vehicle_id)     REFERENCES vehicles(id)      ON DELETE SET NULL,
  ADD CONSTRAINT fk_doc_beneficiary
    FOREIGN KEY (beneficiary_id) REFERENCES beneficiaries(id) ON DELETE SET NULL;

-- 4) Index
ALTER TABLE documents
  ADD INDEX idx_doc_vehicle     (vehicle_id),
  ADD INDEX idx_doc_beneficiary (beneficiary_id);
