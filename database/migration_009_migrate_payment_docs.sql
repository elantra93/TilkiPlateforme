-- Migration 009 — Migration des preuves de règlement (documents.scope='paiement') vers payments
-- Compatible Hostinger mutualisé et VPS : pas de CREATE DATABASE, USE, DEFINER, PROCEDURE, TRIGGER
-- Exécuter APRÈS migration_008 : mysql -u tilki_user -p tilki_portal < database/migration_009_migrate_payment_docs.sql
-- phpMyAdmin : onglet SQL, coller le contenu ci-dessous

-- Ce script insère dans payments une ligne par document de scope='paiement' non encore rattaché.
-- amount = 0 (à corriger par l'admin lors de la validation).
-- method = 'virement' par défaut (valeur obligatoire) — l'admin corrige au moment de valider.
-- status = 'en_attente', created_by = 'client'.

INSERT INTO payments (contract_id, client_id, amount, method, document_id, status, created_by, created_at)
SELECT
  d.contract_id,
  d.client_id,
  0.00         AS amount,
  'virement'   AS method,
  d.id         AS document_id,
  'en_attente' AS status,
  'client'     AS created_by,
  d.created_at
FROM documents d
WHERE d.scope = 'paiement'
  AND d.contract_id IS NOT NULL
  AND d.id NOT IN (
    SELECT document_id FROM payments WHERE document_id IS NOT NULL
  );

-- Vérification : consulter les paiements migrés
-- SELECT p.id, p.contract_id, p.client_id, p.amount, p.status, d.original_filename
-- FROM payments p JOIN documents d ON p.document_id = d.id
-- WHERE p.status = 'en_attente' AND p.created_by = 'client';
