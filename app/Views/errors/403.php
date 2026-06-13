<?php $pageTitle = '403 – Accès refusé'; ?>
<?php require APP_PATH . '/Views/layout/header.php'; ?>

<div class="text-center py-5">
    <h1 class="display-1 fw-bold text-danger">403</h1>
    <p class="lead mb-4">Vous n&rsquo;avez pas accès à cette ressource.</p>
    <a href="/dashboard" class="btn btn-primary">
        <i class="bi bi-house me-2"></i>Tableau de bord
    </a>
</div>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
