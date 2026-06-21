<?php $pageTitle = 'Mes sinistres – TILKI'; ?>
<?php require APP_PATH . '/Views/layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 mb-0 fw-bold"><i class="bi bi-exclamation-triangle me-2"></i>Mes sinistres</h2>
    <?php if (!empty($contracts)): ?>
    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#declarerModal">
        <i class="bi bi-plus-lg me-1"></i>Déclarer un sinistre
    </button>
    <?php else: ?>
    <a href="/claims/declare" class="btn btn-danger">
        <i class="bi bi-plus-lg me-1"></i>Déclarer un sinistre
    </a>
    <?php endif; ?>
</div>

<?php if (empty($claims)): ?>
    <div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>Aucun sinistre enregistré.</div>
<?php else: ?>
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 tbl-card-mobile">
                <thead class="table-light">
                    <tr>
                        <th>N° Sinistre</th>
                        <th>Branche</th>
                        <th>Assureur</th>
                        <th>Survenance</th>
                        <th>N° Police</th>
                        <th>Statut</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($claims as $cl): ?>
                        <tr class="tbl-row-link" data-href="/claims/<?= (int)$cl['id'] ?>">
                            <td data-label="N° Sinistre"><code><?= htmlspecialchars($cl['claim_number']) ?></code></td>
                            <td data-label="Branche"><?= htmlspecialchars($cl['branche']) ?></td>
                            <td data-label="Assureur"><?= htmlspecialchars($cl['insurer']) ?></td>
                            <td data-label="Survenance"><?= date('d/m/Y', strtotime($cl['occurrence_date'])) ?></td>
                            <td data-label="N° Police"><?= htmlspecialchars($cl['policy_number'] ?? '—') ?></td>
                            <td data-label="Statut">
                                <span class="badge bg-<?= $cl['status'] === 'ouvert' ? 'danger' : 'success' ?>">
                                    <?= htmlspecialchars($cl['status']) ?>
                                </span>
                            </td>
                            <td data-label="">
                                <a href="/claims/<?= $cl['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye me-1"></i>Détail
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
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
                    <button type="submit" class="btn btn-danger" id="declarerSubmitBtn" disabled>
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
