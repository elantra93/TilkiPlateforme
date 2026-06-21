<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Services\Auth;

class DevisController extends BaseController
{
    // Définition centralisée des branches : label, icône Bootstrap, clé .env, types de compte autorisés
    private const BRANCHES = [
        'auto'   => ['label' => 'Automobile',                   'icon' => 'bi-car-front-fill',    'env' => 'TALLY_DEVIS_AUTO',   'types' => ['individuel', 'entreprise']],
        'moto'   => ['label' => 'Moto',                         'icon' => 'bi-bicycle',            'env' => 'TALLY_DEVIS_MOTO',   'types' => ['individuel', 'entreprise']],
        'voyage' => ['label' => 'Assurance voyage',             'icon' => 'bi-airplane-fill',      'env' => 'TALLY_DEVIS_VOYAGE', 'types' => ['individuel', 'entreprise']],
        'sante'  => ['label' => 'Assurance santé',              'icon' => 'bi-heart-pulse-fill',   'env' => 'TALLY_DEVIS_SANTE',  'types' => ['individuel', 'entreprise']],
        'mrh'    => ['label' => 'Multirisques habitation',      'icon' => 'bi-house-fill',         'env' => 'TALLY_DEVIS_MRH',    'types' => ['individuel']],
        'mrp'    => ['label' => 'Multirisques professionnelle', 'icon' => 'bi-building-fill',      'env' => 'TALLY_DEVIS_MRP',    'types' => ['entreprise']],
        'rc'     => ['label' => 'Responsabilité civile',        'icon' => 'bi-shield-check-fill',  'env' => 'TALLY_DEVIS_RC',     'types' => ['entreprise']],
    ];

    public function index(): void
    {
        $this->requireAuth();
        $client      = Auth::client();
        $accountType = $client['account_type'] ?? 'individuel';

        $branches = [];
        foreach (self::BRANCHES as $branch) {
            if (!in_array($accountType, $branch['types'], true)) {
                continue;
            }
            $branches[] = [
                'label' => $branch['label'],
                'icon'  => $branch['icon'],
                'url'   => (string)(env($branch['env']) ?: ''),
            ];
        }

        $this->render('devis.index', [
            'client'      => $client,
            'accountType' => $accountType,
            'branches'    => $branches,
        ]);
    }
}
