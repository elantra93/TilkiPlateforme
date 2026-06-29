<?php
declare(strict_types=1);
namespace App\Services;

use App\Models\Contract;
use App\Models\Relance;

class RelanceEngine
{
    /**
     * Process all active contracts due for a relance today.
     * Returns a summary array ['sent' => n, 'skipped' => n, 'failed' => n].
     */
    public static function processAll(): array
    {
        $contracts = Contract::expiringSoon(60, 30);
        $summary   = ['sent' => 0, 'skipped' => 0, 'failed' => 0];

        foreach ($contracts as $contract) {
            $type = Relance::dueTypeForExpiry($contract['expiry_date']);
            if (!$type) {
                continue;
            }
            if (Relance::hasSentType((int)$contract['id'], $type)) {
                $summary['skipped']++;
                continue;
            }
            $ok = self::send($contract, $type, null);
            $ok ? $summary['sent']++ : $summary['failed']++;
        }

        return $summary;
    }

    /**
     * Manually trigger a relance for a specific contract (admin action).
     * Uses the type appropriate for today's date or falls back to the closest one.
     */
    public static function sendManual(array $contract, string $type, int $adminId): bool
    {
        return self::send($contract, $type, $adminId);
    }

    private static function send(array $contract, string $type, ?int $adminId): bool
    {
        $relanceId = Relance::create([
            'contract_id' => (int)$contract['id'],
            'client_id'   => (int)$contract['client_id'],
            'type'        => $type,
            'channel'     => 'email',
            'status'      => 'planifiee',
            'admin_id'    => $adminId,
        ]);

        $to   = $contract['email'] ?? '';
        $isEntreprise = ($contract['account_type'] ?? '') === 'entreprise';
        $name = $isEntreprise && !empty($contract['company_name'])
            ? trim($contract['company_name'])
            : trim(($contract['first_name'] ?? '') . ' ' . ($contract['last_name'] ?? ''));

        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            Relance::markFailed($relanceId, 'Adresse email client invalide ou absente.');
            Contract::updateRelanceStatus((int)$contract['id'], 'echouee');
            return false;
        }

        $config  = require CONFIG_PATH;
        $appName = $config['app']['name'] ?? 'TILKI';
        $appUrl  = rtrim($config['app']['url'] ?? '', '/');

        $subject = self::subject($type, $appName);
        $html    = self::html($type, $contract, $name, $appName, $appUrl);

        try {
            $sent = Mailer::send($to, $name, $subject, $html);
        } catch (\Throwable $e) {
            $sent = false;
            $error = $e->getMessage();
        }

        if ($sent) {
            Relance::markSent($relanceId);
            Contract::updateRelanceStatus((int)$contract['id'], 'envoyee');
        } else {
            Relance::markFailed($relanceId, $error ?? 'Échec d\'envoi inconnu.');
            Contract::updateRelanceStatus((int)$contract['id'], 'echouee');
        }

