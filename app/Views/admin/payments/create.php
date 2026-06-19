<?php $pageTitle = 'Enregistrer un paiement – Administration TILKI'; ?>
<?php require APP_PATH . '/Views/admin/layout/header.php'; ?>

<div class="d-flex align-items-center mb-4 gap-2">
    <a href="/admin/payments" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Retour
    </a>
    <h2 class="h4 fw-bold mb-0"><i class="bi bi-cash-coin me-2"></i>Enregistrer un paiement</h2>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card shadow-sm" style="max-width:680px">
    <div class="card-body p-4">
        <form method="post" action="/admin/payments/create" enctype="multipart/form-data" novalidate>
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

            <!-- Client -->
            <div class="mb-3">
                <label for="clientSel" class="form-label fw-semibold">
                    Client <span class="text-danger">*</span>
                </label>
                <select name="client_id" id="clientSel" class="form-select" required>
                    <option value="">— Sélectionner un client —</option>
                    <?php foreach ($clients as $cl): ?>
                    <option value="<?= (int)$cl['id'] ?>"
                        <?= ((int)($old['clientId'] ?? 0) ?: $preClientId) === (int)$cl['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cl['account_number'] . ' — ' . $cl['first_name'] . ' ' . $cl['last_name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Contrat -->
            <div class="mb-3">
                <label for="contractSel" class="form-label fw-semibold">
                    Contrat <span class="text-danger">*</span>
                </label>
                <select name="contract_id" id="contractSel" class="form-select" required>
                    <option value="">— Sélectionner d'abord un client —</option>
                </select>
            </div>

            <div class="row g-3 mb-3">
                <!-- Montant -->
                <div class="col-md-6">
                    <label for="amountInp" class="form-label fw-semibold">
                        Montant (XOF) <span class="text-danger">*</span>
                    </label>
                    <input type="number" name="amount" id="amountInp" class="form-control"
                           min="1" step="1" required
                           value="<?= htmlspecialchars((string)($old['amount'] ?? '')) ?>">
                </div>
                <!-- Date -->
                <div class="col-md-6">
                    <label for="paidAtInp" class="form-label fw-semibold">
                        Date de paiement <span class="text-danger">*</span>
                    </label>
                    <input type="date" name="paid_at" id="paidAtInp" class="form-control" required
                           value="<?= htmlspecialchars((string)($old['paidAt'] ?? date('Y-m-d'))) ?>">
                </div>
            </div>

            <!-- Mode de paiement -->
            <div class="mb-3">
                <label for="methodSel" class="form-label fw-semibold">
                    Mode de paiement <span class="text-danger">*</span>
                </label>
                <select name="method" id="methodSel" class="form-select" required>
                    <option value="">— Choisir —</option>
                    <?php
                    $methodLabels = ['cheque'=>'Chèque','virement'=>'Virement bancaire','caisse'=>'Caisse','mobile_money'=>'Mobile Money'];
                    foreach ($methods as $m): ?>
                    <option value="<?= $m ?>"
                        <?= ($old['method'] ?? '') === $m ? 'selected' : '' ?>>
                        <?= $methodLabels[$m] ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Référence -->
            <div class="mb-3">
                <label for="refInp" class="form-label fw-semibold">Référence</label>
                <input type="text" name="reference" id="refInp" class="form-control"
                       maxlength="100" placeholder="N° chèque, reçu, transaction…"
                       value="<?= htmlspecialchars((string)($old['reference'] ?? '')) ?>">
            </div>

            <!-- Preuve de paiement -->
            <div class="mb-3">
                <label for="proofFile" class="form-label fw-semibold">Preuve de paiement</label>
                <input type="file" name="proof" id="proofFile" class="form-control"
                       accept=".pdf,.jpg,.jpeg,.png">
                <div class="form-text">PDF, JPG ou PNG — 10 Mo max. Optionnel.</div>
            </div>

            <!-- Note -->
            <div class="mb-4">
                <label for="noteInp" class="form-label fw-semibold">Note interne</label>
                <textarea name="note" id="noteInp" class="form-control" rows="3"
                          maxlength="1000"><?= htmlspecialchars((string)($old['note'] ?? '')) ?></textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i>Enregistrer le paiement
                </button>
                <a href="/admin/payments" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    const contractsByClient = <?= json_encode($contractsByClient, JSON_HEX_TAG) ?>;
    const preContractId     = <?= (int)($preContractId ?? 0) ?>;

    const clientSel   = document.getElementById('clientSel');
    const contractSel = document.getElementById('contractSel');

    function populateContracts(clientId) {
        contractSel.innerHTML = '';
        const placeholder = new Option('— Sélectionner un contrat —', '');
        contractSel.add(placeholder);

        const list = contractsByClient[clientId] ?? [];
        list.forEach(c => {
            const opt = new Option(c.label, c.id);
            if (c.id === preContractId) opt.selected = true;
            contractSel.add(opt);
        });

        if (list.length === 1) contractSel.selectedIndex = 1;
    }

    clientSel.addEventListener('change', function () {
        populateContracts(this.value);
    });

    if (clientSel.value) {
        populateContracts(clientSel.value);
    }
})();
</script>

<?php require APP_PATH . '/Views/admin/layout/footer.php'; ?>
