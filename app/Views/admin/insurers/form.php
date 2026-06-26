<?php
$isEdit    = $insurer !== null;
$pageTitle = ($isEdit ? 'Modifier assureur' : 'Nouvel assureur') . ' – Administration TILKI';

function vIns(string $key, array $old, ?array $ins, mixed $default = ''): mixed {
    return $old[$key] ?? $ins[$key] ?? $default;
}
?>
<?php require APP_PATH . '/Views/admin/layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h5 fw-bold mb-0">
        <i class="bi bi-<?= $isEdit ? 'pencil' : 'plus-lg' ?> me-2"></i>
        <?= $isEdit ? 'Modifier l\'assureur' : 'Nouvel assureur' ?>
    </h2>
    <a href="/admin/insurers" class="btn btn-sm btn-outline-secondary">
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

                <form method="post"
                      action="<?= $isEdit ? '/admin/insurers/' . (int)$insurer['id'] . '/edit' : '/admin/insurers/create' ?>"
                      novalidate>
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-semibold">
                                Dénomination complète <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" class="form-control"
                                   value="<?= htmlspecialchars((string)vIns('name', $old, $insurer)) ?>"
                                   required autofocus
                                   placeholder="Ex : SUNU Assurances IARD Côte d'Ivoire">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Sigle / nom court</label>
                            <input type="text" name="short_name" class="form-control font-monospace"
                                   value="<?= htmlspecialchars((string)vIns('short_name', $old, $insurer)) ?>"
                                   placeholder="Ex : SUNU IARD CI">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Pays</label>
                            <input type="text" name="country" class="form-control"
                                   value="<?= htmlspecialchars((string)vIns('country', $old, $insurer, "Côte d'Ivoire")) ?>">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active"
                                       id="isActiveSwitch" role="switch"
                                       <?= (int)vIns('is_active', $old, $insurer, 1) ? 'checked' : '' ?>>
                                <label class="form-check-label small fw-semibold" for="isActiveSwitch">
                                    Actif (visible dans les formulaires de contrats)
                                </label>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-<?= $isEdit ? 'floppy' : 'plus-lg' ?> me-2"></i>
                            <?= $isEdit ? 'Enregistrer' : 'Créer l\'assureur' ?>
                        </button>
                        <a href="/admin/insurers" class="btn btn-outline-secondary">Annuler</a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<?php require APP_PATH . '/Views/admin/layout/footer.php'; ?>
