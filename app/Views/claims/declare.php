<?php
$pageTitle = 'Déclarer un sinistre – TILKI';

function v(string $k, array $old, mixed $default = ''): mixed {
    return $old[$k] ?? $default;
}
?>
<?php require APP_PATH . '/Views/layout/header.php'; ?>

<div class="mb-3">
    <a href="/claims" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Retour aux sinistres
    </a>
</div>

<div class="row justify-content-center">
<div class="col-lg-7">

<div class="card shadow-sm">
    <div class="card-header fw-semibold">
        <i class="bi bi-exclamation-triangle me-2 text-danger"></i>Déclarer un sinistre
    </div>
    <div class="card-body p-4">

        <p class="text-muted small mb-4">
            Renseignez les informations ci-dessous. Notre équipe prendra en charge votre déclaration
            et vous contactera dans les meilleurs délais.
        </p>

        <?php if (!empty($error)): ?>
        <div class="alert alert-danger small">
            <i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="post" action="/claims/declare" novalidate>
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

            <div class="row g-3">

                <!-- Contrat lié (optionnel) -->
                <div class="col-12">
                    <label class="form-label small fw-semibold">
                        Contrat concerné
                        <span class="text-muted fw-normal">(optionnel — pré-remplit assureur et branche)</span>
                    </label>
                    <select name="contract_id" id="contractSel" class="form-select">
                        <option value="">— Aucun / Je ne sais pas —</option>
                        <?php foreach ($contracts as $c): ?>
                        <option value="<?= (int)$c['id'] ?>"
                                data-insurer="<?= htmlspecialchars($c['insurer']) ?>"
                                data-branche="<?= htmlspecialchars($c['branche']) ?>"
                            <?= (int)v('contract_id', $old, 0) === (int)$c['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['policy_number'] . ' — ' . $c['branche'] . ' (' . $c['insurer'] . ')') ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Assureur -->
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">
                        Assureur <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="insurer" id="insurerInput" class="form-control"
                           value="<?= htmlspecialchars((string)v('insurer', $old)) ?>"
                           placeholder="ex : NSIA Assurances" required>
                </div>

                <!-- Branche -->
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">
                        Branche <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="branche" id="brancheInput" class="form-control"
                           value="<?= htmlspecialchars((string)v('branche', $old)) ?>"
                           placeholder="ex : Automobile" required>
                </div>

                <!-- Date de survenance -->
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">
                        Date de survenance <span class="text-danger">*</span>
                    </label>
                    <input type="date" name="occurrence_date" class="form-control"
                           value="<?= htmlspecialchars((string)v('occurrence_date', $old)) ?>"
                           max="<?= date('Y-m-d') ?>" required>
                </div>

                <!-- Description -->
                <div class="col-12">
                    <label class="form-label small fw-semibold">
                        Description du sinistre <span class="text-danger">*</span>
                    </label>
                    <textarea name="description" class="form-control" rows="4"
                              placeholder="Décrivez les circonstances du sinistre (lieu, nature des dommages, personnes impliquées…)"
                              required><?= htmlspecialchars((string)v('description', $old)) ?></textarea>
                    <div class="form-text">Soyez aussi précis que possible pour faciliter le traitement de votre dossier.</div>
                </div>

            </div>

            <hr class="my-4">
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-send me-2"></i>Envoyer la déclaration
                </button>
                <a href="/claims" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>

    </div>
</div>

</div>
</div>

<script>
document.getElementById('contractSel').addEventListener('change', function () {
    const opt     = this.options[this.selectedIndex];
    const insurer = opt.dataset.insurer ?? '';
    const branche = opt.dataset.branche ?? '';
    if (insurer) document.getElementById('insurerInput').value = insurer;
    if (branche) document.getElementById('brancheInput').value = branche;
});
</script>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
