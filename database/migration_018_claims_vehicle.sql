-- Migration 018 — Claims : vehicle_id FK (sinistre auto → véhicule)
-- Prérequis : migration_012 (vehicles)
-- Compatible Hostinger mutualisé et VPS : pas de CREATE DATABASE, USE, DEFINER
-- Exécuter : mysql -u tilki_user -p tilki_portal < database/migration_018_claims_vehicle.sql

-- 1) Ajouter vehicle_id (après is_auto_rc)
ALTER TABLE claims
  ADD COLUMN vehicle_id INT UNSIGNED DEFAULT NULL
                        COMMENT 'Véhicule impliqué (si branche Auto RC / Auto)' AFTER is_auto_rc;

-- 2) Contrainte FK
ALTER TABLE claims
  ADD CONSTRAINT fk_claim_vehicle
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE SET NULL;

-- 3) Index
ALTER TABLE claims
  ADD INDEX idx_claim_vehicle (vehicle_id);
