<?php
declare(strict_types=1);
namespace App\Services;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone()    {}

    public static function get(): PDO
    {
        if (self::$instance === null) {
            $c  = require CONFIG_PATH;
            $db = $c['db'];
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $db['host'], $db['port'], $db['name'], $db['charset']
            );
            try {
                self::$instance = new PDO($dsn, $db['user'], $db['pass'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                error_log('[DB] ' . $e->getMessage());
                http_response_code(503);
                exit('Service momentanément indisponible.');
            }
        }
        return self::$instance;
    }
}
