<?php $pageTitle = 'Code PIN – TILKI'; ?>
<?php require APP_PATH . '/Views/layout/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-7 col-lg-5">
        <?php if (!empty($_SESSION['must_change_password'])): ?>
        <div class="alert alert-warning">
            <i class="bi bi-shield-lock me-2"></i>
            Vous devez définir votre code PIN avant de continuer.
        </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-header fw-bold">
                <i class="bi bi-key me-2"></i>
                <?= !empty($_SESSION['must_change_password']) ? 'Définir votre code PIN' : 'Modifier votre code PIN' ?>
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
                        <label class="form-label small fw-semibold">Code PIN actuel</label>
                        <input type="password" name="current_password" class="form-control"
                               inputmode="numeric" autocomplete="current-password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nouveau code PIN</label>
                        <input type="password" name="new_password" class="form-control"
                               inputmode="numeric" pattern="[0-9]{4,8}"
                               minlength="4" maxlength="8"
                               autocomplete="new-password" required>
                        <div class="form-text">Entre 4 et 8 chiffres uniquement.</div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-semibold">Confirmer le code PIN</label>
                        <input type="password" name="confirm_password" class="form-control"
                               inputmode="numeric" pattern="[0-9]{4,8}"
                               minlength="4" maxlength="8"
                               autocomplete="new-password" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check2-circle me-2"></i>Enregistrer le code PIN
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
