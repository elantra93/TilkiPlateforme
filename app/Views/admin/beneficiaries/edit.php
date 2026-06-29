<?php
$pageTitle = 'Modifier bénéficiaire – Administration TILKI';
function vBen(string $key, array $old, ?array $b, mixed $default = ''): mixed {
    return $old[$key] ?? $b[$key] ?? $default;
}
?>
<?php require APP_PATH . '/Views/admin/layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h5 fw-bold mb-0">
            <i class="bi bi-person-heart me-2"></i>Modifier le bénéficiaire
        </h2>
        <?php if ($contract): ?>
        <small class="text-muted">
            Contrat <a href="/admin/contracts/<?= (int)$contract['id'] ?>/edit"
                       class="text-decoration-none"><?= htmlspecialchars($contract['policy_number']) ?></a>
            — <?= htmlspecialchars($contract['branche']) ?>
        </small>
        <?php endif; ?>
    </div>
    <a href="/admin/contracts/<?= (int)$beneficiary['contract_id'] ?>/edit"
       class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Retour au contrat
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-body p-4">

                <?php if (!empty($error)): ?>
                <div class="alert alert-danger small">
                    <i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($error) ?>
                </div>
                <?php endif; ?>

                <form method="post" action="/admin/beneficiaries/<?= (int)$beneficiary['id'] ?>/edit" novalidate>
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">
                                Nom <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="last_name" class="form-control text-uppercase"
                                   value="<?= htmlspecialchars((string)vBen('last_name', $old, $beneficiary)) ?>"
                                   required autofocus placeholder="NOM DE FAMILLE">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">
                                Prénom <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="first_name" class="form-control"
                                   value="<?= htmlspecialchars((string)vBen('first_name', $old, $beneficiary)) ?>"
                                   required placeholder="Prénom">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Date de naissance</label>
                            <input type="date" name="birth_date" class="form-control"
                                   value="<?= htmlspecialchars((string)vBen('birth_date', $old, $beneficiary)) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Genre</label>
                            <select name="gender" class="form-select">
                                <option value="">—</option>
                                <?php foreach (\App\Models\Beneficiary::GENDERS as $code => $label): ?>
                                <option value="<?= $code ?>"
                                    <?= vBen('gender', $old, $beneficiary) === $code ? 'selected' : '' ?>>
                                    <?= $label ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Lien de parenté</label>
                            <select name="relation" class="form-select">
                                <?php foreach (\App\Models\Beneficiary::RELATIONS as $rel): ?>
                                <option value="<?= $rel ?>"
                                    <?= vBen('relation', $old, $beneficiary, 'autre') === $rel ? 'selected' : '' ?>>
                                    <?= ucfirst($rel) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">
                                Matricule / N° adhérent
                            </label>
                            <input type="text" name="matricule" class="form-control font-monospace"
                                   value="<?= htmlspecialchars((string)vBen('matricule', $old, $beneficiary)) ?>"
                                   placeholder="Optionnel">
                        </div>
                        <div class="col-md-6 d-flex align-items-end pb-1">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_principal"
                                       id="isPrincipal" role="switch"
                                       <?= (int)vBen('is_principal', $old, $beneficiary, 0) ? 'checked' : '' ?>>
                                <label class="form-check-label small fw-semibold" for="isPrincipal">
                                    Souscripteur principal
                                </label>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-floppy me-2"></i>Enregistrer
                        </button>
                        <a href="/admin/contracts/<?= (int)$beneficiary['contract_id'] ?>/edit"
                           class="btn btn-outline-secondary">Annuler</a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<?php require APP_PATH . '/Views/admin/layout/footer.php'; ?>
