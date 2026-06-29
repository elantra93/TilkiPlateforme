-- Migration 019 — Colonne branches sur insurers
-- Ajoute un champ JSON listant les branches couvertes par chaque assureur.
-- Idempotente : ADD COLUMN IF NOT EXISTS + UPDATE en masse pour le seed CI.

ALTER TABLE insurers
    ADD COLUMN IF NOT EXISTS branches TEXT DEFAULT NULL
        COMMENT 'JSON : liste des branches couvertes par cet assureur';

-- Branches NSIA (IARD + Vie)
UPDATE insurers SET branches = '["Auto","Moto","Voyage","Multirisques habitation","Santé individuelle"]'
    WHERE name IN ('NSIA Assurances Côte d\'Ivoire','NSIA Vie Côte d\'Ivoire') AND (branches IS NULL OR branches = '');

-- Branches SUNU (IARD + Vie)
UPDATE insurers SET branches = '["Santé individuelle","Santé groupe","Vie","RC pro"]'
    WHERE name IN ('SUNU Assurances IARD Côte d\'Ivoire','SUNU Vie Côte d\'Ivoire') AND (branches IS NULL OR branches = '');

-- Branches AXA
UPDATE insurers SET branches = '["Auto","Moto","Voyage","Multirisques habitation","Santé individuelle","RC pro"]'
    WHERE name = 'AXA Assurances Côte d\'Ivoire' AND (branches IS NULL OR branches = '');

-- Branches Allianz
UPDATE insurers SET branches = '["Auto","Moto","Voyage","Flotte automobile","Multirisques habitation"]'
    WHERE name = 'Allianz Assurances Côte d\'Ivoire' AND (branches IS NULL OR branches = '');

-- Branches Saham
UPDATE insurers SET branches = '["RC pro","Multirisques professionnelle","Flotte automobile"]'
    WHERE name = 'Saham Assurances Côte d\'Ivoire' AND (branches IS NULL OR branches = '');

-- Branches Activa
UPDATE insurers SET branches = '["Auto","Moto","Voyage","RC pro","Multirisques habitation"]'
    WHERE name = 'Activa Assurances Côte d\'Ivoire' AND (branches IS NULL OR branches = '');

-- Branches Atlantique
UPDATE insurers SET branches = '["Auto","Moto","Santé individuelle","Santé groupe","RC pro"]'
    WHERE name = 'Atlantique Assurances Côte d\'Ivoire' AND (branches IS NULL OR branches = '');

-- Branches Prudential (vie)
UPDATE insurers SET branches = '["Santé individuelle","Santé groupe","Vie"]'
    WHERE name = 'Prudential Assurance Côte d\'Ivoire' AND (branches IS NULL OR branches = '');

-- Branches SIC
UPDATE insurers SET branches = '["Auto","Moto","Voyage","Multirisques habitation","RC pro"]'
    WHERE name = 'SIC Assurances' AND (branches IS NULL OR branches = '');

-- Branches COLINA
UPDATE insurers SET branches = '["Santé groupe","Vie","RC pro"]'
    WHERE name = 'COLINA SA' AND (branches IS NULL OR branches = '');
