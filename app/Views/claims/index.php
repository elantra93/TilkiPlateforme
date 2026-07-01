<?php $pageTitle = 'Mes sinistres – TILKI'; ?>
<?php require APP_PATH . '/Views/layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 mb-0 fw-bold">Mes sinistres</h2>
    <?php if (!empty($contracts)): ?>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#declarerModal">
        <i class="bi bi-plus-lg me-1"></i>Déclarer un sinistre
    </button>
    <?php else: ?>
    <a href="/claims/declare" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Déclarer un sinistre
    </a>
    <?php endif; ?>
</div>

<?php if (empty($claims)): ?>
<div class="card shadow-sm">
    <div class="card-body text-center text-muted py-5">
        <i class="bi bi-shield-check fs-1 d-block mb-2 text-success opacity-50"></i>
        <p class="mb-0">Aucun sinistre enregistré.</p>
    </div>
</div>
<?php else: ?>
    <div class="card">
        <ul class="list-group list-group-flush">
            <?php foreach ($claims as $cl): ?>
            <li class="list-group-item list-group-item-action px-4 py-3 tk-list-row"
                onclick="window.location='/claims/<?= (int)$cl['id'] ?>'" style="cursor:pointer">
                <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                    <div class="d-flex align-items-center gap-3 min-w-0">
                        <span class="tk-icon-tile"><i class="bi <?= tk_branche_icon($cl['branche']) ?>"></i></span>
                        <div class="min-w-0">
                            <div class="fw-semibold text-body">
                                <?= htmlspecialchars($cl['branche']) ?> &middot; <?= htmlspecialchars($cl['insurer']) ?>
                            </div>
                            <div class="small text-muted mt-1">
                                <span class="font-mono"><?= htmlspecialchars($cl['claim_number']) ?></span>
                                &middot; déclaré le <?= date('d/m/Y', strtotime($cl['occurrence_date'])) ?>
                            </div>
                        </div>
                    </div>
                    <span class="badge tk-badge-<?= htmlspecialchars($cl['status']) ?> flex-shrink-0">
                        <?= htmlspecialchars($cl['status']) ?>
                    </span>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<!-- ── Modal sélection de contrat ─────────────────────────────────────────── -->
<?php if (!empty($contracts)): ?>
<div class="modal fade" id="declarerModal" tabindex="-1" aria-labelledby="declarerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold" id="declarerModalLabel">
                    <i class="bi bi-exclamation-triangle me-2 text-danger"></i>Déclarer un sinistre
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <form method="get" action="/claims/declare" novalidate id="declarerForm" target="_blank">
                <div class="modal-body">
                    <p class="text-muted small mb-3">
                        Sélectionnez le contrat concerné. Vous serez redirigé vers le formulaire
                        de déclaration en ligne, pré-rempli avec les informations de votre contrat.
                    </p>
                    <label for="modalContractSel" class="form-label fw-semibold">
                        Contrat concerné <span class="text-danger">*</span>
                    </label>
                    <select name="contract_id" id="modalContractSel" class="form-select" required>
                        <option value="">— Sélectionner une police —</option>
                        <?php foreach ($contracts as $c): ?>
                        <option value="<?= (int)$c['id'] ?>">
                            <?= htmlspecialchars($c['policy_number'] . ' — ' . $c['branche'] . ' (' . $c['insurer'] . ')') ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary" id="declarerSubmitBtn" disabled>
                        <i class="bi bi-box-arrow-up-right me-1"></i>Accéder au formulaire
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script src="/assets/js/claims-modal.js"></script>
<?php require APP_PATH . '/Views/layout/footer.php'; ?>
