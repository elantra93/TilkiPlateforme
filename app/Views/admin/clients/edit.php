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
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-<?= $client['status'] === 'actif' ? 'success' : ($client['status'] === 'suspendu' ? 'warning text-dark' : 'secondary') ?> fs-6">
            <?= htmlspecialchars($client['status']) ?>
        </span>
        <a href="/admin/payments/create?client_id=<?= (int)$client['id'] ?>" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-cash-coin me-1"></i>Enregistrer un paiement
        </a>
    </div>
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

<!-- ── Documents client ──────────────────────────────────────────────────────── -->
<div class="card shadow-sm mt-4">
    <div class="card-header fw-semibold">
        <i class="bi bi-folder me-2 text-primary"></i>Documents du dossier client
    </div>
    <div class="card-body p-0">

        <!-- Formulaire d'ajout -->
        <div class="p-3 border-bottom bg-light">
            <p class="small fw-semibold mb-2"><i class="bi bi-upload me-1"></i>Ajouter un document</p>
            <form method="post"
                  action="/admin/clients/<?= (int)$client['id'] ?>/upload-doc"
                  enctype="multipart/form-data"
                  class="row g-2 align-items-end">
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
                <div class="col-md-4">
                    <label class="form-label small mb-1">Type de document <span class="text-danger">*</span></label>
                    <select name="doc_type" class="form-select form-select-sm" required>
                        <option value="">— Choisir —</option>
                        <?php foreach ($docTypes as $key => $label): ?>
                        <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label small mb-1">Fichier <span class="text-muted fw-normal">(PDF, JPG, PNG – max 10 Mo)</span></label>
                    <input type="file" name="document" class="form-control form-control-sm"
                           accept=".pdf,.jpg,.jpeg,.png" required>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-upload me-1"></i>Envoyer
                    </button>
                </div>
            </form>
        </div>

        <!-- Liste documents existants -->
        <?php if (empty($clientDocs)): ?>
            <p class="text-muted small p-3 mb-0"><i class="bi bi-dash me-1 opacity-50"></i>Aucun document dans le dossier.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Type</th><th>Fichier</th><th>Taille</th><th>Date</th><th></th></tr>
                </thead>
                <tbody>
                <?php foreach ($clientDocs as $doc): ?>
                    <tr>
                        <td class="small"><?= htmlspecialchars($docTypes[$doc['doc_type']] ?? $doc['doc_type']) ?></td>
                        <td class="small text-truncate" style="max-width:220px"><?= htmlspecialchars($doc['original_filename']) ?></td>
                        <td class="small text-muted"><?= number_format($doc['file_size']/1024,0) ?>&nbsp;Ko</td>
                        <td class="small text-muted"><?= date('d/m/Y', strtotime($doc['created_at'])) ?></td>
                        <td>
                            <a href="/documents/<?= (int)$doc['id'] ?>/download"
                               class="btn btn-sm btn-outline-secondary" target="_blank">
                                <i class="bi bi-download"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require APP_PATH . '/Views/admin/layout/footer.php'; ?>
