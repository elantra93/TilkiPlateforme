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
    <div class="d-flex gap-2">
        <?php if ($isEdit): ?>
        <a href="/admin/payments/create?client_id=<?= (int)$contract['client_id'] ?>&contract_id=<?= (int)$contract['id'] ?>"
           class="btn btn-sm btn-outline-primary">
            <i class="bi bi-cash-coin me-1"></i>Enregistrer un paiement
        </a>
        <?php endif; ?>
        <a href="/admin/contracts" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
    </div>
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
            <?php if ($contract): ?>
                <?php $currency = htmlspecialchars((string)v('currency', $old, $contract, 'XOF')); ?>
                <?php if (($premiumDue ?? 0) <= 0): ?>
                <div class="form-control-plaintext">
                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                        <i class="bi bi-check2 me-1"></i>À jour
                    </span>
                    <div class="form-text">Calculé automatiquement</div>
                </div>
                <?php else: ?>
                <div class="form-control-plaintext fw-semibold text-danger">
                    <?= number_format((float)($premiumDue ?? 0), 0, ',', ' ') ?> <?= $currency ?>
                    <div class="form-text text-muted">prime totale – paiements validés</div>
                </div>
                <?php endif; ?>
            <?php else: ?>
            <div class="form-control-plaintext text-muted fst-italic small">
                Calculé après création
            </div>
            <?php endif; ?>
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

<?php if ($isEdit): ?>
<!-- ── Documents du contrat ──────────────────────────────────────────────────── -->
<div class="row justify-content-center mt-4">
<div class="col-xl-8">
<div class="card shadow-sm">
    <div class="card-header fw-semibold">
        <i class="bi bi-paperclip me-2 text-secondary"></i>Documents du contrat
    </div>
    <div class="card-body p-0">

        <!-- Formulaire d'ajout -->
        <div class="p-3 border-bottom bg-light">
            <p class="small fw-semibold mb-2"><i class="bi bi-upload me-1"></i>Ajouter un document</p>
            <form method="post"
                  action="/admin/contracts/<?= (int)$contract['id'] ?>/upload"
                  enctype="multipart/form-data"
                  class="row g-2 align-items-end" id="contractDocForm">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
                <div class="col-md-3">
                    <label class="form-label small mb-1">Catégorie</label>
                    <select name="category" id="contractCatSel" class="form-select form-select-sm" required>
                        <option value="">— Choisir —</option>
                        <option value="cotation">Cotation</option>
                        <option value="souscription">Souscription</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-1">Type de document</label>
                    <select name="doc_type" id="contractDocTypeSel" class="form-select form-select-sm" required>
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

        <!-- Documents existants -->
        <?php if (empty($documents)): ?>
            <p class="text-muted small p-3 mb-0"><i class="bi bi-dash me-1 opacity-50"></i>Aucun document pour ce contrat.</p>
        <?php else: ?>
        <?php
        $catLabels = ['cotation' => 'Cotation', 'souscription' => 'Souscription'];
        $docsByCategory = ['cotation' => [], 'souscription' => []];
        foreach ($documents as $doc) {
            $docsByCategory[$doc['category']][] = $doc;
        }
        foreach ($docsByCategory as $cat => $docs):
            if (empty($docs)) continue;
        ?>
        <div class="border-bottom px-3 pt-2 pb-1">
            <p class="small fw-semibold text-muted mb-1"><?= htmlspecialchars($catLabels[$cat] ?? $cat) ?></p>
            <?php foreach ($docs as $doc): ?>
            <div class="d-flex align-items-center gap-2 py-1">
                <i class="bi bi-<?= str_starts_with($doc['mime_type'], 'image/') ? 'file-earmark-image text-info' : 'file-earmark-text text-secondary' ?> flex-shrink-0"></i>
                <span class="small flex-grow-1 text-truncate"><?= htmlspecialchars($doc['original_filename']) ?></span>
                <span class="small text-muted"><?= date('d/m/Y', strtotime($doc['created_at'])) ?></span>
                <a href="/documents/<?= (int)$doc['id'] ?>/download" class="btn btn-sm btn-outline-secondary" target="_blank">
                    <i class="bi bi-download"></i>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
</div>
</div>

<script>
(function () {
    const contractDocTypes = <?= json_encode($contractDocTypes ?? [], JSON_HEX_TAG) ?>;
    const docTypeLabels = {
        'questionnaire': 'Questionnaire', 'cotation': 'Cotation', 'bordereau': 'Bordereau',
        'note_de_couverture': 'Note de couverture',
        'conditions_particulieres': 'Conditions particulières', 'attestation_assurance': "Attestation d'assurance",
        'attestation_cedeao': 'Attestation CEDEAO', 'conditions_generales': 'Conditions générales',
        'contrat': 'Contrat', 'avenant': 'Avenant', 'preuve_paiement': 'Preuve de paiement',
        'quittance': 'Quittance', 'attestation': 'Attestation', 'decompte': 'Décompte',
        'tableau_garanties': 'Tableau de garanties', 'reseau_soins': 'Réseau de soins',
    };
    const catSel  = document.getElementById('contractCatSel');
    const typeSel = document.getElementById('contractDocTypeSel');

    catSel.addEventListener('change', function () {
        const types = contractDocTypes[this.value] || [];
        typeSel.innerHTML = '<option value="">— Sélectionner —</option>';
        types.forEach(t => {
            const opt = new Option(docTypeLabels[t] ?? t.replace(/_/g, ' '), t);
            typeSel.add(opt);
        });
    });
})();
</script>
<?php endif; ?>

<?php require APP_PATH . '/Views/admin/layout/footer.php'; ?>
