-- Migration 010 — Identité entreprise sur les clients
-- Prérequis : migration_006 (account_type déjà présent)
-- Compatible Hostinger mutualisé et VPS : pas de CREATE DATABASE, USE, DEFINER
-- Exécuter : mysql -u tilki_user -p tilki_portal < database/migration_010_clients_entreprise.sql

ALTER TABLE clients
  ADD COLUMN company_name          VARCHAR(150) DEFAULT NULL  COMMENT 'Raison sociale'           AFTER account_type,
  ADD COLUMN company_rccm          VARCHAR(100) DEFAULT NULL  COMMENT 'N° RCCM'                  AFTER company_name,
  ADD COLUMN company_dfe           VARCHAR(100) DEFAULT NULL  COMMENT 'N° DFE / identifiant fiscal' AFTER company_rccm,
  ADD COLUMN company_address       VARCHAR(255) DEFAULT NULL  COMMENT 'Adresse du siège'          AFTER company_dfe,
  ADD COLUMN company_city          VARCHAR(100) DEFAULT NULL  COMMENT 'Ville du siège'            AFTER company_address,
  ADD COLUMN company_country       VARCHAR(100) DEFAULT 'Côte d\'Ivoire' COMMENT 'Pays'           AFTER company_city,
  ADD COLUMN company_contact_name  VARCHAR(150) DEFAULT NULL  COMMENT 'Interlocuteur principal'   AFTER company_country,
  ADD COLUMN company_contact_phone VARCHAR(30)  DEFAULT NULL  COMMENT 'Téléphone de l\'interlocuteur' AFTER company_contact_name;
