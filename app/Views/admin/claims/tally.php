<?php $pageTitle = 'Déclarer un sinistre via Tally – Administration TILKI'; ?>
<?php require APP_PATH . '/Views/admin/layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h5 fw-bold mb-0">
        <i class="bi bi-exclamation-triangle-fill me-2 text-danger"></i>
        Déclarer un sinistre via Tally
    </h2>
    <a href="/admin/claims" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Retour aux sinistres
    </a>
</div>

<?php if (!empty($flash)): ?>
<div class="alert alert-<?= htmlspecialchars($flash['type']) ?> small">
    <i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($flash['msg']) ?>
</div>
<?php endif; ?>

<div class="row justify-content-center">
<div class="col-xl-6">
<div class="card shadow-sm">
<div class="card-body p-4">

<p class="text-muted small mb-4">
    Sélectionnez le client et la police concernés. Vous serez redirigé vers le formulaire Tally
    pré-rempli. Le sinistre est créé automatiquement dès la soumission du formulaire.
</p>

<form method="get" action="/admin/claims/tally-redirect" target="_blank" id="tallyForm">

    <div class="row g-4">

        <!-- Étape 1 : Client -->
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

        <!-- Étape 2 : Police (cascade) -->
        <div class="col-12" id="contractRow" style="display:none">
            <label class="form-label fw-semibold">
                <span class="badge bg-primary me-1">2</span> Police d'assurance
            </label>
            <select name="contract_id" id="contractSel" class="form-select" required>
                <option value="">— Sélectionner une police —</option>
            </select>
        </div>

        <!-- Bouton -->
        <div class="col-12" id="submitRow" style="display:none">
            <button type="submit" class="btn btn-danger">
                <i class="bi bi-box-arrow-up-right me-2"></i>Ouvrir le formulaire Tally
            </button>
            <p class="text-muted small mt-2 mb-0">
                <i class="bi bi-info-circle me-1"></i>
                Le formulaire Tally s'ouvrira dans un nouvel onglet, pré-rempli avec les données du contrat.
            </p>
        </div>

    </div>
</form>

</div>
</div>
</div>
</div>

<div id="claimTallyCtx"
     data-contracts="<?= htmlspecialchars(json_encode($contractsByClient, JSON_HEX_TAG)) ?>"></div>
<script src="/assets/js/claim-tally.js"></script>

<?php require APP_PATH . '/Views/admin/layout/footer.php'; ?>
