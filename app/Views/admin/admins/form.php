<?php
$isEdit    = $admin !== null;
$pageTitle = ($isEdit ? 'Modifier le compte' : 'Nouveau compte admin') . ' – TILKI';
$action    = $isEdit
    ? '/admin/admins/' . (int)$admin['id'] . '/edit'
    : '/admin/admins/create';

function vAdm(string $key, array $old, ?array $admin, mixed $default = ''): mixed {
    return $old[$key] ?? $admin[$key] ?? $default;
}
?>
<?php require APP_PATH . '/Views/admin/layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h5 fw-bold mb-0">
        <i class="bi bi-<?= $isEdit ? 'pencil' : 'person-plus' ?> me-2"></i>
        <?= $isEdit ? 'Modifier le compte' : 'Nouveau compte administrateur' ?>
    </h2>
    <a href="/admin/admins" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Retour
    </a>
</div>

<div class="row justify-content-center">
<div class="col-lg-6">
<div class="card shadow-sm">
<div class="card-body p-4">

<?php if (!empty($error)): ?>
<div class="alert alert-danger small">
    <i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<form method="post" action="<?= $action ?>" novalidate>
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

    <div class="row g-3">

        <div class="col-12">
            <label class="form-label small fw-semibold">Nom complet <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control"
                   value="<?= htmlspecialchars((string)vAdm('name', $old, $admin)) ?>"
                   required autofocus>
        </div>

        <div class="col-12">
            <label class="form-label small fw-semibold">Adresse e-mail <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control"
                   value="<?= htmlspecialchars((string)vAdm('email', $old, $admin)) ?>"
                   required autocomplete="username">
        </div>

        <div class="col-12">
            <label class="form-label small fw-semibold">Rôle <span class="text-danger">*</span></label>
            <select name="role" class="form-select" required>
                <?php foreach ($roles as $key => $label): ?>
                <option value="<?= $key ?>"
                    <?= vAdm('role', $old, $admin, 'admin') === $key ? 'selected' : '' ?>>
                    <?= htmlspecialchars($label) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <div class="form-text">
                <strong>Admin</strong> : accès complet à l'administration.
                <strong>Support</strong> : accès en lecture / gestion des dossiers.
            </div>
        </div>

        <?php if (!$isEdit): ?>
        <div class="col-md-6">
            <label class="form-label small fw-semibold">Mot de passe <span class="text-danger">*</span></label>
            <input type="password" name="password" class="form-control"
                   minlength="8" required autocomplete="new-password">
            <div class="form-text">8 caractères minimum.</div>
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold">Confirmer <span class="text-danger">*</span></label>
            <input type="password" name="confirm_password" class="form-control"
                   minlength="8" required autocomplete="new-password">
        </div>
        <?php else: ?>
        <div class="col-12">
            <hr class="my-2">
            <p class="small text-muted mb-2">
                <i class="bi bi-key me-1"></i>
                Laisser vide pour conserver le mot de passe actuel.
            </p>
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold">Nouveau mot de passe</label>
            <input type="password" name="new_password" class="form-control"
                   minlength="8" autocomplete="new-password">
            <div class="form-text">8 caractères minimum.</div>
        </div>
        <?php endif; ?>

    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check2-circle me-2"></i>
            <?= $isEdit ? 'Enregistrer les modifications' : 'Créer le compte' ?>
        </button>
    </div>
</form>

</div>
</div>
</div>
</div>

<?php require APP_PATH . '/Views/admin/layout/footer.php'; ?>
