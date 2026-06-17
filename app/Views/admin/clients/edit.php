<?php $pageTitle = 'Fiche client – Administration TILKI'; ?>
<?php require APP_PATH . '/Views/admin/layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="/admin/clients" class="btn btn-sm btn-outline-secondary me-2">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
        <h2 class="h5 fw-bold mb-0 d-inline-block align-middle">
            <i class="bi bi-person-badge me-2"></i>
            <?= htmlspecialchars($client['first_name'] . ' ' . $client['last_name']) ?>
        </h2>
    </div>
    <span class="badge bg-<?= $client['status'] === 'actif' ? 'success' : ($client['status'] === 'suspendu' ? 'warning text-dark' : 'secondary') ?> fs-6">
        <?= htmlspecialchars($client['status']) ?>
    </span>
</div>

<div class="row g-4">

    <!-- ── Informations client ─────────────────────────────────────────────── -->
    <div class="col-lg-5">
        <div class="card shadow-sm">
            <div class="card-header fw-semibold">
                <i class="bi bi-person me-2 text-primary"></i>Informations
            </div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted">N° de compte</dt>
                    <dd class="col-7"><code><?= htmlspecialchars($client['account_number']) ?></code></dd>

                    <dt class="col-5 text-muted">Prénom</dt>
                    <dd class="col-7"><?= htmlspecialchars($client['first_name']) ?></dd>

                    <dt class="col-5 text-muted">Nom</dt>
                    <dd class="col-7"><?= htmlspecialchars($client['last_name']) ?></dd>

                    <dt class="col-5 text-muted">Email</dt>
                    <dd class="col-7"><?= htmlspecialchars($client['email']) ?></dd>

                    <dt class="col-5 text-muted">Téléphone</dt>
                    <dd class="col-7"><?= htmlspecialchars($client['phone'] ?? '—') ?></dd>

                    <dt class="col-5 text-muted">Créé le</dt>
                    <dd class="col-7 text-muted"><?= date('d/m/Y', strtotime($client['created_at'])) ?></dd>
                </dl>
            </div>
        </div>
    </div>

    <!-- ── Carte d'assurance ───────────────────────────────────────────────── -->
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header fw-semibold">
                <i class="bi bi-credit-card me-2 text-primary"></i>Carte d'assurance
            </div>
            <div class="card-body d-flex flex-column gap-3">

                <?php if ($carte): ?>
                <div class="d-flex align-items-start gap-3 p-3 bg-light rounded">
                    <i class="bi bi-<?= str_starts_with($carte['mime_type'], 'image/') ? 'file-earmark-image text-info' : 'file-earmark-pdf text-danger' ?> fs-3 flex-shrink-0"></i>
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="fw-semibold small text-truncate"><?= htmlspecialchars($carte['original_filename']) ?></div>
                        <div class="text-muted" style="font-size:.78rem">
                            <?= number_format($carte['file_size'] / 1024, 0) ?>&nbsp;Ko
                            &bull; Uploadée le <?= date('d/m/Y', strtotime($carte['created_at'])) ?>
                        </div>
                    </div>
                </div>
                <p class="small text-muted mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    Uploader un nouveau fichier remplacera la carte actuelle.
                </p>
                <?php else: ?>
                <p class="small text-muted mb-0">
                    <i class="bi bi-dash me-1 opacity-50"></i>
                    Aucune carte d'assurance pour ce client. Uploadez-en une ci-dessous.
                </p>
                <?php endif; ?>

                <form method="post"
                      action="/admin/clients/<?= (int)$client['id'] ?>/carte"
                      enctype="multipart/form-data"
                      novalidate>
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">
                            Fichier <span class="text-muted fw-normal">(PDF, JPG ou PNG – max&nbsp;10&nbsp;Mo)</span>
                        </label>
                        <input type="file" name="document" class="form-control"
                               accept=".pdf,.jpg,.jpeg,.png" required>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-upload me-2"></i><?= $carte ? 'Remplacer la carte' : 'Uploader la carte' ?>
                    </button>
                </form>

            </div>
        </div>
    </div>

</div>

<?php require APP_PATH . '/Views/admin/layout/footer.php'; ?>
