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

$cfg = file_exists(CONFIG_PATH) ? (require CONFIG_PATH) : [];
$isProd = ($cfg['app']['env'] ?? 'production') === 'production';
if (!$isHttps && $isProd && php_sapi_name() !== 'cli') {
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
ini_set('session.cookie_samesite', 'Lax');
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
        '/claims/declare'            => ['Claim',    'showDeclare'],
        '/claims/(\d+)'              => ['Claim',    'show'],
        '/documents/(\d+)/download'  => ['Document', 'download'],
        '/password/change'           => ['Auth',     'showChangePassword'],
        '/password/forgot'           => ['Auth',     'showForgotPassword'],
        '/password/reset'            => ['Auth',     'showResetPassword'],
        // Admin – Auth & Dashboard
        '/admin'                              => ['Admin',           'index'],
        '/admin/login'                        => ['Admin',           'showLogin'],
        '/admin/logout'                       => ['Admin',           'logout'],
        '/admin/dashboard'                    => ['Admin',           'dashboard'],
        // Admin – Clients
        '/admin/clients'                      => ['AdminClient',     'index'],
        '/admin/clients/create'               => ['AdminClient',     'showCreate'],
        // Admin – Contracts
        '/admin/contracts'                    => ['AdminContract',   'index'],
        '/admin/contracts/create'             => ['AdminContract',   'showCreate'],
        '/admin/contracts/(\d+)/edit'         => ['AdminContract',   'showEdit'],
        // Admin – Claims
        '/admin/claims'                       => ['AdminClaim',      'index'],
        '/admin/claims/create'                => ['AdminClaim',      'showCreate'],
        '/admin/claims/(\d+)/edit'            => ['AdminClaim',      'showEdit'],
        // Admin – Documents
        '/admin/documents/upload'             => ['AdminDocument',   'showUpload'],
        '/admin/documents/pending'            => ['AdminDocument',   'pending'],
        // Admin – File Tally
        '/admin/tally/queue'                  => ['AdminTally',      'queue'],
    ],
    'POST' => [
        '/login'                     => ['Auth',     'login'],
        '/logout'                    => ['Auth',     'logout'],
        '/password/change'           => ['Auth',     'changePassword'],
        '/password/forgot'           => ['Auth',     'forgotPassword'],
        '/password/reset'            => ['Auth',     'resetPassword'],
        '/claims/declare'             => ['Claim',    'declare'],
        '/claims/(\d+)/upload'        => ['Document', 'upload'],
        '/contracts/(\d+)/upload'     => ['Document', 'uploadContract'],
        // Admin – Auth
        '/admin/login'                        => ['Admin',           'login'],
        '/admin/logout'                       => ['Admin',           'logout'],
        // Admin – Clients
        '/admin/clients/create'               => ['AdminClient',     'create'],
        // Admin – Contracts
        '/admin/contracts/create'             => ['AdminContract',   'create'],
        '/admin/contracts/(\d+)/edit'         => ['AdminContract',   'edit'],
        // Admin – Claims
        '/admin/claims/create'                => ['AdminClaim',      'create'],
        '/admin/claims/(\d+)/edit'            => ['AdminClaim',      'edit'],
        // Admin – Documents
        '/admin/documents/upload'             => ['AdminDocument',   'upload'],
        '/admin/documents/(\d+)/validate'     => ['AdminDocument',   'validate'],
        // Admin – File Tally
        '/admin/tally/(\d+)/match'            => ['AdminTally',      'match'],
        '/admin/tally/(\d+)/ignore'           => ['AdminTally',      'ignore'],
        // Webhooks Tally (pas d'auth session)
        '/webhooks/tally'                     => ['TallyWebhook',      'handle'],
        '/webhooks/tally-sinistre'            => ['TallyClaimWebhook', 'handle'],
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
