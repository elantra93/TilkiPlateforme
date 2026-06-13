<?php $pageTitle = 'Sinistre ' . htmlspecialchars($claim['claim_number']) . ' – TILKI'; ?>
<?php require APP_PATH . '/Views/layout/header.php'; ?>

<div class="mb-3">
    <a href="/claims" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Retour aux sinistres
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card shadow-sm">
            <div class="card-header fw-bold">
                <i class="bi bi-exclamation-triangle me-2"></i>Détails du sinistre
            </div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-6 text-muted">N° Sinistre</dt>
                    <dd class="col-6"><code><?= htmlspecialchars($claim['claim_number']) ?></code></dd>

                    <dt class="col-6 text-muted">Branche</dt>
                    <dd class="col-6"><?= htmlspecialchars($claim['branche']) ?></dd>

                    <dt class="col-6 text-muted">Assureur</dt>
                    <dd class="col-6"><?= htmlspecialchars($claim['insurer']) ?></dd>

                    <dt class="col-6 text-muted">N° Police</dt>
                    <dd class="col-6"><?= htmlspecialchars($claim['policy_number'] ?? '—') ?></dd>

                    <dt class="col-6 text-muted">Survenance</dt>
                    <dd class="col-6"><?= date('d/m/Y', strtotime($claim['occurrence_date'])) ?></dd>

                    <dt class="col-6 text-muted">Statut</dt>
                    <dd class="col-6">
                        <span class="badge bg-<?= $claim['status'] === 'ouvert' ? 'danger' : 'success' ?>">
                            <?= htmlspecialchars($claim['status']) ?>
                        </span>
                    </dd>

                    <?php if ($claim['description']): ?>
                        <dt class="col-12 text-muted mt-2">Description</dt>
                        <dd class="col-12"><?= nl2br(htmlspecialchars($claim['description'])) ?></dd>
                    <?php endif; ?>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <!-- Documents -->
        <div class="card shadow-sm mb-4">
            <div class="card-header fw-bold">
                <i class="bi bi-paperclip me-2"></i>Documents
            </div>
            <?php if (empty($documents)): ?>
                <div class="card-body text-muted small">Aucun document pour ce sinistre.</div>
            <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($documents as $doc): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <div>
                                <i class="bi bi-file-earmark me-2 text-secondary"></i>
                                <span class="small fw-semibold"><?= htmlspecialchars($doc['original_filename']) ?></span>
                                <div class="text-muted x-small mt-1">
                                    <?= htmlspecialchars($doc['doc_type']) ?>
                                    &bull; <?= number_format($doc['file_size'] / 1024, 0) ?> Ko
                                    &bull; <?= date('d/m/Y', strtotime($doc['created_at'])) ?>
                                    &bull; source : <?= htmlspecialchars($doc['source']) ?>
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

        <!-- Upload preuve de règlement -->
        <?php if ($claim['status'] === 'ouvert'): ?>
        <div class="card shadow-sm">
            <div class="card-header fw-bold">
                <i class="bi bi-upload me-2"></i>Déposer une preuve de règlement
            </div>
            <div class="card-body">
                <form method="post" action="/claims/<?= $claim['id'] ?>/upload"
                      enctype="multipart/form-data" novalidate>
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">
                            Fichier <span class="text-muted fw-normal">(PDF, JPG, PNG – max 10 Mo)</span>
                        </label>
                        <input type="file" name="document" class="form-control"
                               accept=".pdf,.jpg,.jpeg,.png" required>
                    </div>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-upload me-2"></i>Envoyer
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
