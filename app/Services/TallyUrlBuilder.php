<?php
declare(strict_types=1);
namespace App\Services;

class TallyUrlBuilder
{
    /**
     * Construit l'URL du formulaire Tally de déclaration de sinistre,
     * pré-remplie avec les données du client et du contrat.
     *
     * @param array  $client        Ligne client (doit avoir account_number)
     * @param array  $contract      Ligne contrat (policy_number, insurer, branche)
     * @param string $attestationUrl URL publique de l'attestation ; passée uniquement si branche Automobile
     */
    public static function claimFormUrl(
        array  $client,
        array  $contract,
        string $attestationUrl = ''
    ): string {
        $config = require CONFIG_PATH;
        $base   = (string)($config['tally']['claim_form_url'] ?? '');
        if (!$base) return '';

        $params = [
            'account_number' => (string)($client['account_number'] ?? ''),
            'police_number'  => (string)($contract['policy_number'] ?? ''),
            'assureur'       => (string)($contract['insurer']       ?? ''),
            'branche'        => (string)($contract['branche']       ?? ''),
        ];

        $branche = strtolower(trim($contract['branche'] ?? ''));
        if (in_array($branche, ['automobile', 'auto'], true) && $attestationUrl !== '') {
            $params['attestation_picture'] = $attestationUrl;
        }

        $sep = str_contains($base, '?') ? '&' : '?';
        return $base . $sep . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }
}
