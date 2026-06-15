<?php $pageTitle = 'Déclarer un sinistre – TILKI'; ?>
<?php require APP_PATH . '/Views/layout/header.php'; ?>

<div class="mb-3">
    <a href="/claims" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Retour aux sinistres
    </a>
</div>

<div class="row justify-content-center">
<div class="col-lg-6">

<div class="card shadow-sm">
    <div class="card-header fw-semibold">
        <i class="bi bi-exclamation-triangle me-2 text-danger"></i>Déclarer un sinistre
    </div>
    <div class="card-body p-4">

        <p class="text-muted small mb-4">
            Sélectionnez le contrat concerné. Vous serez redirigé vers le formulaire de déclaration
            en ligne, pré-rempli avec les informations de votre contrat.
        </p>

        <form method="get" action="/claims/declare" novalidate>

            <div class="mb-4">
                <label for="contractSel" class="form-label fw-semibold">
                    Contrat concerné <span class="text-danger">*</span>
                </label>
                <?php if (empty($contracts)): ?>
                    <div class="alert alert-warning small">
                        <i class="bi bi-exclamation-circle me-1"></i>
                        Vous n'avez aucun contrat enregistré. Contactez TILKI pour plus d'informations.
                    </div>
                <?php else: ?>
                <select name="contract_id" id="contractSel" class="form-select" required>
                    <option value="">— Sélectionner un contrat —</option>
                    <?php foreach ($contracts as $c): ?>
                    <option value="<?= (int)$c['id'] ?>">
                        <?= htmlspecialchars($c['policy_number'] . ' — ' . $c['branche'] . ' (' . $c['insurer'] . ')') ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <?php endif; ?>
            </div>

            <?php if (!empty($contracts)): ?>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-box-arrow-up-right me-2"></i>Accéder au formulaire
                </button>
                <a href="/claims" class="btn btn-outline-secondary">Annuler</a>
            </div>
            <?php endif; ?>

        </form>

    </div>
</div>

</div>
</div>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
