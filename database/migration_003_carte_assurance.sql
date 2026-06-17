-- Migration 003 — Carte d'assurance
-- Étend documents.scope et documents.category pour la valeur 'carte'
-- Compatible Hostinger mutualisé et VPS : pas de CREATE DATABASE, USE, DEFINER, PROCEDURE, TRIGGER

ALTER TABLE documents
  MODIFY COLUMN scope ENUM('contrat','sinistre','carte') NOT NULL,
  MODIFY COLUMN category ENUM(
    'cotation',
    'souscription',
    'declaration',
    'expertise_devis',
    'correspondances',
    'reglements_remboursements',
    'carte'
  ) NOT NULL DEFAULT 'souscription';