        return $sent;
    }

    private static function subject(string $type, string $appName): string
    {
        $labels = [
            'j-60'     => 'Votre contrat expire dans 60 jours',
            'j-30'     => 'Votre contrat expire dans 30 jours',
            'j-15'     => 'Votre contrat expire dans 15 jours',
            'j-7'      => 'Votre contrat expire dans 7 jours',
            'echeance' => "Votre contrat arrive à échéance aujourd'hui",
            'j+7'      => 'Votre contrat est arrivé à échéance — Action requise',
            'j+30'     => 'Rappel urgent : renouvellement de contrat',
        ];
        return '[' . $appName . '] ' . ($labels[$type] ?? 'Rappel de contrat');
    }

    private static function html(string $type, array $c, string $name, string $appName, string $appUrl): string
    {
        $policyNumber = htmlspecialchars($c['policy_number'] ?? '');
        $branche      = htmlspecialchars($c['branche']       ?? '');
        $insurer      = htmlspecialchars($c['insurer']        ?? '');
        $expiryFmt    = $c['expiry_date'] ? date('d/m/Y', strtotime($c['expiry_date'])) : '—';
        $daysLeft     = (int)($c['days_until_expiry'] ?? 0);
        $nameSafe     = htmlspecialchars($name);
        $appSafe      = htmlspecialchars($appName);

        $urgency = match(true) {
            $daysLeft <= 0 => '#dc3545',  // red
            $daysLeft <= 7 => '#fd7e14',  // orange
            default        => '#0F47F5',  // brand blue
        };

        if ($daysLeft > 0) {
            $contextLine = "Votre contrat <strong>{$policyNumber}</strong> expire dans <strong>{$daysLeft} jour(s)</strong>, le {$expiryFmt}.";
        } elseif ($daysLeft === 0) {
            $contextLine = "Votre contrat <strong>{$policyNumber}</strong> arrive à échéance <strong>aujourd'hui</strong> ({$expiryFmt}).";
        } else {
            $overdue = abs($daysLeft);
            $contextLine = "Votre contrat <strong>{$policyNumber}</strong> est arrivé à échéance il y a <strong>{$overdue} jour(s)</strong> ({$expiryFmt}). Votre couverture n'est plus active.";
        }

        $loginUrl = htmlspecialchars($appUrl . '/login');

        return <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>{$appSafe}</title></head>
<body style="margin:0;padding:0;background:#F5F5F7;font-family:'Segoe UI',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#F5F5F7;padding:32px 0;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0"
       style="background:#fff;border-radius:12px;overflow:hidden;max-width:600px;width:100%;">

  <!-- Header -->
  <tr><td style="background:{$urgency};padding:28px 32px;">
    <p style="margin:0;color:#fff;font-size:22px;font-weight:700;">{$appSafe}</p>
    <p style="margin:4px 0 0;color:rgba(255,255,255,0.85);font-size:13px;">Courtage en assurance</p>
  </td></tr>

  <!-- Body -->
  <tr><td style="padding:32px;">
    <p style="margin:0 0 16px;font-size:15px;color:#1d1d1f;">Bonjour <strong>{$nameSafe}</strong>,</p>
    <p style="margin:0 0 24px;font-size:15px;color:#3d3d3f;line-height:1.6;">{$contextLine}</p>

    <table width="100%" cellpadding="12" cellspacing="0"
           style="background:#F5F5F7;border-radius:8px;margin-bottom:24px;">
      <tr>
        <td style="font-size:13px;color:#6e6e73;font-weight:600;width:40%;">N° de police</td>
        <td style="font-size:13px;color:#1d1d1f;font-weight:700;">{$policyNumber}</td>
      </tr>
      <tr>
        <td style="font-size:13px;color:#6e6e73;font-weight:600;">Branche</td>
        <td style="font-size:13px;color:#1d1d1f;">{$branche}</td>
      </tr>
      <tr>
        <td style="font-size:13px;color:#6e6e73;font-weight:600;">Assureur</td>
        <td style="font-size:13px;color:#1d1d1f;">{$insurer}</td>
      </tr>
      <tr>
        <td style="font-size:13px;color:#6e6e73;font-weight:600;">Date d'échéance</td>
        <td style="font-size:13px;color:{$urgency};font-weight:700;">{$expiryFmt}</td>
      </tr>
    </table>

    <p style="margin:0 0 24px;font-size:14px;color:#3d3d3f;line-height:1.6;">
      Pour renouveler votre contrat ou obtenir une nouvelle cotation,
      connectez-vous à votre espace client ou contactez votre conseiller TILKI.
    </p>

    <table cellpadding="0" cellspacing="0" style="margin-bottom:32px;">
      <tr><td style="background:{$urgency};border-radius:8px;padding:12px 28px;">
        <a href="{$loginUrl}" style="color:#fff;font-size:14px;font-weight:700;text-decoration:none;">
          Accéder à mon espace client →
        </a>
      </td></tr>
    </table>

    <p style="margin:0;font-size:13px;color:#6e6e73;border-top:1px solid #e5e5ea;padding-top:24px;">
      Cet email a été envoyé automatiquement par {$appSafe}.<br>
      Si vous venez de renouveler votre contrat, veuillez ignorer ce message.
    </p>
  </td></tr>

</table>
</td></tr>
</table>
</body>
</html>
HTML;
    }
}
