<?php
$isEdit    = $contract !== null;
$pageTitle = ($isEdit ? 'Modifier contrat' : 'Nouveau contrat') . ' – Administration TILKI';
$action    = $isEdit ? '/admin/contracts/' . (int)$contract['id'] . '/edit' : '/admin/contracts/create';

// Valeurs affichées : old > contract > défauts
function v(string $key, array $old, ?array $contract, mixed $default = ''): mixed {
    return $old[$key] ?? $contract[$key] ?? $default;
}
?>
<?php require APP_PATH . '/Views/admin/layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h5 fw-bold mb-0">
        <i class="bi bi-<?= $isEdit ? 'pencil' : 'plus-lg' ?> me-2"></i>
        <?= $isEdit ? 'Modifier le contrat' : 'Nouveau contrat' ?>
    </h2>
    <a href="/admin/contracts" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Retour
    </a>
</div>

<div class="row justify-content-center">
<div class="col-xl-8">
<div class="card shadow-sm">
<div class="card-body p-4">

<?php if (!empty($error)): ?>
<div class="alert alert-danger small"><i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="post" action="<?= $action ?>" novalidate>
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

    <div class="row g-3">

        <!-- Client (create only) -->
        <?php if (!$isEdit): ?>
        <div class="col-12">
            <label class="form-label small fw-semibold">Client <span class="text-danger">*</span></label>
            <select name="client_id" class="form-select" required>
                <option value="">— Sélectionner un client —</option>
                <?php foreach ($clients as $cl): ?>
                <option value="<?= (int)$cl['id'] ?>"
                    <?= (int)v('client_id', $old, null, 0) === (int)$cl['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cl['account_number'] . ' — ' . $cl['first_name'] . ' ' . $cl['last_name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php else: ?>
        <div class="col-12">
            <label class="form-label small fw-semibold text-muted">Client</label>
            <?php
                $owner = null;
                foreach ($clients as $cl) {
                    if ((int)$cl['id'] === (int)$contract['client_id']) { $owner = $cl; break; }
                }
            ?>
            <div class="form-control-plaintext fw-semibold">
                <?= $owner ? htmlspecialchars($owner['account_number'] . ' — ' . $owner['first_name'] . ' ' . $owner['last_name']) : '—' ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="col-md-6">
            <label class="form-label small fw-semibold">Branche <span class="text-danger">*</span></label>
            <input type="text" name="branche" class="form-control"
                   value="<?= htmlspecialchars((string)v('branche', $old, $contract)) ?>" required>
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold">N° Police <span class="text-danger">*</span></label>
            <input type="text" name="policy_number" class="form-control"
                   value="<?= htmlspecialchars((string)v('policy_number', $old, $contract)) ?>" required>
        </div>
        <div class="col-12">
            <label class="form-label small fw-semibold">Assureur <span class="text-danger">*</span></label>
            <input type="text" name="insurer" class="form-control"
                   value="<?= htmlspecialchars((string)v('insurer', $old, $contract)) ?>" required>
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold">Date d'effet <span class="text-danger">*</span></label>
            <input type="date" name="effective_date" class="form-control"
                   value="<?= htmlspecialchars((string)v('effective_date', $old, $contract)) ?>" required>
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold">Date d'échéance <span class="text-danger">*</span></label>
            <input type="date" name="expiry_date" class="form-control"
                   value="<?= htmlspecialchars((string)v('expiry_date', $old, $contract)) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label small fw-semibold">Prime totale</label>
            <input type="number" name="premium_total" class="form-control" step="0.01" min="0"
                   value="<?= htmlspecialchars((string)v('premium_total', $old, $contract, '0')) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label small fw-semibold">Restant dû</label>
            <input type="number" name="premium_due" class="form-control" step="0.01" min="0"
                   value="<?= htmlspecialchars((string)v('premium_due', $old, $contract, '0')) ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold">Devise</label>
            <input type="text" name="currency" class="form-control" maxlength="3"
                   value="<?= htmlspecialchars((string)v('currency', $old, $contract, 'XOF')) ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold">Statut</label>
            <select name="status" class="form-select">
                <?php foreach (['actif', 'expiré', 'résilié', 'suspendu'] as $s): ?>
                <option value="<?= $s ?>" <?= v('status', $old, $contract, 'actif') === $s ? 'selected' : '' ?>>
                    <?= htmlspecialchars(ucfirst($s)) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <hr class="my-4">
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-<?= $isEdit ? 'save' : 'plus-lg' ?> me-2"></i>
            <?= $isEdit ? 'Enregistrer les modifications' : 'Créer le contrat' ?>
        </button>
        <a href="/admin/contracts" class="btn btn-outline-secondary">Annuler</a>
    </div>
</form>

</div>
</div>
</div>
</div>

<?php require APP_PATH . '/Views/admin/layout/footer.php'; ?>
