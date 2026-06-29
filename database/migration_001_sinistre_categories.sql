-- Migration 001 : ajout des catégories propres aux sinistres
-- À exécuter sur les bases déjà créées avec l'ancien schema.
-- Usage : mysql -u root -p tilki_portal < database/migration_001_sinistre_categories.sql

USE tilki_portal;

ALTER TABLE documents
    MODIFY COLUMN category
        ENUM(
            'cotation',
            'souscription',
            'declaration',
            'expertise_devis',
            'correspondances',
            'reglements_remboursements'
        ) NOT NULL DEFAULT 'souscription';
