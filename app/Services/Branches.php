<?php
declare(strict_types=1);
namespace App\Services;

class Branches
{
    // Branches commerciales — marché CI (zone CIMA)
    public const BRANCHES = [
        'Auto',
        'Moto',
        'Flotte automobile',
        'Santé individuelle',
        'Santé groupe',
        'Vie',
        'Voyage',
        'Multirisques habitation',
        'Multirisques professionnelle',
        'RC pro',
    ];

    // Branches réservées aux clients entreprise
    public const ENTREPRISE_ONLY = [
        'Flotte automobile',
        'Santé groupe',
        'Multirisques professionnelle',
        'RC pro',
    ];

    public static function forAccountType(string $type): array
    {
        if ($type === 'entreprise') {
            return self::BRANCHES;
        }
        return array_values(array_filter(
            self::BRANCHES,
            fn($b) => !in_array($b, self::ENTREPRISE_ONLY, true)
        ));
    }

    // Retourne true si la branche implique des véhicules (flotte ou mono)
    public static function isVehicleBranche(string $branche): bool
    {
        return in_array(
            mb_strtolower(trim($branche)),
            ['auto', 'automobile', 'moto', 'flotte automobile'],
            true
        );
    }
}
