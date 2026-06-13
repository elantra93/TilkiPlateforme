<?php
$isEdit    = $claim !== null;
$pageTitle = ($isEdit ? 'Modifier sinistre' : 'Nouveau sinistre') . ' – Administration TILKI';
$action    = $isEdit ? '/admin/claims/' . (int)$claim['id'] . '/edit' : '/admin/claims/create';

function v(string $key, array $old, ?array $row, mixed $default = ''): mixed {
    return $old[$key] ?? $row[$key] ?? $default;
}

// Groupe les contrats par client pour la cascade JS
$contractsByClient = [];
foreach ($contracts as $c) {
    $contractsByClient[$c['client_id']][] = [
        'id'    => $c['id'],
        'label' => $c['policy_number'] . ' — ' . $c['branche'],
    ];
}
?>
<?php require APP_PATH . '/Views/admin/layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h5 fw-bold mb-0">
        <i class="bi bi-<?= $isEdit ? 'pencil' : 'plus-lg' ?> me-2"></i>
        <?= $isEdit ? 'Modifier le sinistre' : 'Nouveau sinistre' ?>
    </h2>
    <a href="/admin/claims" class="btn btn-sm btn-outline-secondary">
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
        <div class="col-md-6">
            <label class="form-label small fw-semibold">Client <span class="text-danger">*</span></label>
            <select name="client_id" id="clientSel" class="form-select" required>
                <option value="">— Sélectionner —</option>
                <?php foreach ($clients as $cl): ?>
                <option value="<?= (int)$cl['id'] ?>"
                    <?= (int)v('client_id', $old, null, 0) === (int)$cl['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cl['account_number'] . ' — ' . $cl['first_name'] . ' ' . $cl['last_name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php else: ?>
        <div class="col-md-6">
            <label class="form-label small fw-semibold text-muted">Client</label>
            <?php
                $owner = null;
                foreach ($clients as $cl) {
                    if ((int)$cl['id'] === (int)$claim['client_id']) { $owner = $cl; break; }
                }
            ?>
            <div class="form-control-plaintext fw-semibold">
                <?= $owner ? htmlspecialchars($owner['account_number'] . ' — ' . $owner['first_name'] . ' ' . $owner['last_name']) : '—' ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Contrat lié (optionnel) -->
        <div class="col-md-6">
            <label class="form-label small fw-semibold">Contrat lié <span class="text-muted fw-normal">(optionnel)</span></label>
            <select name="contract_id" id="contractSel" class="form-select">
                <option value="">— Aucun —</option>
                <?php
                $srcContracts = $isEdit
                    ? ($contractsByClient[$claim['client_id']] ?? [])
                    : ($contractsByClient[(int)v('client_id', $old, null, 0)] ?? []);
                foreach ($srcContracts as $c):
                ?>
                <option value="<?= (int)$c['id'] ?>"
                    <?= (int)v('contract_id', $old, $claim, 0) === (int)$c['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['label']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label small fw-semibold">N° Sinistre <span class="text-danger">*</span></label>
            <input type="text" name="claim_number" class="form-control"
                   value="<?= htmlspecialchars((string)v('claim_number', $old, $claim)) ?>" required>
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold">Assureur <span class="text-danger">*</span></label>
            <input type="text" name="insurer" class="form-control"
                   value="<?= htmlspecialchars((string)v('insurer', $old, $claim)) ?>" required>
        </div>
        <div class="col-md-6">
            <label class="form-label small fw-semibold">Branche <span class="text-danger">*</span></label>
            <input type="text" name="branche" class="form-control"
                   value="<?= htmlspecialchars((string)v('branche', $old, $claim)) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label small fw-semibold">Date de survenance <span class="text-danger">*</span></label>
            <input type="date" name="occurrence_date" class="form-control"
                   value="<?= htmlspecialchars((string)v('occurrence_date', $old, $claim)) ?>" required>
        </div>
        <div class="col-md-2">
            <label class="form-label small fw-semibold">Statut</label>
            <select name="status" class="form-select">
                <?php foreach (['ouvert', 'clos'] as $s): ?>
                <option value="<?= $s ?>" <?= v('status', $old, $claim, 'ouvert') === $s ? 'selected' : '' ?>>
                    <?= ucfirst($s) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12">
            <label class="form-label small fw-semibold">Description</label>
            <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars((string)v('description', $old, $claim)) ?></textarea>
        </div>
    </div>

    <hr class="my-4">
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-<?= $isEdit ? 'save' : 'plus-lg' ?> me-2"></i>
            <?= $isEdit ? 'Enregistrer les modifications' : 'Créer le sinistre' ?>
        </button>
        <a href="/admin/claims" class="btn btn-outline-secondary">Annuler</a>
    </div>
</form>
</div>
</div>
</div>
</div>

<script>
const contractsByClient = <?= json_encode($contractsByClient, JSON_HEX_TAG) ?>;

document.getElementById('clientSel')?.addEventListener('change', function () {
    const sel   = document.getElementById('contractSel');
    const items = contractsByClient[this.value] || [];
    sel.innerHTML = '<option value="">— Aucun —</option>';
    items.forEach(c => {
        const opt = document.createElement('option');
        opt.value       = c.id;
        opt.textContent = c.label;
        sel.appendChild(opt);
    });
});
</script>

<?php require APP_PATH . '/Views/admin/layout/footer.php'; ?>
