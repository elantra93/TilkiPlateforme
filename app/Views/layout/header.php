<?php
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle ?? 'TILKI Portail Client') ?></title>
    <link rel="icon" type="image/svg+xml" href="/logoparapluie.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="/dashboard">
            <img src="/logoparapluie.svg" alt="TILKI" height="40" style="width:auto; filter: brightness(0) invert(1);">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/dashboard"><i class="bi bi-speedometer2 me-1"></i>Tableau de bord</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/contracts"><i class="bi bi-file-earmark-text me-1"></i>Contrats</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/claims"><i class="bi bi-exclamation-triangle me-1"></i>Sinistres</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/payments"><i class="bi bi-cash-coin me-1"></i>Paiements</a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($_SESSION['client_name'] ?? '') ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="/account">
                                <i class="bi bi-person-gear me-2"></i>Paramètres de compte
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

<main class="container py-4">
<?php if ($flash): ?>
<div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show" role="alert">
    <i class="bi bi-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?> me-2"></i>
    <?= htmlspecialchars($flash['msg']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
