<?php
declare(strict_types=1);
namespace App\Services;

use App\Models\Client;
use App\Models\LoginAttempt;

class Auth
{
    private const MAX_ATTEMPTS   = 5;
    private const LOCKOUT_MINUTES= 15;

    public static function attempt(string $accountNumber, string $password, string $ip): array
    {
        $failures = LoginAttempt::recentFailures($accountNumber, $ip, self::LOCKOUT_MINUTES);
        if ($failures >= self::MAX_ATTEMPTS) {
            return [
                'success' => false,
                'error'   => 'Trop de tentatives échouées. Réessayez dans ' . self::LOCKOUT_MINUTES . ' minutes.',
            ];
        }

        $client = Client::findByAccountNumber($accountNumber);

        if (!$client || !password_verify($password, $client['password_hash'])) {
            LoginAttempt::record($accountNumber, $ip, false);
            AuditLogger::log('client', null, 'login_failed', "account:{$accountNumber}", $ip);
            return ['success' => false, 'error' => 'Numéro de compte ou mot de passe incorrect.'];
        }

        LoginAttempt::record($accountNumber, $ip, true);
        AuditLogger::log('client', (int)$client['id'], 'login_success', "account:{$accountNumber}", $ip);

        if (password_needs_rehash($client['password_hash'], PASSWORD_BCRYPT)) {
            Client::updatePasswordHash((int)$client['id'], password_hash($password, PASSWORD_BCRYPT));
        }

        session_regenerate_id(true);
        $_SESSION['client_id']            = (int)$client['id'];
        $_SESSION['account_number']       = $client['account_number'];
        $_SESSION['client_name']          = $client['first_name'] . ' ' . $client['last_name'];
        $_SESSION['must_change_password'] = (bool)$client['must_change_password'];
        $_SESSION['_init']                = time();

        return ['success' => true, 'must_change_password' => (bool)$client['must_change_password']];
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        header('Location: /login');
        exit;
    }

    public static function client(): ?array
    {
        if (empty($_SESSION['client_id'])) return null;
        return Client::findById((int)$_SESSION['client_id']);
    }
}
