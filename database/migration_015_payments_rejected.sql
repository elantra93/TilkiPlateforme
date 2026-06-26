-- Migration 015 — Paiements : statut rejeté + colonnes de rejet
-- Prérequis : migration_008 (status ENUM en_attente/valide déjà présent)
-- Compatible Hostinger mutualisé et VPS : pas de CREATE DATABASE, USE, DEFINER
-- Exécuter : mysql -u tilki_user -p tilki_portal < database/migration_015_payments_rejected.sql

-- 1) Étendre le ENUM status pour inclure 'rejeté'
ALTER TABLE payments
  MODIFY COLUMN status ENUM('en_attente','valide','rejeté') NOT NULL DEFAULT 'valide';

-- 2) Ajouter les colonnes de rejet (après validated_at)
ALTER TABLE payments
  ADD COLUMN rejected_reason VARCHAR(500) DEFAULT NULL AFTER validated_at,
  ADD COLUMN rejected_by     INT UNSIGNED DEFAULT NULL AFTER rejected_reason,
  ADD COLUMN rejected_at     DATETIME     DEFAULT NULL AFTER rejected_by;

-- 3) Index sur les paiements rejetés (requête fréquente pour la page "en attente")
ALTER TABLE payments
  ADD INDEX idx_pay_rejected_at (rejected_at);
