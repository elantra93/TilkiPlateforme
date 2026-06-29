-- Migration 008 — Restructuration table payments
-- La table payments existait déjà (migration_004) avec une structure différente.
-- Ce script l'adapte à la nouvelle spec sans perte de données existantes.
-- Compatible Hostinger mutualisé et VPS : pas de CREATE DATABASE, USE, DEFINER, PROCEDURE, TRIGGER
-- Exécuter : mysql -u tilki_user -p tilki_portal < database/migration_008_payments_restructure.sql
-- phpMyAdmin : onglet SQL, coller le contenu ci-dessous

-- 1) Supprimer la contrainte FK sur proof_document_id
ALTER TABLE payments DROP FOREIGN KEY fk_pay_proof;

-- 2) Renommer proof_document_id → document_id
ALTER TABLE payments CHANGE proof_document_id document_id INT UNSIGNED NULL;

-- 3) Recréer la FK avec le nouveau nom de colonne
ALTER TABLE payments
  ADD CONSTRAINT fk_pay_document
    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE SET NULL;

-- 4) Étendre le ENUM method : ajouter 'especes' et 'carte' (garder 'caisse' pour compat données existantes)
ALTER TABLE payments
  MODIFY method ENUM('especes','virement','cheque','caisse','mobile_money','carte') NOT NULL;

-- 5) Ajouter status, validated_by, validated_at (après document_id)
ALTER TABLE payments
  ADD COLUMN status       ENUM('en_attente','valide') NOT NULL DEFAULT 'valide' AFTER document_id,
  ADD COLUMN validated_by INT NULL                                               AFTER status,
  ADD COLUMN validated_at DATETIME NULL                                          AFTER validated_by;

-- 6) Tous les paiements existants sont rétroactivement validés
UPDATE payments SET status = 'valide';

-- 7) Rendre paid_at nullable (le nouveau flux ne renseigne pas cette date)
ALTER TABLE payments MODIFY paid_at DATE NULL;

-- 8) Convertir created_by de INT UNSIGNED → ENUM('admin','client')
--    (anciens paiements tous créés par l'admin)
ALTER TABLE payments
  ADD COLUMN created_by_new ENUM('admin','client') NOT NULL DEFAULT 'admin' AFTER validated_at;
UPDATE payments SET created_by_new = 'admin';
ALTER TABLE payments DROP COLUMN created_by;
ALTER TABLE payments CHANGE created_by_new created_by ENUM('admin','client') NOT NULL DEFAULT 'admin';

-- 9) Ajouter index sur status (contract_id existe déjà via idx_pay_contract)
ALTER TABLE payments ADD INDEX idx_pay_status (status);
