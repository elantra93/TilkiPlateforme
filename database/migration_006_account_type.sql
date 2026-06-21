-- Migration 006 — account_type sur les clients
-- Compatible Hostinger mutualisé et VPS : pas de CREATE DATABASE, USE, DEFINER, PROCEDURE, TRIGGER
-- Exécuter : mysql -u tilki_user -p tilki_portal < database/migration_006_account_type.sql
-- phpMyAdmin : onglet SQL, coller le contenu ci-dessous (sans les deux lignes de commentaire ci-dessus)

ALTER TABLE clients
  ADD COLUMN account_type ENUM('individuel','entreprise') NOT NULL DEFAULT 'individuel'
  AFTER status;
