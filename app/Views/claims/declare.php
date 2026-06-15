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
            Sélectionnez le contrat concerné. Notre équipe prendra en charge votre déclaration
            et vous contactera dans les meilleurs délais.
        </p>

        <?php if (!empty($error)): ?>
        <div class="alert alert-danger small">
            <i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="post" action="/claims/declare" novalidate>
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

            <div class="mb-4">
                <label for="contractSel" class="form-label fw-semibold">
                    Contrat concerné <span class="text-danger">*</span>
                </label>
                <select name="contract_id" id="contractSel" class="form-select" required>
                    <option value="">— Sélectionner un contrat —</option>
                    <?php foreach ($contracts as $c): ?>
                    <option value="<?= (int)$c['id'] ?>"
                        <?= (int)($old['contract_id'] ?? 0) === (int)$c['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['policy_number'] . ' — ' . $c['branche'] . ' (' . $c['insurer'] . ')') ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-send me-2"></i>Envoyer la déclaration
                </button>
                <a href="/claims" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>

    </div>
</div>

</div>
</div>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
