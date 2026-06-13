<?php $pageTitle = 'Changer le mot de passe – TILKI'; ?>
<?php require APP_PATH . '/Views/layout/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-7 col-lg-5">
        <?php if (!empty($_SESSION['must_change_password'])): ?>
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-2"></i>
            Vous devez définir un nouveau mot de passe avant de continuer.
        </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-header fw-bold">
                <i class="bi bi-key me-2"></i>Changer le mot de passe
            </div>
            <div class="card-body p-4">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger py-2 small">
                        <i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="/password/change" novalidate>
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Mot de passe actuel</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nouveau mot de passe</label>
                        <input type="password" name="new_password" class="form-control" minlength="8" required>
                        <div class="form-text">Minimum 8 caractères.</div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-semibold">Confirmer le mot de passe</label>
                        <input type="password" name="confirm_password" class="form-control" minlength="8" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check2-circle me-2"></i>Enregistrer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
