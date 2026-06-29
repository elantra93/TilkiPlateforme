#!/usr/bin/env php
<?php
/**
 * Script cron — Relances d'échéances TILKI
 *
 * Usage : php bin/relance.php
 *
 * Cron Hostinger/VPS (exemple quotidien à 08h00) :
 *   0 8 * * * php /home/user/tilki_app/bin/relance.php >> /home/user/tilki_app/storage/logs/relance.log 2>&1
 */
declare(strict_types=1);

$_siblingApp = dirname(__DIR__) . '/tilki_app';
define('ROOT_PATH', is_dir($_siblingApp . '/app') ? $_siblingApp : dirname(__DIR__));
unset($_siblingApp);

define('CONFIG_PATH', ROOT_PATH . '/config/config.php');
define('APP_PATH',    ROOT_PATH . '/app');
define('STORAGE_PATH',ROOT_PATH . '/storage');

// Autoloader PSR-4
spl_autoload_register(function (string $class): void {
    if (strncmp('App\\', $class, 4) !== 0) return;
    $file = APP_PATH . '/' . str_replace('\\', '/', substr($class, 4)) . '.php';
    if (file_exists($file)) require $file;
});

use App\Services\RelanceEngine;

$start = microtime(true);
echo '[' . date('Y-m-d H:i:s') . '] Démarrage des relances...' . PHP_EOL;

try {
    $summary = RelanceEngine::processAll();
    printf(
        '[%s] Terminé en %.2fs — %d envoyée(s), %d déjà traitée(s), %d échouée(s)%s',
        date('Y-m-d H:i:s'),
        microtime(true) - $start,
        $summary['sent'],
        $summary['skipped'],
        $summary['failed'],
        PHP_EOL
    );
    exit(0);
} catch (\Throwable $e) {
    echo '[' . date('Y-m-d H:i:s') . '] ERREUR : ' . $e->getMessage() . PHP_EOL;
    exit(1);
}
