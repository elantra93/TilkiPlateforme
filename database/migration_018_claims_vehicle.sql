-- Migration 018 — Claims : vehicle_id FK (idempotent MySQL 8)
-- Prérequis : migration_012 (vehicles)
-- Compatible Hostinger mutualisé et VPS : pas de CREATE DATABASE, USE, DEFINER
-- Exécuter : mysql -u tilki_user -p tilki_portal < database/migration_018_claims_vehicle.sql

-- 1) Ajouter vehicle_id si absent
SET @sql = (SELECT IF(
  NOT EXISTS(
    SELECT 1 FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'claims' AND COLUMN_NAME = 'vehicle_id'
  ),
  'ALTER TABLE claims ADD COLUMN vehicle_id INT UNSIGNED DEFAULT NULL COMMENT ''Véhicule impliqué (si branche Auto RC / Auto)'' AFTER is_auto_rc',
  'SELECT 1'
));
PREPARE _s FROM @sql; EXECUTE _s; DEALLOCATE PREPARE _s;

-- 2) FK fk_claim_vehicle si absente
SET @sql = (SELECT IF(
  NOT EXISTS(
    SELECT 1 FROM information_schema.TABLE_CONSTRAINTS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'claims' AND CONSTRAINT_NAME = 'fk_claim_vehicle'
  ),
  'ALTER TABLE claims ADD CONSTRAINT fk_claim_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE SET NULL',
  'SELECT 1'
));
PREPARE _s FROM @sql; EXECUTE _s; DEALLOCATE PREPARE _s;

-- 3) Index idx_claim_vehicle si absent
SET @sql = (SELECT IF(
  NOT EXISTS(
    SELECT 1 FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'claims' AND INDEX_NAME = 'idx_claim_vehicle'
  ),
  'ALTER TABLE claims ADD INDEX idx_claim_vehicle (vehicle_id)',
  'SELECT 1'
));
PREPARE _s FROM @sql; EXECUTE _s; DEALLOCATE PREPARE _s;
