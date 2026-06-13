<?php
declare(strict_types=1);
/**
 * Seed script – données de test
 * Usage : php scripts/seed.php
 */

define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config/config.php');
define('APP_PATH',    ROOT_PATH . '/app');

require_once APP_PATH . '/Services/Database.php';

use App\Services\Database;

$pdo = Database::get();
echo "=== TILKI Seed ===\n\n";

// ── Client de test ─────────────────────────────────────────────────────────────
$accountNumber  = '100001';
$clientPassword = 'Tilki2024!';

$pdo->prepare(
    "INSERT INTO clients
        (account_number, first_name, last_name, email, phone, password_hash, must_change_password, status)
     VALUES (:account_number, :first_name, :last_name, :email, :phone, :password_hash, 0, 'actif')
     ON DUPLICATE KEY UPDATE updated_at = NOW()"
)->execute([
    'account_number' => $accountNumber,
    'first_name'     => 'Amadou',
    'last_name'      => 'Diallo',
    'email'          => 'amadou.diallo@test.tilki.sn',
    'phone'          => '+221 77 000 00 01',
    'password_hash'  => password_hash($clientPassword, PASSWORD_BCRYPT),
]);

$clientId = (int)$pdo->query(
    "SELECT id FROM clients WHERE account_number = '$accountNumber' LIMIT 1"
)->fetchColumn();

echo "Client créé  : compte {$accountNumber} / mdp : {$clientPassword}\n";

// ── Contrat de test ────────────────────────────────────────────────────────────
$pdo->prepare(
    "INSERT INTO contracts
        (client_id, branche, policy_number, insurer, effective_date, expiry_date,
         premium_total, premium_due, currency, status)
     VALUES (:client_id, :branche, :policy_number, :insurer, :effective_date, :expiry_date,
             :premium_total, :premium_due, :currency, :status)"
)->execute([
    'client_id'      => $clientId,
    'branche'        => 'Automobile',
    'policy_number'  => 'AUTO-2024-001',
    'insurer'        => 'NSIA Assurances',
    'effective_date' => '2024-01-01',
    'expiry_date'    => '2024-12-31',
    'premium_total'  => 285000,
    'premium_due'    => 0,
    'currency'       => 'XOF',
    'status'         => 'actif',
]);
$contractId = (int)$pdo->lastInsertId();
echo "Contrat créé : AUTO-2024-001 (NSIA, Automobile)\n";

// ── Sinistre de test ───────────────────────────────────────────────────────────
$pdo->prepare(
    "INSERT INTO claims
        (client_id, contract_id, claim_number, insurer, branche, occurrence_date, status, description)
     VALUES (:client_id, :contract_id, :claim_number, :insurer, :branche, :occurrence_date, :status, :description)"
)->execute([
    'client_id'       => $clientId,
    'contract_id'     => $contractId,
    'claim_number'    => 'SIN-2024-001',
    'insurer'         => 'NSIA Assurances',
    'branche'         => 'Automobile',
    'occurrence_date' => '2024-06-15',
    'status'          => 'ouvert',
    'description'     => 'Accrochage en stationnement – aile avant gauche endommagée.',
]);
$claimId = (int)$pdo->lastInsertId();
echo "Sinistre créé: SIN-2024-001 (ouvert)\n";

// ── Admin de test ──────────────────────────────────────────────────────────────
$adminPassword = 'Admin@Tilki2024';

$pdo->prepare(
    "INSERT INTO admins (email, password_hash, name, role)
     VALUES (:email, :password_hash, :name, :role)
     ON DUPLICATE KEY UPDATE name = VALUES(name)"
)->execute([
    'email'         => 'admin@tilki.sn',
    'password_hash' => password_hash($adminPassword, PASSWORD_BCRYPT),
    'name'          => 'Administrateur TILKI',
    'role'          => 'superadmin',
]);
echo "Admin créé   : admin@tilki.sn / mdp : {$adminPassword}\n";

echo "\n=== Seed terminé. ===\n";
