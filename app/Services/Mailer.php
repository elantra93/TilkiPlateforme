<?php
declare(strict_types=1);
namespace App\Services;

class Mailer
{
    public static function sendPasswordReset(array $client, string $token): bool
    {
        $config   = require CONFIG_PATH;
        $appName  = $config['app']['name']  ?? 'TILKI';
        $appUrl   = rtrim($config['app']['url'] ?? 'http://localhost', '/');
        $isDev    = ($config['app']['env']  ?? 'production') === 'development';

        $resetUrl = $appUrl . '/password/reset?token=' . urlencode($token);
        $to       = $client['email'];
        $name     = $client['first_name'] . ' ' . $client['last_name'];

        $subject  = '[' . $appName . '] Réinitialisation de votre mot de passe';
        $body     = self::buildBody($appName, $name, $resetUrl);
        $headers  = implode("\r\n", [
            'From: noreply@tilki.sn',
            'Reply-To: noreply@tilki.sn',
            'Content-Type: text/plain; charset=UTF-8',
            'X-Mailer: TilkiPortal/1.0',
        ]);

        // Always log (useful in dev and as audit trail)
        self::log($to, $subject, $resetUrl);

        $sent = @mail($to, $subject, $body, $headers);

        // In development, expose the URL in the session so the controller can display it
        if ($isDev) {
            $_SESSION['dev_reset_url'] = $resetUrl;
        }

        return $sent;
    }

    private static function buildBody(string $appName, string $name, string $resetUrl): string
    {
        return <<<TEXT
        Bonjour {$name},

        Vous avez demandé la réinitialisation de votre mot de passe sur le portail {$appName}.

        Cliquez sur le lien ci-dessous (valable 1 heure) :
        {$resetUrl}

        Si vous n'êtes pas à l'origine de cette demande, ignorez simplement ce message.
        Votre mot de passe restera inchangé.

        — L'équipe {$appName}
        TEXT;
    }

    private static function log(string $to, string $subject, string $resetUrl): void
    {
        $config  = require CONFIG_PATH;
        $logDir  = $config['storage']['logs'] ?? ROOT_PATH . '/storage/logs';
        $logFile = $logDir . '/mail.log';

        $line = sprintf(
            "[%s] TO=%s SUBJECT=%s URL=%s\n",
            date('Y-m-d H:i:s'),
            $to,
            $subject,
            $resetUrl
        );

        @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
    }
}
