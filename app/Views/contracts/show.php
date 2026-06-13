<?php $pageTitle = 'Contrat ' . htmlspecialchars($contract['policy_number']) . ' – TILKI'; ?>
<?php require APP_PATH . '/Views/layout/header.php'; ?>

<div class="mb-3">
    <a href="/contracts" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Retour aux contrats
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card shadow-sm">
            <div class="card-header fw-bold">
                <i class="bi bi-file-earmark-text me-2"></i>Détails du contrat
            </div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-6 text-muted">Branche</dt>
                    <dd class="col-6 fw-semibold"><?= htmlspecialchars($contract['branche']) ?></dd>

                    <dt class="col-6 text-muted">N° Police</dt>
                    <dd class="col-6"><code><?= htmlspecialchars($contract['policy_number']) ?></code></dd>

                    <dt class="col-6 text-muted">Assureur</dt>
                    <dd class="col-6"><?= htmlspecialchars($contract['insurer']) ?></dd>

                    <dt class="col-6 text-muted">Début</dt>
                    <dd class="col-6"><?= date('d/m/Y', strtotime($contract['effective_date'])) ?></dd>

                    <dt class="col-6 text-muted">Expiration</dt>
                    <dd class="col-6"><?= date('d/m/Y', strtotime($contract['expiry_date'])) ?></dd>

                    <dt class="col-6 text-muted">Prime totale</dt>
                    <dd class="col-6">
                        <?= number_format((float)$contract['premium_total'], 0, ',', ' ') ?>
                        <?= htmlspecialchars($contract['currency']) ?>
                    </dd>

                    <dt class="col-6 text-muted">Prime due</dt>
                    <dd class="col-6">
                        <?= number_format((float)$contract['premium_due'], 0, ',', ' ') ?>
                        <?= htmlspecialchars($contract['currency']) ?>
                    </dd>

                    <dt class="col-6 text-muted">Statut</dt>
                    <dd class="col-6">
                        <span class="badge bg-<?= $contract['status'] === 'actif' ? 'success' : 'secondary' ?>">
                            <?= htmlspecialchars($contract['status']) ?>
                        </span>
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header fw-bold">
                <i class="bi bi-paperclip me-2"></i>Documents disponibles
            </div>
            <?php if (empty($documents)): ?>
                <div class="card-body text-muted small">Aucun document disponible pour ce contrat.</div>
            <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($documents as $doc): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <div>
                                <i class="bi bi-file-earmark-pdf text-danger me-2"></i>
                                <span class="small fw-semibold"><?= htmlspecialchars($doc['original_filename']) ?></span>
                                <div class="text-muted x-small mt-1">
                                    <?= htmlspecialchars($doc['doc_type']) ?>
                                    &bull; <?= number_format($doc['file_size'] / 1024, 0) ?> Ko
                                    &bull; <?= date('d/m/Y', strtotime($doc['created_at'])) ?>
                                </div>
                            </div>
                            <?php if ($doc['status'] === 'valide'): ?>
                                <a href="/documents/<?= $doc['id'] ?>/download"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-download me-1"></i>Télécharger
                                </a>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">En attente</span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
