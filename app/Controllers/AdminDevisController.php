<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Middleware\AdminMiddleware;

class AdminDevisController extends BaseController
{
    private const BRANCHES = [
        ['label' => 'Automobile',                   'icon' => 'bi-car-front-fill',   'env' => 'TALLY_DEVIS_AUTO'],
        ['label' => 'Moto',                         'icon' => 'bi-bicycle',           'env' => 'TALLY_DEVIS_MOTO'],
        ['label' => 'Assurance voyage',             'icon' => 'bi-airplane-fill',     'env' => 'TALLY_DEVIS_VOYAGE'],
        ['label' => 'Assurance santé',              'icon' => 'bi-heart-pulse-fill',  'env' => 'TALLY_DEVIS_SANTE'],
        ['label' => 'Multirisques habitation',      'icon' => 'bi-house-fill',        'env' => 'TALLY_DEVIS_MRH'],
        ['label' => 'Multirisques professionnelle', 'icon' => 'bi-building-fill',     'env' => 'TALLY_DEVIS_MRP'],
        ['label' => 'Responsabilité civile',        'icon' => 'bi-shield-check-fill', 'env' => 'TALLY_DEVIS_RC'],
    ];

    public function index(): void
    {
        AdminMiddleware::check();

        $branches = [];
        foreach (self::BRANCHES as $branch) {
            $branches[] = [
                'label' => $branch['label'],
                'icon'  => $branch['icon'],
                'url'   => (string)(env($branch['env']) ?: ''),
            ];
        }

        $this->render('admin.devis.index', [
            'branches' => $branches,
        ]);
    }
}
