-- Migration 017 — Contracts : colonnes relance dénormalisées + insurer_id FK
-- Prérequis : migration_011 (insurers), migration_014 (relances)
-- Compatible Hostinger mutualisé et VPS : pas de CREATE DATABASE, USE, DEFINER
-- Exécuter : mysql -u tilki_user -p tilki_portal < database/migration_017_contracts_relance.sql

-- 1) Statut de relance dénormalisé (mis à jour par le moteur de relances B6)
--    Évite un sous-SELECT sur la table relances à chaque affichage de liste
ALTER TABLE contracts
  ADD COLUMN relance_statut      ENUM('aucune','planifiee','envoyee','echouee') NOT NULL DEFAULT 'aucune'
                                 COMMENT 'Cache du dernier statut de relance'           AFTER status,
  ADD COLUMN relance_derniere_at DATETIME DEFAULT NULL
                                 COMMENT 'Date d\'envoi de la dernière relance'          AFTER relance_statut;

-- 2) Référence optionnelle vers la table insurers (migration_011)
--    Valeur NULL = assureur saisi en texte libre (colonne insurer VARCHAR conservée)
ALTER TABLE contracts
  ADD COLUMN insurer_id INT UNSIGNED DEFAULT NULL
                        COMMENT 'FK vers insurers.id (optionnel, null = texte libre)' AFTER insurer;

ALTER TABLE contracts
  ADD CONSTRAINT fk_contract_insurer
    FOREIGN KEY (insurer_id) REFERENCES insurers(id) ON DELETE SET NULL;

ALTER TABLE contracts
  ADD INDEX idx_contract_insurer (insurer_id);
