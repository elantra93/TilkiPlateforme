#!/bin/bash
# Exécute les migrations 010 à 018 dans l'ordre.
# Usage : DB_USER=tilki_user DB_PASS=secret DB_NAME=tilki_portal bash database/run_migrations_010_018.sh
# Ou depuis le répertoire racine du projet.

set -euo pipefail

DB_USER="${DB_USER:-tilki_user}"
DB_PASS="${DB_PASS:-}"
DB_NAME="${DB_NAME:-tilki_portal}"
DB_HOST="${DB_HOST:-localhost}"

MYSQL_CMD="mysql -h${DB_HOST} -u${DB_USER} -p${DB_PASS} ${DB_NAME}"

MIGRATIONS=(
  "database/migration_010_clients_entreprise.sql"
  "database/migration_011_insurers.sql"
  "database/migration_012_vehicles.sql"
  "database/migration_013_beneficiaries.sql"
  "database/migration_014_relances.sql"
  "database/migration_015_payments_rejected.sql"
  "database/migration_016_documents_scopes.sql"
  "database/migration_017_contracts_relance.sql"
  "database/migration_018_claims_vehicle.sql"
)

for f in "${MIGRATIONS[@]}"; do
  echo "→ $f"
  $MYSQL_CMD < "$f"
  echo "  ✓ OK"
done

echo ""
echo "Migrations 010–018 appliquées avec succès."
