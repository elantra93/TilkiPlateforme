<?php
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$_tkPath = rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/') ?: '/';
function _tk_tab(string $prefix, string $current): string {
    return str_starts_with($current, $prefix) ? ' active' : '';
}
/**
 * Icône Bootstrap Icons associée à une branche d'assurance.
 * Utilisée par les tuiles bleutées (.tk-icon-tile) du canevas TILKI Portail.
 */
if (!function_exists('tk_branche_icon')) {
    function tk_branche_icon(?string $branche): string {
        $b = strtolower((string) $branche);
        return match (true) {
            str_contains($b, 'auto') || str_contains($b, 'véhic') || str_contains($b, 'vehic') => 'bi-car-front',
            str_contains($b, 'moto') || str_contains($b, 'deux-roues')                          => 'bi-bicycle',
            str_contains($b, 'sant') || str_contains($b, 'maladie')                             => 'bi-heart-pulse',
            str_contains($b, 'voyage')                                                          => 'bi-airplane',
            str_contains($b, 'habit') || str_contains($b, 'mrh') || str_contains($b, 'incendie') || str_contains($b, 'maison') => 'bi-house',
            str_contains($b, 'transport') || str_contains($b, 'marchand')                       => 'bi-truck',
            str_contains($b, 'vie') || str_contains($b, 'décès') || str_contains($b, 'deces')   => 'bi-shield-heart',
            str_contains($b, 'rc') || str_contains($b, 'pro') || str_contains($b, 'entreprise') => 'bi-building',
            default                                                                             => 'bi-shield',
        };
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle ?? 'TILKI Portail Client') ?></title>
    <link rel="icon" type="image/svg+xml" href="/logoparapluie.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body>

<!-- Barre supérieure -->
<nav class="tk-topnav navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="/dashboard">
            <img src="/logoblanc.svg" alt="TILKI" height="36" style="width:auto">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#tkClientNav" aria-controls="tkClientNav" aria-expanded="false" aria-label="Navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="tkClientNav">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i>
                        <span><?= htmlspecialchars($_SESSION['client_name'] ?? '') ?></span>
                        <?php if (($_SESSION['client_type'] ?? 'individuel') === 'entreprise'): ?>
                        <span class="badge bg-white bg-opacity-25 text-white fw-normal" style="font-size:.65rem">Entreprise</span>
                        <?php else: ?>
                        <span class="badge bg-white bg-opacity-25 text-white fw-normal" style="font-size:.65rem">Particulier</span>
                        <?php endif; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="/account">
                                <i class="bi bi-person-gear me-2"></i>Mon compte
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="post" action="/logout">
                                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                <button class="dropdown-item text-danger" type="submit">
                                    <i class="bi bi-box-arrow-right me-2"></i>Déconnexion
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Onglets de navigation -->
<nav class="tk-tabnav">
    <div class="container">
        <ul class="nav tk-tabs">
            <li class="nav-item">
                <a class="nav-link<?= _tk_tab('/dashboard', $_tkPath) ?>" href="/dashboard">
                    <i class="bi bi-speedometer2"></i>Tableau de bord
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link<?= _tk_tab('/contracts', $_tkPath) ?>" href="/contracts">
                    <i class="bi bi-file-earmark-text"></i>Contrats
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link<?= _tk_tab('/claims', $_tkPath) ?>" href="/claims">
                    <i class="bi bi-exclamation-triangle"></i>Sinistres
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link<?= _tk_tab('/payments', $_tkPath) ?>" href="/payments">
                    <i class="bi bi-credit-card"></i>Paiements
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link<?= _tk_tab('/devis', $_tkPath) ?>" href="/devis">
                    <i class="bi bi-pencil-square"></i>Devis
                </a>
            </li>
        </ul>
    </div>
</nav>

<main class="container py-4">
<?php if ($flash): ?>
<div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show" role="alert">
    <i class="bi bi-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?> me-2"></i>
    <?= htmlspecialchars($flash['msg']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
