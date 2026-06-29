<?php
declare(strict_types=1);
namespace App\Services;

class ContractDocTypes
{
    // Types attendus par branche pour la catégorie "souscription"
    private const BY_BRANCH = [
        'automobile' => [
            ['key' => 'conditions_particulieres', 'label' => 'Conditions particulières', 'required' => true],
            ['key' => 'attestation_assurance',    'label' => "Attestation d'assurance",  'required' => true],
            ['key' => 'attestation_cedeao',       'label' => 'Attestation CEDEAO',       'required' => true],
            ['key' => 'conditions_generales',     'label' => 'Conditions générales',     'required' => false],
        ],
        'sante' => [
            ['key' => 'contrat',           'label' => 'Contrat',             'required' => true],
            ['key' => 'tableau_garanties', 'label' => 'Tableau de garanties','required' => true],
            ['key' => 'reseau_soins',      'label' => 'Réseau de soins',     'required' => true],
        ],
    ];

    // Fallback générique (branches non structurées)
    public const GENERIC_SOUSCRIPTION = [
        'contrat', 'avenant', 'preuve_paiement', 'quittance', 'attestation', 'decompte',
    ];

    public static function forBranche(string $branche): ?array
    {
        return self::BY_BRANCH[self::normalise($branche)] ?? null;
    }

    public static function all(): array
    {
        return self::BY_BRANCH;
    }

    private static function normalise(string $branche): string
    {
        $b = mb_strtolower(trim($branche));
        if (in_array($b, ['sante', 'santé', 'health', 'assurance santé', 'assurance sante'], true)) {
            return 'sante';
        }
        if (in_array($b, ['automobile', 'auto', 'automotive'], true)) {
            return 'automobile';
        }
        return $b;
    }
}
