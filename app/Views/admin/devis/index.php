<?php $pageTitle = 'Obtenir un devis – Administration TILKI'; ?>
<?php require APP_PATH . '/Views/admin/layout/header.php'; ?>

<div class="mb-4">
    <h2 class="h5 fw-bold mb-1">
        <i class="bi bi-pencil-square me-2 text-primary"></i>Obtenir un devis
    </h2>
    <p class="text-muted small mb-0">
        Accédez aux formulaires de devis par branche pour accompagner vos clients.
    </p>
</div>

<?php if (empty($branches)): ?>
<div class="alert alert-info">
    <i class="bi bi-info-circle me-2"></i>
    Aucun formulaire de devis configuré. Renseignez les clés <code>TALLY_DEVIS_*</code> dans le fichier <code>.env</code>.
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
                        Ouvrir le formulaire <i class="bi bi-box-arrow-up-right ms-1" style="font-size:.7rem"></i>
                    </div>
                </div>
            </div>
        </a>
        <?php else: ?>
        <div class="card shadow-sm h-100 opacity-50" title="URL non configurée dans .env">
            <div class="card-body d-flex align-items-center gap-3 p-4">
                <div class="branch-icon branch-icon--disabled flex-shrink-0">
                    <i class="bi <?= htmlspecialchars($branch['icon']) ?>"></i>
                </div>
                <div>
                    <div class="fw-semibold text-muted"><?= htmlspecialchars($branch['label']) ?></div>
                    <div class="small text-muted mt-1">
                        <i class="bi bi-exclamation-circle me-1"></i>URL non configurée
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>

<?php endif; ?>

<?php require APP_PATH . '/Views/admin/layout/footer.php'; ?>
