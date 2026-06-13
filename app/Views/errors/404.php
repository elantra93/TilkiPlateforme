<?php
if (!empty($_SESSION['client_id'])):
    $pageTitle = '404 – Page introuvable';
    require APP_PATH . '/Views/layout/header.php';
endif; ?>

<?php if (empty($_SESSION['client_id'])): ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>404 – TILKI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center min-vh-100">
<?php endif; ?>

<div class="text-center py-5">
    <h1 class="display-1 fw-bold text-muted">404</h1>
    <p class="lead mb-4">Cette page est introuvable.</p>
    <a href="/dashboard" class="btn btn-primary">
        <i class="bi bi-house me-2"></i>Tableau de bord
    </a>
</div>

<?php if (!empty($_SESSION['client_id'])): ?>
    <?php require APP_PATH . '/Views/layout/footer.php'; ?>
<?php else: ?>
</body></html>
<?php endif; ?>
