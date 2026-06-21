-- Migration 007 — Nouveaux champs prime sur contracts (emission_date, premium_net, premium_fees)
-- Compatible Hostinger mutualisé et VPS : pas de CREATE DATABASE, USE, DEFINER, PROCEDURE, TRIGGER
-- Exécuter : mysql -u tilki_user -p tilki_portal < database/migration_007_contracts_premium.sql
-- phpMyAdmin : onglet SQL, coller le contenu ci-dessous

ALTER TABLE contracts
  ADD COLUMN emission_date DATE          NULL             AFTER expiry_date,
  ADD COLUMN premium_net   DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER premium_total,
  ADD COLUMN premium_fees  DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER premium_net;

-- Résultat colonne ordre : ..., premium_total, premium_net, premium_fees, premium_due, ...
-- premium_due reste en base mais ne sera plus saisie : valeur calculée à l'affichage (BLOC 6).
