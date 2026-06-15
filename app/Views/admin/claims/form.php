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

<div class="row justify-content-center g-4">
<div class="col-xl-8">

<!-- ── Formulaire principal ──────────────────────────────────────────────────── -->
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

        <!-- Responsabilité civile auto -->
        <div class="col-md-6 d-flex align-items-end">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch"
                       name="is_auto_rc" id="isAutoRc"
                       <?= (bool)(int)v('is_auto_rc', $old, $claim, 0) ? 'checked' : '' ?>>
                <label class="form-check-label fw-semibold small" for="isAutoRc">
                    Sinistre automobile en responsabilité civile (RC)
                </label>
            </div>
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

<?php if ($isEdit): ?>
<!-- ── Documents du sinistre ─────────────────────────────────────────────────── -->
<?php
$catMeta = [
    'declaration'               => ['label' => 'Déclaration',                     'icon' => 'bi-clipboard-data',     'color' => 'text-info'],
    'expertise_devis'           => ['label' => "Rapports d'expertises et devis",  'icon' => 'bi-file-earmark-check', 'color' => 'text-success'],
    'correspondances'           => ['label' => 'Correspondances',                  'icon' => 'bi-envelope-paper',     'color' => 'text-warning'],
    'reglements_remboursements' => ['label' => 'Règlements et remboursements',     'icon' => 'bi-cash-coin',          'color' => 'text-primary'],
];
$docsByCategory = array_fill_keys(array_keys($catMeta), []);
foreach ($documents ?? [] as $doc) {
    if (isset($docsByCategory[$doc['category']])) {
        $docsByCategory[$doc['category']][] = $doc;
    }
}
?>
<div class="card shadow-sm">
    <div class="card-header fw-semibold">
        <i class="bi bi-paperclip me-2 text-secondary"></i>Documents du sinistre
    </div>
    <div class="card-body p-0">

        <!-- Formulaire d'ajout -->
        <div class="p-3 border-bottom bg-light">
            <p class="small fw-semibold mb-2"><i class="bi bi-upload me-1"></i>Ajouter un document</p>
            <form method="post" action="/admin/claims/<?= (int)$claim['id'] ?>/upload"
                  enctype="multipart/form-data" class="row g-2 align-items-end" id="claimDocForm">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
                <div class="col-md-3">
                    <label class="form-label small mb-1">Catégorie</label>
                    <select name="category" id="claimCatSel" class="form-select form-select-sm" required>
                        <option value="">— Choisir —</option>
                        <?php foreach ($catMeta as $catKey => $catInfo): ?>
                        <option value="<?= $catKey ?>"><?= htmlspecialchars($catInfo['label']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-1">Type de document</label>
                    <select name="doc_type" id="claimDocTypeSel" class="form-select form-select-sm" required disabled>
                        <option value="">— Choisir une catégorie —</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small mb-1">Fichier <span class="text-muted fw-normal">(PDF, image, Word, Excel – max 10 Mo)</span></label>
                    <input type="file" name="document" class="form-control form-control-sm"
                           accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-upload me-1"></i>Envoyer
                    </button>
                </div>
            </form>
        </div>

        <!-- Documents existants par catégorie -->
        <?php foreach ($catMeta as $catKey => $catInfo): ?>
        <?php $docs = $docsByCategory[$catKey]; ?>
        <div class="border-bottom">
            <div class="px-3 py-2 d-flex align-items-center gap-2 bg-white">
                <i class="bi <?= $catInfo['icon'] ?> <?= $catInfo['color'] ?> small"></i>
                <span class="small fw-semibold"><?= htmlspecialchars($catInfo['label']) ?></span>
                <span class="badge bg-secondary fw-normal ms-1" style="font-size:.7rem"><?= count($docs) ?></span>
            </div>
            <?php if (empty($docs)): ?>
            <div class="px-3 py-2 text-muted small fst-italic">Aucun document.</div>
            <?php else: ?>
            <ul class="list-group list-group-flush">
                <?php foreach ($docs as $doc): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center py-2 ps-4">
                    <div>
                        <span class="small fw-semibold"><?= htmlspecialchars($doc['original_filename']) ?></span>
                        <span class="text-muted ms-2" style="font-size:.72rem">
                            <?= htmlspecialchars(str_replace('_', ' ', $doc['doc_type'])) ?>
                            &bull; <?= number_format($doc['file_size'] / 1024, 0) ?> Ko
                            &bull; <?= date('d/m/Y', strtotime($doc['created_at'])) ?>
                        </span>
                    </div>
                    <span class="badge bg-<?= $doc['status'] === 'valide' ? 'success' : 'warning text-dark' ?> ms-2">
                        <?= $doc['status'] === 'valide' ? 'Valide' : 'En attente' ?>
                    </span>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>

    </div>
</div>
<?php endif; ?>

<?php if ($isEdit && !empty($steps)): ?>
<!-- ── Suivi d'avancement ─────────────────────────────────────────────────────── -->
<div class="card shadow-sm">
    <div class="card-header fw-semibold">
        <i class="bi bi-list-check me-2 text-primary"></i>Suivi d'avancement du sinistre
    </div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th class="ps-3" style="width:2rem">#</th>
                    <th>Étape</th>
                    <th style="width:7rem">Réalisée</th>
                    <th style="width:10rem">Date</th>
                    <th style="width:7rem"></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($steps as $step): ?>
            <tr class="<?= $step['completed'] ? 'table-success' : '' ?>">
                <td class="ps-3 text-muted small"><?= (int)$step['position'] ?></td>
                <td class="fw-semibold small"><?= htmlspecialchars($step['label']) ?></td>
                <form method="post"
                      action="/admin/claims/<?= (int)$claim['id'] ?>/steps/<?= (int)$step['id'] ?>"
                      class="d-contents">
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
                    <td>
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" name="completed"
                                   id="step_<?= (int)$step['id'] ?>"
                                   <?= $step['completed'] ? 'checked' : '' ?>
                                   onchange="this.closest('form').submit()">
                        </div>
                    </td>
                    <td>
                        <input type="date" name="completed_date" class="form-control form-control-sm"
                               value="<?= htmlspecialchars($step['completed_date'] ?? '') ?>"
                               <?= !$step['completed'] ? 'disabled' : '' ?>>
                    </td>
                    <td>
                        <button type="submit" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-save me-1"></i>Sauver
                        </button>
                    </td>
                </form>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

</div><!-- /col -->
</div><!-- /row -->

<script>
// Cascade catégorie → type de document (section upload sinistre)
const claimDocTypes = <?= json_encode($docTypes ?? [], JSON_HEX_TAG) ?>;
document.getElementById('claimCatSel')?.addEventListener('change', function () {
    const sel = document.getElementById('claimDocTypeSel');
    const types = claimDocTypes[this.value] || [];
    sel.innerHTML = '<option value="">— Sélectionner —</option>';
    types.forEach(t => {
        sel.innerHTML += `<option value="${t}">${t.replace(/_/g, ' ')}</option>`;
    });
    sel.disabled = types.length === 0;
});

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

// Enable/disable date inputs when checkbox changes
document.querySelectorAll('input[type="checkbox"][name="completed"]').forEach(cb => {
    cb.addEventListener('change', function () {
        const dateInput = this.closest('tr').querySelector('input[type="date"]');
        if (dateInput) dateInput.disabled = !this.checked;
    });
});
</script>

<?php require APP_PATH . '/Views/admin/layout/footer.php'; ?>
