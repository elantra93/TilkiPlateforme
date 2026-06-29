<?php $pageTitle = 'Obtenir un devis – TILKI'; ?>
<?php require APP_PATH . '/Views/layout/header.php'; ?>

<div class="mb-4">
    <h2 class="h4 fw-bold mb-1">
        <i class="bi bi-pencil-square me-2 text-primary"></i>Obtenir un devis
    </h2>
    <p class="text-muted small mb-0">
        Sélectionnez la branche souhaitée. Vous serez redirigé vers notre formulaire en ligne.
    </p>
</div>

<?php if (empty($branches)): ?>
<div class="card shadow-sm">
    <div class="card-body text-center text-muted py-5">
        <i class="bi bi-pencil-square fs-1 d-block mb-2 opacity-25"></i>
        <p class="mb-1">Aucune branche disponible pour votre type de compte.</p>
        <p class="small mb-0">Contactez TILKI pour plus d'informations.</p>
    </div>
</div>
<?php else: ?>

<div class="row g-3">
    <?php foreach ($branches as $branch): ?>
    <div class="col-sm-6 col-lg-4">
        <?php if ($branch['url']): ?>
        <a href="<?= htmlspecialchars($branch['url']) ?>"
           target="_blank"
           rel="noopener noreferrer"
           class="card shadow-sm text-decoration-none h-100 branch-card">
            <div class="card-body d-flex align-items-center gap-3 p-4">
                <div class="branch-icon flex-shrink-0">
                    <i class="bi <?= htmlspecialchars($branch['icon']) ?>"></i>
                </div>
                <div>
                    <div class="fw-semibold text-body"><?= htmlspecialchars($branch['label']) ?></div>
                    <div class="small text-primary mt-1">
                        Obtenir un devis <i class="bi bi-box-arrow-up-right ms-1 fs-2xs"></i>
                    </div>
                </div>
            </div>
        </a>
        <?php else: ?>
        <div class="card shadow-sm h-100 opacity-50" title="Formulaire non encore disponible">
            <div class="card-body d-flex align-items-center gap-3 p-4">
                <div class="branch-icon branch-icon--disabled flex-shrink-0">
                    <i class="bi <?= htmlspecialchars($branch['icon']) ?>"></i>
                </div>
                <div>
                    <div class="fw-semibold text-muted"><?= htmlspecialchars($branch['label']) ?></div>
                    <div class="small text-muted mt-1">
                        <i class="bi bi-clock me-1"></i>Bientôt disponible
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>

<?php endif; ?>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
