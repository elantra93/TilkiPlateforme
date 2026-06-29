<?php $pageTitle = 'Fiche client – Administration TILKI'; ?>
<?php require APP_PATH . '/Views/admin/layout/header.php'; ?>

<?php
$isEntreprise = ($client['account_type'] ?? 'individuel') === 'entreprise';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="/admin/clients" class="btn btn-sm btn-outline-secondary me-2">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
        <h2 class="h5 fw-bold mb-0 d-inline-block align-middle">
            <i class="bi bi-person-badge me-2"></i>
            <?= htmlspecialchars($client['first_name'] . ' ' . $client['last_name']) ?>
            <?php if ($isEntreprise && $client['company_name']): ?>
            <span class="text-muted fw-normal small ms-1">/ <?= htmlspecialchars($client['company_name']) ?></span>
            <?php endif; ?>
        </h2>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-<?= $client['status'] === 'actif' ? 'success' : ($client['status'] === 'suspendu' ? 'warning text-dark' : 'secondary') ?>">
            <?= htmlspecialchars($client['status']) ?>
        </span>
        <?php if ($isEntreprise): ?>
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
            <i class="bi bi-building me-1"></i>Entreprise
        </span>
        <?php endif; ?>
        <a href="/admin/payments/create?client_id=<?= (int)$client['id'] ?>" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-cash-coin me-1"></i>Enregistrer un paiement
        </a>
    </div>
</div>

