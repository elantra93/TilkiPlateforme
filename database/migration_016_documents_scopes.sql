-- Migration 016 — Documents : scopes vehicule/beneficiaire + FK (idempotent MySQL 8)
-- Prérequis : migration_012 (vehicles) et migration_013 (beneficiaries)
-- Compatible Hostinger mutualisé et VPS : pas de CREATE DATABASE, USE, DEFINER
-- Exécuter : mysql -u tilki_user -p tilki_portal < database/migration_016_documents_scopes.sql

-- 1) Étendre le ENUM scope pour inclure 'vehicule' et 'beneficiaire'
--    MODIFY est idempotent : rejouer ne fait rien si les valeurs existent déjà
ALTER TABLE documents
  MODIFY COLUMN scope ENUM(
    'contrat','sinistre','carte','paiement','client','vehicule','beneficiaire'
  ) NOT NULL;

-- 2) Ajouter vehicle_id si absent
SET @sql = (SELECT IF(
  NOT EXISTS(
    SELECT 1 FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'documents' AND COLUMN_NAME = 'vehicle_id'
  ),
  'ALTER TABLE documents ADD COLUMN vehicle_id INT UNSIGNED DEFAULT NULL AFTER claim_id',
  'SELECT 1'
));
PREPARE _s FROM @sql; EXECUTE _s; DEALLOCATE PREPARE _s;

-- 3) Ajouter beneficiary_id si absent
SET @sql = (SELECT IF(
  NOT EXISTS(
    SELECT 1 FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'documents' AND COLUMN_NAME = 'beneficiary_id'
  ),
  'ALTER TABLE documents ADD COLUMN beneficiary_id INT UNSIGNED DEFAULT NULL AFTER vehicle_id',
  'SELECT 1'
));
PREPARE _s FROM @sql; EXECUTE _s; DEALLOCATE PREPARE _s;

-- 4) FK fk_doc_vehicle si absente
SET @sql = (SELECT IF(
  NOT EXISTS(
    SELECT 1 FROM information_schema.TABLE_CONSTRAINTS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'documents' AND CONSTRAINT_NAME = 'fk_doc_vehicle'
  ),
  'ALTER TABLE documents ADD CONSTRAINT fk_doc_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE SET NULL',
  'SELECT 1'
));
PREPARE _s FROM @sql; EXECUTE _s; DEALLOCATE PREPARE _s;

-- 5) FK fk_doc_beneficiary si absente
SET @sql = (SELECT IF(
  NOT EXISTS(
    SELECT 1 FROM information_schema.TABLE_CONSTRAINTS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'documents' AND CONSTRAINT_NAME = 'fk_doc_beneficiary'
  ),
  'ALTER TABLE documents ADD CONSTRAINT fk_doc_beneficiary FOREIGN KEY (beneficiary_id) REFERENCES beneficiaries(id) ON DELETE SET NULL',
  'SELECT 1'
));
PREPARE _s FROM @sql; EXECUTE _s; DEALLOCATE PREPARE _s;

-- 6) Index idx_doc_vehicle si absent
SET @sql = (SELECT IF(
  NOT EXISTS(
    SELECT 1 FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'documents' AND INDEX_NAME = 'idx_doc_vehicle'
  ),
  'ALTER TABLE documents ADD INDEX idx_doc_vehicle (vehicle_id)',
  'SELECT 1'
));
PREPARE _s FROM @sql; EXECUTE _s; DEALLOCATE PREPARE _s;

-- 7) Index idx_doc_beneficiary si absent
SET @sql = (SELECT IF(
  NOT EXISTS(
    SELECT 1 FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'documents' AND INDEX_NAME = 'idx_doc_beneficiary'
  ),
  'ALTER TABLE documents ADD INDEX idx_doc_beneficiary (beneficiary_id)',
  'SELECT 1'
));
PREPARE _s FROM @sql; EXECUTE _s; DEALLOCATE PREPARE _s;
