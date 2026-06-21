<?php
declare(strict_types=1);

// Hostinger: app files live in ~/tilki_app/ (sibling of public_html)
// VPS/Dev:   app files live in parent of public/ (original repo layout)
$_siblingApp = dirname(__DIR__) . '/tilki_app';
define('ROOT_PATH', is_dir($_siblingApp . '/app') ? $_siblingApp : dirname(__DIR__));
unset($_siblingApp);

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
        '/claims/declare'            => ['Claim',    'declare'],
        '/claims/(\d+)'              => ['Claim',    'show'],
        '/payments'                  => ['Payment',  'index'],
        '/devis'                     => ['Devis',    'index'],
        '/documents/(\d+)/download'  => ['Document', 'download'],
        '/documents/(\d+)/view'      => ['Document', 'view'],
        '/account'                   => ['Account',  'show'],
        '/password/change'           => ['Auth',     'showChangePassword'],
        '/password/forgot'           => ['Auth',     'showForgotPassword'],
        '/password/reset'            => ['Auth',     'showResetPassword'],
        // Admin – Auth & Dashboard
        '/admin'                              => ['Admin',           'index'],
        '/admin/login'                        => ['Admin',           'showLogin'],
        '/admin/logout'                       => ['Admin',           'logout'],
        '/admin/dashboard'                    => ['Admin',           'dashboard'],
        '/admin/password/change'              => ['Admin',           'showChangePassword'],
        // Admin – Clients
        '/admin/clients'                      => ['AdminClient',     'index'],
        '/admin/clients/create'               => ['AdminClient',     'showCreate'],
        '/admin/clients/(\d+)/edit'           => ['AdminClient',     'showEdit'],
        // Admin – Contracts
        '/admin/contracts'                    => ['AdminContract',   'index'],
        '/admin/contracts/create'             => ['AdminContract',   'showCreate'],
        '/admin/contracts/(\d+)/edit'         => ['AdminContract',   'showEdit'],
        // Admin – Claims
        '/admin/claims'                       => ['AdminClaim',      'index'],
        '/admin/claims/create'                => ['AdminClaim',      'showCreate'],
        '/admin/claims/tally-redirect'        => ['AdminClaim',      'tallyRedirect'],
        '/admin/claims/(\d+)/edit'            => ['AdminClaim',      'showEdit'],
        // Admin – Paiements
        '/admin/payments'                     => ['AdminPayment',    'index'],
        '/admin/payments/create'              => ['AdminPayment',    'showCreate'],
        // Admin – Documents
        '/admin/documents/upload'             => ['AdminDocument',   'redirectUpload'],
        '/admin/documents/pending'            => ['AdminDocument',   'pending'],
        // Admin – File Tally
        '/admin/tally/queue'                  => ['AdminTally',      'queue'],
        // Admin – Devis
        '/admin/devis'                        => ['AdminDevis',      'index'],
        // Admin – Gestion des admins (superadmin)
        '/admin/admins'                       => ['AdminUser',       'index'],
        '/admin/admins/create'                => ['AdminUser',       'showCreate'],
        '/admin/admins/(\d+)/edit'            => ['AdminUser',       'showEdit'],
    ],
    'POST' => [
        '/login'                     => ['Auth',     'login'],
        '/logout'                    => ['Auth',     'logout'],
        '/password/change'           => ['Auth',     'changePassword'],
        '/account/pin'               => ['Account',  'changePin'],
        '/password/forgot'           => ['Auth',     'forgotPassword'],
        '/password/reset'            => ['Auth',     'resetPassword'],
        '/claims/(\d+)/upload'        => ['Document', 'upload'],
        '/contracts/(\d+)/upload'     => ['Document', 'uploadContract'],
        '/contracts/(\d+)/payment'    => ['Contract', 'submitPayment'],
        // Admin – Auth
        '/admin/login'                        => ['Admin',           'login'],
        '/admin/logout'                       => ['Admin',           'logout'],
        '/admin/password/change'              => ['Admin',           'changePassword'],
        // Admin – Clients
        '/admin/clients/create'               => ['AdminClient',     'create'],
        '/admin/clients/(\d+)/carte'          => ['AdminClient',     'uploadCarte'],
        '/admin/clients/(\d+)/upload-doc'     => ['AdminClient',     'uploadDoc'],
        // Admin – Contracts
        '/admin/contracts/create'             => ['AdminContract',   'create'],
        '/admin/contracts/(\d+)/edit'         => ['AdminContract',   'edit'],
        '/admin/contracts/(\d+)/upload'       => ['AdminContract',   'uploadDoc'],
        // Admin – Claims
        '/admin/claims/(\d+)/edit'            => ['AdminClaim',      'edit'],
        '/admin/claims/(\d+)/steps/(\d+)'     => ['AdminClaim',      'updateStep'],
        '/admin/claims/(\d+)/upload'          => ['AdminClaim',      'uploadDoc'],
        // Admin – Paiements
        '/admin/payments/create'              => ['AdminPayment',    'create'],
        // Admin – Documents (standalone upload supprimé → uniquement validation)
        '/admin/documents/upload'             => ['AdminDocument',   'redirectUpload'],
        '/admin/documents/(\d+)/validate'     => ['AdminDocument',   'validate'],
        // Admin – File Tally
        '/admin/tally/(\d+)/match'            => ['AdminTally',      'match'],
        '/admin/tally/(\d+)/ignore'           => ['AdminTally',      'ignore'],
        // Admin – Gestion des admins (superadmin)
        '/admin/admins/create'                => ['AdminUser',       'create'],
        '/admin/admins/(\d+)/edit'            => ['AdminUser',       'edit'],
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