<div class="row g-4">

    <!-- ── Formulaire identité ────────────────────────────────────────────────── -->
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header d-flex align-items-center gap-2 fw-semibold">
                <i class="bi bi-person text-primary"></i>Identité
            </div>
            <div class="card-body">
                <form method="post" action="/admin/clients/<?= (int)$client['id'] ?>/edit" novalidate>
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

                    <!-- Type de compte -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold d-block">Type de compte</label>
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="account_type" id="at_edit_ind"
                                   value="individuel" data-account-type-toggle
                                   <?= !$isEntreprise ? 'checked' : '' ?>>
                            <label class="btn btn-outline-primary btn-sm" for="at_edit_ind">
                                <i class="bi bi-person me-1"></i>Individuel
                            </label>
                            <input type="radio" class="btn-check" name="account_type" id="at_edit_ent"
                                   value="entreprise" data-account-type-toggle
                                   <?= $isEntreprise ? 'checked' : '' ?>>
                            <label class="btn btn-outline-primary btn-sm" for="at_edit_ent">
                                <i class="bi bi-building me-1"></i>Entreprise
                            </label>
                        </div>
                    </div>

                    <!-- Identité de base -->
                    <div class="row g-2 mb-2">
                        <div class="col-md-5">
                            <label class="form-label small fw-semibold">Prénom <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control form-control-sm"
                                   value="<?= htmlspecialchars($client['first_name']) ?>" required>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label small fw-semibold">Nom <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control form-control-sm"
                                   value="<?= htmlspecialchars($client['last_name']) ?>" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control form-control-sm"
                                   value="<?= htmlspecialchars($client['email']) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Téléphone</label>
                            <input type="tel" name="phone" class="form-control form-control-sm"
                                   value="<?= htmlspecialchars($client['phone'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Statut</label>
                            <select name="status" class="form-select form-select-sm">
                                <?php foreach (['actif','inactif','suspendu'] as $s): ?>
                                <option value="<?= $s ?>" <?= $client['status'] === $s ? 'selected' : '' ?>>
                                    <?= ucfirst($s) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">N° de compte</label>
                            <input type="text" class="form-control form-control-sm font-monospace bg-light"
                                   value="<?= htmlspecialchars($client['account_number']) ?>" readonly>
                        </div>
                    </div>

                    <!-- Identité entreprise (masquée si individuel) -->
                    <div data-enterprise-section
                         class="<?= $isEntreprise ? '' : 'd-none' ?>">
                        <hr class="my-3">
                        <p class="small fw-semibold text-primary mb-2">
                            <i class="bi bi-building me-1"></i>Identité entreprise
                        </p>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Raison sociale <span class="text-danger">*</span></label>
                                <input type="text" name="company_name" class="form-control form-control-sm"
                                       value="<?= htmlspecialchars($client['company_name'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">N° RCCM</label>
                                <input type="text" name="company_rccm" class="form-control form-control-sm font-monospace"
                                       value="<?= htmlspecialchars($client['company_rccm'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">N° DFE</label>
                                <input type="text" name="company_dfe" class="form-control form-control-sm font-monospace"
                                       value="<?= htmlspecialchars($client['company_dfe'] ?? '') ?>">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label small fw-semibold">Adresse du siège</label>
                                <input type="text" name="company_address" class="form-control form-control-sm"
                                       value="<?= htmlspecialchars($client['company_address'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Ville</label>
                                <input type="text" name="company_city" class="form-control form-control-sm"
                                       value="<?= htmlspecialchars($client['company_city'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Interlocuteur</label>
                                <input type="text" name="company_contact_name" class="form-control form-control-sm"
                                       value="<?= htmlspecialchars($client['company_contact_name'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Téléphone du contact</label>
                                <input type="tel" name="company_contact_phone" class="form-control form-control-sm"
                                       value="<?= htmlspecialchars($client['company_contact_phone'] ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            Créé le <?= date('d/m/Y', strtotime($client['created_at'])) ?>
                        </small>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-floppy me-1"></i>Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($isEntreprise): ?>
        <!-- Checklist conformité entreprise -->
        <div class="card shadow-sm mt-3">
            <div class="card-header d-flex align-items-center gap-2 fw-semibold">
                <i class="bi bi-clipboard-check text-primary"></i>Conformité dossier
            </div>
            <div class="card-body p-3">
                <ul class="list-unstyled mb-0 small">
                    <?php
                    $checks = [
                        'Raison sociale renseignée' => !empty($client['company_name']),
                        'N° RCCM fourni'            => !empty($client['company_rccm']),
                        'N° DFE fourni'             => !empty($client['company_dfe']),
                        'Adresse du siège'          => !empty($client['company_address']),
                        'Interlocuteur désigné'     => !empty($client['company_contact_name']),
                    ];
                    foreach ($checks as $label => $done): ?>
                    <li class="d-flex align-items-center gap-2 py-1 border-bottom">
                        <i class="bi bi-<?= $done ? 'check-circle-fill text-success' : 'circle text-muted opacity-50' ?>"></i>
                        <span class="<?= $done ? '' : 'text-muted' ?>"><?= $label ?></span>
                        <?php if (!$done): ?>
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle ms-auto fs-xxs">À compléter</span>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- ── Carte d'assurance ───────────────────────────────────────────────────── -->
    <div class="col-lg-5">
        <div class="card shadow-sm">
            <div class="card-header d-flex align-items-center gap-2 fw-semibold">
                <i class="bi bi-credit-card text-primary"></i>Carte d'assurance
            </div>
            <div class="card-body d-flex flex-column gap-3">

                <?php if ($carte): ?>
                <div class="d-flex align-items-start gap-3 p-3 bg-light rounded">
                    <i class="bi bi-<?= str_starts_with($carte['mime_type'], 'image/') ? 'file-earmark-image text-info' : 'file-earmark-pdf text-danger' ?> fs-3 flex-shrink-0"></i>
                    <div class="flex-grow-1 overflow-hidden">
                        <div class="fw-semibold small text-truncate"><?= htmlspecialchars($carte['original_filename']) ?></div>
                        <div class="text-muted fs-sm7">
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
                    Aucune carte d'assurance pour ce client.
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
                        <input type="file" name="document" class="form-control form-control-sm"
                               accept=".pdf,.jpg,.jpeg,.png" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-upload me-2"></i><?= $carte ? 'Remplacer la carte' : 'Uploader la carte' ?>
                    </button>
                </form>

            </div>
        </div>
    </div>

</div>

<!-- ── Documents client ──────────────────────────────────────────────────────── -->
<div class="card shadow-sm mt-4">
    <div class="card-header d-flex align-items-center gap-2 fw-semibold">
        <i class="bi bi-folder text-primary"></i>Documents du dossier client
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
                            <a href="/admin/documents/<?= (int)$doc['id'] ?>/download"
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
