<?php
$flash = $_SESSION['admin_flash'] ?? null;
unset($_SESSION['admin_flash']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle ?? 'Administration – TILKI') ?></title>
    <link rel="icon" type="image/svg+xml" href="/logoparapluie.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid px-4">
        <a class="navbar-brand d-flex align-items-center gap-2" href="/admin/dashboard">
            <img src="/logoparapluie.svg" alt="TILKI" height="40" style="width:auto; filter: brightness(0) invert(1);">
            <span class="badge bg-warning text-dark small">Admin</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="adminNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/admin/dashboard">
                        <i class="bi bi-speedometer2 me-1"></i>Tableau de bord
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/admin/clients">
                        <i class="bi bi-people me-1"></i>Clients
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/admin/contracts">
                        <i class="bi bi-file-earmark-text me-1"></i>Contrats
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/admin/claims">
                        <i class="bi bi-exclamation-triangle me-1"></i>Sinistres
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-paperclip me-1"></i>Documents
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark">
                        <li>
                            <a class="dropdown-item" href="/admin/documents/upload">
                                <i class="bi bi-upload me-2"></i>Uploader
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="/admin/documents/pending">
                                <i class="bi bi-hourglass-split me-2"></i>En attente de validation
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="/admin/tally/queue">
                                <i class="bi bi-inbox me-2"></i>File Tally
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-person-badge me-1"></i>
                        <?= htmlspecialchars($_SESSION['admin_name'] ?? '') ?>
                        <span class="badge bg-secondary ms-1 small">
                            <?= htmlspecialchars($_SESSION['admin_role'] ?? '') ?>
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="/admin/password/change">
                                <i class="bi bi-key me-2"></i>Changer mon mot de passe
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="post" action="/admin/logout">
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

<main class="container-fluid px-4 py-4">
<?php if ($flash): ?>
<div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show">
    <?= htmlspecialchars($flash['msg']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
