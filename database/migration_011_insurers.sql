-- Migration 011 — Table insurers + seed 24 assureurs CI
-- Compatible Hostinger mutualisé et VPS : pas de CREATE DATABASE, USE, DEFINER
-- Exécuter : mysql -u tilki_user -p tilki_portal < database/migration_011_insurers.sql

CREATE TABLE IF NOT EXISTS insurers (
    id         INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(150)  NOT NULL             COMMENT 'Dénomination complète',
    short_name VARCHAR(60)   DEFAULT NULL         COMMENT 'Sigle ou nom court',
    country    VARCHAR(100)  NOT NULL DEFAULT 'Côte d\'Ivoire',
    is_active  TINYINT(1)    NOT NULL DEFAULT 1,
    created_at DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_insurer_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed — 24 assureurs opérant en Côte d'Ivoire (zone CIMA)
-- INSERT IGNORE évite les doublons en cas de ré-exécution
INSERT IGNORE INTO insurers (name, short_name) VALUES
  ('SUNU Assurances IARD Côte d\'Ivoire',     'SUNU IARD CI'),
  ('SUNU Vie Côte d\'Ivoire',                 'SUNU Vie CI'),
  ('NSIA Assurances Côte d\'Ivoire',          'NSIA CI'),
  ('NSIA Vie Côte d\'Ivoire',                 'NSIA Vie CI'),
  ('AXA Assurances Côte d\'Ivoire',           'AXA CI'),
  ('Allianz Assurances Côte d\'Ivoire',       'Allianz CI'),
  ('Activa Assurances Côte d\'Ivoire',        'Activa CI'),
  ('Atlantique Assurances Côte d\'Ivoire',    'Atlantique CI'),
  ('Saham Assurances Côte d\'Ivoire',         'Saham CI'),
  ('Prudential Assurance Côte d\'Ivoire',     'Prudential CI'),
  ('Fidelia Assurances',                      'Fidelia'),
  ('SIC Assurances',                          'SIC'),
  ('COLINA SA',                               'COLINA'),
  ('GA Assurances Côte d\'Ivoire',            'GA CI'),
  ('MGIC – Mutuelle Générale des Ivoiriens',  'MGIC'),
  ('La Loyale Assurances',                    'La Loyale'),
  ('Vie Plus Côte d\'Ivoire',                 'Vie Plus CI'),
  ('Trans Assurances CI',                     'Trans CI'),
  ('Amissa Assurances',                       'Amissa'),
  ('Union des Assurances de Côte d\'Ivoire',  'UACI'),
  ('Pan African Life Assurances',             'PALA'),
  ('SONAS Côte d\'Ivoire',                    'SONAS CI'),
  ('Cosmos Assurances',                       'Cosmos'),
  ('Lion Assurances Côte d\'Ivoire',          'Lion CI');
