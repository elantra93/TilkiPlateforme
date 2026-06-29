<?php $pageTitle = 'Changer mon mot de passe – Administration TILKI'; ?>
<?php require APP_PATH . '/Views/admin/layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h5 fw-bold mb-0">
        <i class="bi bi-key me-2"></i>Changer mon mot de passe
    </h2>
    <a href="/admin/dashboard" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Retour
    </a>
</div>

<div class="row justify-content-center">
<div class="col-xl-5 col-lg-6">
<div class="card shadow-sm">
<div class="card-body p-4">

<?php if (!empty($error)): ?>
<div class="alert alert-danger small">
    <i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<form method="post" action="/admin/password/change" novalidate>
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

    <div class="mb-3">
        <label for="current_password" class="form-label fw-semibold small">
            Mot de passe actuel <span class="text-danger">*</span>
        </label>
        <input type="password" id="current_password" name="current_password"
               class="form-control" autocomplete="current-password" required>
    </div>

    <div class="mb-3">
        <label for="new_password" class="form-label fw-semibold small">
            Nouveau mot de passe <span class="text-danger">*</span>
        </label>
        <input type="password" id="new_password" name="new_password"
               class="form-control" autocomplete="new-password" required minlength="8">
        <div class="form-text">Minimum 8 caractères.</div>
    </div>

    <div class="mb-4">
        <label for="confirm_password" class="form-label fw-semibold small">
            Confirmer le nouveau mot de passe <span class="text-danger">*</span>
        </label>
        <input type="password" id="confirm_password" name="confirm_password"
               class="form-control" autocomplete="new-password" required minlength="8">
    </div>

    <button type="submit" class="btn btn-primary w-100">
        <i class="bi bi-save me-2"></i>Mettre à jour le mot de passe
    </button>
</form>

</div>
</div>
</div>
</div>

<?php require APP_PATH . '/Views/admin/layout/footer.php'; ?>
