<?php
$pageTitle = 'Modifier véhicule – Administration TILKI';
function vVeh(string $key, array $old, ?array $v, mixed $default = ''): mixed {
    return $old[$key] ?? $v[$key] ?? $default;
}
?>
<?php require APP_PATH . '/Views/admin/layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h5 fw-bold mb-0">
            <i class="bi bi-car-front me-2"></i>Modifier le véhicule
        </h2>
        <?php if ($contract): ?>
        <small class="text-muted">
            Contrat <a href="/admin/contracts/<?= (int)$contract['id'] ?>/edit"
                       class="text-decoration-none"><?= htmlspecialchars($contract['policy_number']) ?></a>
            — <?= htmlspecialchars($contract['branche']) ?>
        </small>
        <?php endif; ?>
    </div>
    <a href="/admin/contracts/<?= (int)$vehicle['contract_id'] ?>/edit"
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

                <form method="post" action="/admin/vehicles/<?= (int)$vehicle['id'] ?>/edit" novalidate>
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">
                                Immatriculation <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="immatriculation" class="form-control font-monospace text-uppercase"
                                   value="<?= htmlspecialchars((string)vVeh('immatriculation', $old, $vehicle)) ?>"
                                   required autofocus placeholder="Ex : AB 123 CI">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">
                                Marque <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="marque" class="form-control"
                                   value="<?= htmlspecialchars((string)vVeh('marque', $old, $vehicle)) ?>"
                                   required placeholder="Ex : Toyota">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Modèle</label>
                            <input type="text" name="modele" class="form-control"
                                   value="<?= htmlspecialchars((string)vVeh('modele', $old, $vehicle)) ?>"
                                   placeholder="Ex : Corolla">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Année</label>
                            <input type="number" name="annee" class="form-control"
                                   min="1970" max="<?= date('Y') + 1 ?>"
                                   value="<?= htmlspecialchars((string)vVeh('annee', $old, $vehicle)) ?>"
                                   placeholder="<?= date('Y') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Énergie</label>
                            <select name="energie" class="form-select">
                                <option value="">—</option>
                                <?php foreach (\App\Models\Vehicle::ENERGIES as $e): ?>
                                <option value="<?= $e ?>"
                                    <?= vVeh('energie', $old, $vehicle) === $e ? 'selected' : '' ?>>
                                    <?= ucfirst($e) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Usage</label>
                            <select name="usage" class="form-select">
                                <?php foreach (\App\Models\Vehicle::USAGES as $u): ?>
                                <option value="<?= $u ?>"
                                    <?= vVeh('usage', $old, $vehicle, 'personnel') === $u ? 'selected' : '' ?>>
                                    <?= ucfirst($u) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Valeur vénale (XOF)</label>
                            <input type="number" name="valeur_venale" class="form-control"
                                   min="0" step="1000"
                                   value="<?= htmlspecialchars((string)vVeh('valeur_venale', $old, $vehicle)) ?>"
                                   placeholder="Ex : 5000000">
                        </div>
                    </div>

                    <hr class="my-4">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-floppy me-2"></i>Enregistrer
                        </button>
                        <a href="/admin/contracts/<?= (int)$vehicle['contract_id'] ?>/edit"
                           class="btn btn-outline-secondary">Annuler</a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<?php require APP_PATH . '/Views/admin/layout/footer.php'; ?>
