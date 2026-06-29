<?php
declare(strict_types=1);
namespace App\Services;

class Mailer
{
    public static function sendPasswordReset(array $client, string $token): bool
    {
        $config   = require CONFIG_PATH;
        $appName  = $config['app']['name'] ?? 'TILKI';
        $appUrl   = rtrim($config['app']['url'] ?? 'http://localhost', '/');
        $isDev    = ($config['app']['env'] ?? 'production') === 'development';

        $resetUrl = $appUrl . '/password/reset?token=' . urlencode($token);
        $to       = $client['email'];
        $name     = $client['first_name'] . ' ' . $client['last_name'];
        $subject  = '[' . $appName . '] Réinitialisation de votre mot de passe';
        $body     = self::buildBody($appName, $name, $resetUrl);

        self::log($to, $subject, $resetUrl);

        $useSmtp = !empty($config['mail']['smtp']['host']);
        $sent = $useSmtp
            ? self::sendViaSMTP($to, $subject, $body, $config)
            : self::sendViaMail($to, $subject, $body, $config);

        if ($isDev) {
            $_SESSION['dev_reset_url'] = $resetUrl;
        }

        return $sent;
    }

    private static function sendViaMail(string $to, string $subject, string $body, array $config): bool
    {
        $from     = $config['mail']['from'] ?? ('noreply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
        $fromName = $config['mail']['from_name'] ?? 'TILKI';
        $headers  = implode("\r\n", [
            "From: {$fromName} <{$from}>",
            "Reply-To: {$from}",
            'Content-Type: text/plain; charset=UTF-8',
            'X-Mailer: TilkiPortal/1.0',
        ]);
        return @mail($to, $subject, $body, $headers);
    }

    private static function sendViaSMTP(string $to, string $subject, string $body, array $config): bool
    {
        $smtp     = $config['mail']['smtp'];
        $host     = $smtp['host'];
        $port     = (int)($smtp['port'] ?? 587);
        $user     = $smtp['user'];
        $pass     = $smtp['pass'];
        $secure   = $smtp['secure'] ?? 'tls';
        $from     = $config['mail']['from'];
        $fromName = $config['mail']['from_name'] ?? 'TILKI';

        $transport = ($secure === 'ssl') ? 'ssl' : 'tcp';
        $socket = @fsockopen("{$transport}://{$host}", $port, $errno, $errstr, 15);
        if (!$socket) {
            error_log("[Mailer] Connexion SMTP échouée ({$errno}): {$errstr}");
            return false;
        }

        try {
            self::smtpExpect($socket, '220');

            self::smtpCmd($socket, 'EHLO ' . ($_SERVER['SERVER_NAME'] ?? 'localhost'));
            self::smtpExpect($socket, '250');

            if ($secure === 'tls') {
                self::smtpCmd($socket, 'STARTTLS');
                self::smtpExpect($socket, '220');
                stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                self::smtpCmd($socket, 'EHLO ' . ($_SERVER['SERVER_NAME'] ?? 'localhost'));
                self::smtpExpect($socket, '250');
            }

            self::smtpCmd($socket, 'AUTH LOGIN');
            self::smtpExpect($socket, '334');
            self::smtpCmd($socket, base64_encode($user));
            self::smtpExpect($socket, '334');
            self::smtpCmd($socket, base64_encode($pass));
            self::smtpExpect($socket, '235');

            self::smtpCmd($socket, "MAIL FROM:<{$from}>");
            self::smtpExpect($socket, '250');
            self::smtpCmd($socket, "RCPT TO:<{$to}>");
            self::smtpExpect($socket, '250');
            self::smtpCmd($socket, 'DATA');
            self::smtpExpect($socket, '354');

            $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
            $message = "From: {$fromName} <{$from}>\r\n"
                . "To: {$to}\r\n"
                . "Subject: {$encodedSubject}\r\n"
                . "MIME-Version: 1.0\r\n"
                . "Content-Type: text/plain; charset=UTF-8\r\n"
                . "Content-Transfer-Encoding: base64\r\n"
                . "\r\n"
                . chunk_split(base64_encode($body))
                . "\r\n.\r\n";

            fwrite($socket, $message);
            self::smtpExpect($socket, '250');

            self::smtpCmd($socket, 'QUIT');
            fclose($socket);
            return true;
        } catch (\RuntimeException $e) {
            error_log('[Mailer] SMTP erreur: ' . $e->getMessage());
            @fclose($socket);
            return false;
        }
    }

    private static function smtpCmd($socket, string $cmd): void
    {
        fwrite($socket, $cmd . "\r\n");
    }

    private static function smtpExpect($socket, string $expectedCode): string
    {
        $response = '';
        while ($line = fgets($socket, 512)) {
            $response .= $line;
            if (strlen($line) >= 4 && $line[3] === ' ') break;
        }
        if (!str_starts_with($response, $expectedCode)) {
            throw new \RuntimeException("Réponse SMTP inattendue (attendu {$expectedCode}): " . trim($response));
        }
        return $response;
    }

    public static function send(string $to, string $toName, string $subject, string $html): bool
    {
        $config   = require CONFIG_PATH;
        $from     = $config['mail']['from'] ?? ('noreply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
        $fromName = $config['mail']['from_name'] ?? 'TILKI Assurances';

        self::logEntry($to, $subject);

        $useSmtp = !empty($config['mail']['smtp']['host']);
        if ($useSmtp) {
            return self::sendHtmlViaSMTP($to, $toName, $subject, $html, $from, $fromName, $config);
        }
        return self::sendHtmlViaMail($to, $toName, $subject, $html, $from, $fromName);
    }

    private static function sendHtmlViaMail(
        string $to, string $toName, string $subject, string $html,
        string $from, string $fromName
    ): bool {
        $boundary = '==Tilki_' . bin2hex(random_bytes(8));
        $text     = html_entity_decode(strip_tags((string)preg_replace('#<br\s*/?>|</p>#i', "\n", $html)), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-Type: multipart/alternative; boundary="' . $boundary . '"' . "\r\n";
        $headers .= 'From: ' . self::mbEncode($fromName) . ' <' . $from . '>' . "\r\n";
        $headers .= 'Reply-To: ' . $from . "\r\n";
        $headers .= 'X-Mailer: TilkiPortal/1.0';

        $body  = '--' . $boundary . "\r\n";
        $body .= "Content-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n";
        $body .= chunk_split(base64_encode(trim($text))) . "\r\n";
        $body .= '--' . $boundary . "\r\n";
        $body .= "Content-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n";
        $body .= chunk_split(base64_encode($html)) . "\r\n";
        $body .= '--' . $boundary . '--';

        return @mail(self::mbEncode($toName) . ' <' . $to . '>', self::mbEncode($subject), $body, $headers);
    }

    private static function sendHtmlViaSMTP(
        string $to, string $toName, string $subject, string $html,
        string $from, string $fromName, array $config
    ): bool {
        $smtp     = $config['mail']['smtp'];
        $host     = $smtp['host'];
        $port     = (int)($smtp['port'] ?? 587);
        $user     = $smtp['user'];
        $pass     = $smtp['pass'];
        $secure   = $smtp['secure'] ?? 'tls';
        $boundary = '==Tilki_' . bin2hex(random_bytes(8));
        $text     = html_entity_decode(strip_tags((string)preg_replace('#<br\s*/?>|</p>#i', "\n", $html)), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $transport = ($secure === 'ssl') ? 'ssl' : 'tcp';
        $socket = @fsockopen("{$transport}://{$host}", $port, $errno, $errstr, 15);
        if (!$socket) {
            error_log("[Mailer] Connexion SMTP échouée ({$errno}): {$errstr}");
            return false;
        }

        try {
            self::smtpExpect($socket, '220');
            self::smtpCmd($socket, 'EHLO ' . ($_SERVER['SERVER_NAME'] ?? 'localhost'));
            self::smtpExpect($socket, '250');

            if ($secure === 'tls') {
                self::smtpCmd($socket, 'STARTTLS');
                self::smtpExpect($socket, '220');
                stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                self::smtpCmd($socket, 'EHLO ' . ($_SERVER['SERVER_NAME'] ?? 'localhost'));
                self::smtpExpect($socket, '250');
            }

            self::smtpCmd($socket, 'AUTH LOGIN');
            self::smtpExpect($socket, '334');
            self::smtpCmd($socket, base64_encode($user));
            self::smtpExpect($socket, '334');
            self::smtpCmd($socket, base64_encode($pass));
            self::smtpExpect($socket, '235');

            self::smtpCmd($socket, "MAIL FROM:<{$from}>");
            self::smtpExpect($socket, '250');
            self::smtpCmd($socket, "RCPT TO:<{$to}>");
            self::smtpExpect($socket, '250');
            self::smtpCmd($socket, 'DATA');
            self::smtpExpect($socket, '354');

            $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
            $message = "From: {$fromName} <{$from}>\r\n"
                . "To: {$toName} <{$to}>\r\n"
                . "Subject: {$encodedSubject}\r\n"
                . "MIME-Version: 1.0\r\n"
                . "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n\r\n"
                . "--{$boundary}\r\n"
                . "Content-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n"
                . chunk_split(base64_encode(trim($text))) . "\r\n"
                . "--{$boundary}\r\n"
                . "Content-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n"
                . chunk_split(base64_encode($html)) . "\r\n"
                . "--{$boundary}--\r\n.\r\n";

            fwrite($socket, $message);
            self::smtpExpect($socket, '250');
            self::smtpCmd($socket, 'QUIT');
            fclose($socket);
            return true;
        } catch (\RuntimeException $e) {
            error_log('[Mailer] SMTP erreur: ' . $e->getMessage());
            @fclose($socket);
            return false;
        }
    }

    private static function mbEncode(string $s): string
    {
        return preg_match('/[^\x20-\x7E]/', $s)
            ? '=?UTF-8?B?' . base64_encode($s) . '?='
            : $s;
    }

    private static function logEntry(string $to, string $subject): void
    {
        $config  = require CONFIG_PATH;
        $logDir  = $config['storage']['logs'] ?? ROOT_PATH . '/storage/logs';
        @file_put_contents(
            $logDir . '/mail.log',
            sprintf("[%s] TO=%s SUBJECT=%s\n", date('Y-m-d H:i:s'), $to, $subject),
            FILE_APPEND | LOCK_EX
        );
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
