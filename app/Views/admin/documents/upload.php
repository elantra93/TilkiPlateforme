<?php $pageTitle = 'Upload document – Administration TILKI'; ?>
<?php require APP_PATH . '/Views/admin/layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h5 fw-bold mb-0"><i class="bi bi-upload me-2"></i>Upload d'un document</h2>
    <a href="/admin/documents/pending" class="btn btn-sm btn-outline-warning">
        <i class="bi bi-hourglass-split me-1"></i>Documents en attente
    </a>
</div>

<div class="row justify-content-center">
<div class="col-xl-7">
<div class="card shadow-sm">
<div class="card-body p-4">

<form method="post" action="/admin/documents/upload" enctype="multipart/form-data" novalidate id="uploadForm">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

    <div class="row g-4">

        <!-- 1. Client -->
        <div class="col-12">
            <label class="form-label fw-semibold">
                <span class="badge bg-primary me-1">1</span> Client
            </label>
            <select name="client_id" id="clientSel" class="form-select" required>
                <option value="">— Sélectionner un client —</option>
                <?php foreach ($clients as $c): ?>
                <option value="<?= (int)$c['id'] ?>">
                    <?= htmlspecialchars($c['account_number'] . ' — ' . $c['first_name'] . ' ' . $c['last_name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- 2. Portée -->
        <div class="col-12" id="scopeRow" style="display:none">
            <label class="form-label fw-semibold">
                <span class="badge bg-primary me-1">2</span> Portée du document
            </label>
            <div class="d-flex gap-4">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="scope" id="scopeContrat" value="contrat">
                    <label class="form-check-label" for="scopeContrat">
                        <i class="bi bi-file-earmark-text me-1"></i>Contrat
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="scope" id="scopeSinistre" value="sinistre">
                    <label class="form-check-label" for="scopeSinistre">
                        <i class="bi bi-exclamation-triangle me-1"></i>Sinistre
                    </label>
                </div>
            </div>
        </div>

        <!-- 3a. Contrat -->
        <div class="col-12" id="contractRow" style="display:none">
            <label class="form-label fw-semibold">
                <span class="badge bg-primary me-1">3</span> Contrat
            </label>
            <select name="contract_id" id="contractSel" class="form-select">
                <option value="">— Sélectionner un contrat —</option>
            </select>
        </div>

        <!-- 3b. Sinistre -->
        <div class="col-12" id="claimRow" style="display:none">
            <label class="form-label fw-semibold">
                <span class="badge bg-primary me-1">3</span> Sinistre
            </label>
            <select name="claim_id" id="claimSel" class="form-select">
                <option value="">— Sélectionner un sinistre —</option>
            </select>
        </div>

        <!-- 4. Famille + Type -->
        <div class="col-md-5" id="categoryRow" style="display:none">
            <label class="form-label fw-semibold">
                <span class="badge bg-primary me-1">4</span> Famille
            </label>
            <select name="category" id="categorySel" class="form-select" required>
                <option value="">— Choisir —</option>
                <option value="cotation">Cotation</option>
                <option value="souscription">Souscription</option>
            </select>
        </div>

        <div class="col-md-7" id="docTypeRow" style="display:none">
            <label class="form-label fw-semibold">
                <span class="badge bg-primary me-1">5</span> Type de document
            </label>
            <select name="doc_type" id="docTypeSel" class="form-select" required>
                <option value="">— Choisir une famille d'abord —</option>
            </select>
        </div>

        <!-- 6. Fichier -->
        <div class="col-12" id="fileRow" style="display:none">
            <label class="form-label fw-semibold">
                <span class="badge bg-primary me-1">6</span> Fichier
                <span class="text-muted fw-normal small">(PDF, JPG, PNG, Word, Excel – max 10 Mo)</span>
            </label>
            <input type="file" name="document" id="fileInput" class="form-control"
                   accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx" required>
        </div>

        <div class="col-12" id="submitRow" style="display:none">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-upload me-2"></i>Envoyer le document
            </button>
        </div>

    </div>
</form>
</div>
</div>
</div>
</div>

<script>
const contractsByClient = <?= json_encode($contractsByClient, JSON_HEX_TAG) ?>;
const claimsByClient    = <?= json_encode($claimsByClient,    JSON_HEX_TAG) ?>;
const docTypes          = <?= json_encode($docTypes,          JSON_HEX_TAG) ?>;

const clientSel   = document.getElementById('clientSel');
const contractSel = document.getElementById('contractSel');
const claimSel    = document.getElementById('claimSel');
const categorySel = document.getElementById('categorySel');
const docTypeSel  = document.getElementById('docTypeSel');

function show(id)  { document.getElementById(id).style.display = ''; }
function hide(id)  { document.getElementById(id).style.display = 'none'; }

function resetFrom(step) {
    if (step <= 2) { hide('scopeRow');    document.querySelectorAll('input[name=scope]').forEach(r => r.checked = false); }
    if (step <= 3) { hide('contractRow'); hide('claimRow'); contractSel.innerHTML = '<option value="">—</option>'; claimSel.innerHTML = '<option value="">—</option>'; }
    if (step <= 4) { hide('categoryRow'); hide('docTypeRow'); categorySel.value = ''; docTypeSel.innerHTML = '<option value="">—</option>'; }
    if (step <= 5) { hide('fileRow'); hide('submitRow'); }
}

clientSel.addEventListener('change', function () {
    resetFrom(2);
    if (!this.value) return;
    // Populate contract select
    contractSel.innerHTML = '<option value="">— Sélectionner —</option>';
    (contractsByClient[this.value] || []).forEach(c => {
        contractSel.innerHTML += `<option value="${c.id}">${c.label}</option>`;
    });
    // Populate claim select
    claimSel.innerHTML = '<option value="">— Sélectionner —</option>';
    (claimsByClient[this.value] || []).forEach(c => {
        claimSel.innerHTML += `<option value="${c.id}">${c.label}</option>`;
    });
    show('scopeRow');
});

document.querySelectorAll('input[name=scope]').forEach(radio => {
    radio.addEventListener('change', function () {
        resetFrom(3);
        if (this.value === 'contrat') { show('contractRow'); hide('claimRow'); }
        else                          { show('claimRow');    hide('contractRow'); }
        show('categoryRow');
    });
});

[contractSel, claimSel].forEach(sel => {
    sel.addEventListener('change', function () {
        resetFrom(4);
        if (!this.value) return;
        show('categoryRow');
    });
});

categorySel.addEventListener('change', function () {
    resetFrom(5);
    if (!this.value) return;
    docTypeSel.innerHTML = '<option value="">— Sélectionner —</option>';
    (docTypes[this.value] || []).forEach(t => {
        docTypeSel.innerHTML += `<option value="${t}">${t.replace(/_/g,' ')}</option>`;
    });
    show('docTypeRow');
    show('fileRow');
    show('submitRow');
});
</script>

<?php require APP_PATH . '/Views/admin/layout/footer.php'; ?>
