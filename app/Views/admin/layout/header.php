<?php
$flash = $_SESSION['admin_flash'] ?? null;
unset($_SESSION['admin_flash']);
$_tkPath = rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/') ?: '/';
function _tk_active(string $prefix, string $current): string {
    return str_starts_with($current, $prefix) ? ' active' : '';
}
// Count badge pour paiements en attente (requête légère, cached par OPcache si activé)
$_tkPayPending = 0;
try {
    $_tkPayPending = (int)\App\Models\Payment::countPending();
} catch (\Throwable $_e) {}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle ?? 'Administration – TILKI') ?></title>
    <link rel="icon" type="image/svg+xml" href="/logoparapluie.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body class="tk-admin">

<div class="tk-sidebar-overlay" id="tkOverlay"></div>

<aside class="tk-sidebar" id="tkSidebar">

    <a class="tk-sidebar-brand" href="/admin/dashboard">
        <img src="/logoparapluie.svg" alt="TILKI" height="30" style="width:auto">
        <span class="badge bg-white text-primary fw-semibold ms-1 fs-badge">Admin</span>
    </a>

    <nav class="tk-sidebar-nav">
        <a href="/admin/dashboard" class="tk-sidebar-link<?= _tk_active('/admin/dashboard', $_tkPath) ?>">
            <i class="bi bi-speedometer2"></i><span>Tableau de bord</span>
        </a>
        <a href="/admin/clients" class="tk-sidebar-link<?= _tk_active('/admin/clients', $_tkPath) ?>">
            <i class="bi bi-people"></i><span>Clients</span>
        </a>
        <a href="/admin/contracts" class="tk-sidebar-link<?= _tk_active('/admin/contracts', $_tkPath) ?>">
            <i class="bi bi-file-earmark-text"></i><span>Contrats</span>
        </a>
        <a href="/admin/claims" class="tk-sidebar-link<?= _tk_active('/admin/claims', $_tkPath) ?>">
            <i class="bi bi-exclamation-triangle"></i><span>Sinistres</span>
        </a>
        <a href="/admin/payments" class="tk-sidebar-link<?= _tk_active('/admin/payments', $_tkPath) ?>">
            <i class="bi bi-credit-card"></i>
            <span>Paiements</span>
            <?php if ($_tkPayPending > 0): ?>
            <span class="badge bg-warning text-dark ms-auto fs-badge"><?= $_tkPayPending ?></span>
            <?php endif; ?>
        </a>
        <a href="/admin/devis" class="tk-sidebar-link<?= _tk_active('/admin/devis', $_tkPath) ?>">
            <i class="bi bi-pencil-square"></i><span>Devis</span>
        </a>
        <a href="/admin/insurers" class="tk-sidebar-link<?= _tk_active('/admin/insurers', $_tkPath) ?>">
            <i class="bi bi-building"></i><span>Assureurs</span>
        </a>
        <a href="/admin/relances" class="tk-sidebar-link<?= _tk_active('/admin/relances', $_tkPath) ?>">
            <i class="bi bi-bell"></i><span>Relances</span>
        </a>

        <div class="tk-sidebar-section">Documents</div>
        <a href="/admin/documents/pending" class="tk-sidebar-link<?= _tk_active('/admin/documents', $_tkPath) ?>">
            <i class="bi bi-hourglass-split"></i><span>En attente</span>
        </a>
        <a href="/admin/tally/queue" class="tk-sidebar-link<?= _tk_active('/admin/tally', $_tkPath) ?>">
            <i class="bi bi-inbox"></i><span>File Tally</span>
        </a>

        <?php if (($_SESSION['admin_role'] ?? '') === 'superadmin'): ?>
        <div class="tk-sidebar-section">Administration</div>
        <a href="/admin/admins" class="tk-sidebar-link<?= _tk_active('/admin/admins', $_tkPath) ?>">
            <i class="bi bi-person-badge"></i><span>Gestion admins</span>
        </a>
        <?php endif; ?>
    </nav>

    <div class="tk-sidebar-footer">
        <div class="tk-sidebar-user">
            <i class="bi bi-person-circle fs-5 flex-shrink-0"></i>
            <div class="overflow-hidden">
                <div class="tk-sidebar-user-name"><?= htmlspecialchars($_SESSION['admin_name'] ?? '') ?></div>
                <div class="tk-sidebar-user-role"><?= htmlspecialchars($_SESSION['admin_role'] ?? '') ?></div>
            </div>
        </div>
        <a href="/admin/password/change" class="tk-sidebar-link">
            <i class="bi bi-key"></i><span>Changer mot de passe</span>
        </a>
        <form method="post" action="/admin/logout">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
            <button type="submit" class="tk-sidebar-link tk-logout">
                <i class="bi bi-box-arrow-right"></i><span>Déconnexion</span>
            </button>
        </form>
    </div>
</aside>

<div class="tk-main" id="tkMain">
    <header class="tk-topbar">
        <button class="tk-topbar-toggle" id="tkToggle" aria-label="Ouvrir le menu">
            <i class="bi bi-list fs-5"></i>
        </button>
        <span class="tk-topbar-title"><?= htmlspecialchars($pageTitle ?? '') ?></span>
    </header>

    <div class="tk-content">
<?php if ($flash): ?>
<div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show">
    <?= htmlspecialchars($flash['msg']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
