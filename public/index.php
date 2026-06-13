<?php
declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config/config.php');
define('APP_PATH',    ROOT_PATH . '/app');
define('STORAGE_PATH',ROOT_PATH . '/storage');

// ── HTTPS redirect ────────────────────────────────────────────────────────────
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
    || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);

if (!$isHttps && php_sapi_name() !== 'cli') {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], true, 301);
    exit;
}

// ── Autoloader (PSR-4 App\) ───────────────────────────────────────────────────
spl_autoload_register(function (string $class): void {
    if (strncmp('App\\', $class, 4) !== 0) return;
    $file = APP_PATH . '/' . str_replace('\\', '/', substr($class, 4)) . '.php';
    if (file_exists($file)) require $file;
});

// ── Session ───────────────────────────────────────────────────────────────────
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure',   '1');
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', '1');
ini_set('session.gc_maxlifetime',  '3600');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['_init'])) {
    $_SESSION['_init'] = time();
} elseif (time() - $_SESSION['_init'] > 1800) {
    session_regenerate_id(true);
    $_SESSION['_init'] = time();
}

// ── Router ────────────────────────────────────────────────────────────────────
$path   = '/' . trim((string)parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$routes = [
    'GET' => [
        '/'                          => ['Auth',     'showLogin'],
        '/login'                     => ['Auth',     'showLogin'],
        '/logout'                    => ['Auth',     'logout'],
        '/dashboard'                 => ['Dashboard','index'],
        '/contracts'                 => ['Contract', 'index'],
        '/contracts/(\d+)'           => ['Contract', 'show'],
        '/claims'                    => ['Claim',    'index'],
        '/claims/(\d+)'              => ['Claim',    'show'],
        '/documents/(\d+)/download'  => ['Document', 'download'],
        '/password/change'           => ['Auth',     'showChangePassword'],
    ],
    'POST' => [
        '/login'                     => ['Auth',     'login'],
        '/logout'                    => ['Auth',     'logout'],
        '/password/change'           => ['Auth',     'changePassword'],
        '/claims/(\d+)/upload'       => ['Document', 'upload'],
    ],
];

$matched = false;
foreach ($routes[$method] ?? [] as $pattern => [$ctrl, $action]) {
    if (preg_match('#^' . $pattern . '$#', $path, $m)) {
        array_shift($m);
        $class = 'App\\Controllers\\' . $ctrl . 'Controller';
        (new $class())->$action(...$m);
        $matched = true;
        break;
    }
}

if (!$matched) {
    http_response_code(404);
    require APP_PATH . '/Views/errors/404.php';
}
