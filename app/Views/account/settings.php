<?php $pageTitle = 'Paramètres de compte – TILKI'; ?>
<?php require APP_PATH . '/Views/layout/header.php'; ?>

<div class="mb-3">
    <h4 class="fw-bold mb-0"><i class="bi bi-person-gear me-2 text-primary"></i>Paramètres de compte</h4>
</div>

<?php $isEntreprise = ($client['account_type'] ?? 'individuel') === 'entreprise'; ?>

<div class="row g-4">

    <!-- ── Carte d'assurance ───────────────────────────────────────────────── -->
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header fw-semibold">
                <i class="bi bi-credit-card me-2 text-primary"></i>Carte d'assurance
            </div>
            <div class="card-body d-flex flex-column gap-3">
                <?php if ($carte): ?>

                    <?php if (str_starts_with($carte['mime_type'], 'image/')): ?>
                    <div class="border rounded overflow-hidden text-center">
                        <img src="/documents/<?= (int)$carte['id'] ?>/view"
                             alt="Carte d'assurance"
                             class="img-fluid"
                             style="max-height:280px; object-fit:contain;">
                    </div>
                    <?php else: ?>
                    <div class="border rounded p-3 text-center text-muted small">
                        <i class="bi bi-file-earmark-pdf fs-2 text-danger d-block mb-2"></i>
                        <?= htmlspecialchars($carte['original_filename']) ?>
                    </div>
                    <?php endif; ?>

                    <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                        <span class="text-muted small">
                            <?= number_format($carte['file_size'] / 1024, 0) ?>&nbsp;Ko
                            &bull; <?= date('d/m/Y', strtotime($carte['created_at'])) ?>
                        </span>
                        <div class="d-flex gap-2">
                            <?php if (str_starts_with($carte['mime_type'], 'image/') || $carte['mime_type'] === 'application/pdf'): ?>
                            <a href="/documents/<?= (int)$carte['id'] ?>/view"
                               target="_blank" rel="noopener"
                               class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-eye me-1"></i>Plein écran
                            </a>
                            <?php endif; ?>
                            <a href="/documents/<?= (int)$carte['id'] ?>/download"
                               class="btn btn-sm btn-primary">
                                <i class="bi bi-download me-1"></i>Télécharger
                            </a>
                        </div>
                    </div>

                <?php else: ?>
                    <div class="text-muted small py-3 text-center">
                        <i class="bi bi-credit-card fs-3 d-block mb-2 opacity-25"></i>
                        Aucune carte d'assurance disponible pour le moment.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ── Modifier le code PIN ────────────────────────────────────────────── -->
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header fw-semibold">
                <i class="bi bi-key me-2 text-primary"></i>Modifier mon code PIN
            </div>
            <div class="card-body">

                <?php if (!empty($pinError)): ?>
                <div class="alert alert-danger py-2 small">
                    <i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($pinError) ?>
                </div>
                <?php endif; ?>

                <form method="post" action="/account/pin" novalidate>
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Code PIN actuel</label>
                        <input type="password" name="current_password" class="form-control"
                               inputmode="numeric" autocomplete="current-password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nouveau code PIN</label>
                        <input type="password" name="new_password" class="form-control"
                               inputmode="numeric" pattern="[0-9]{4,8}"
                               minlength="4" maxlength="8"
                               autocomplete="new-password" required>
                        <div class="form-text">Entre 4 et 8 chiffres uniquement.</div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-semibold">Confirmer le nouveau code PIN</label>
                        <input type="password" name="confirm_password" class="form-control"
                               inputmode="numeric" pattern="[0-9]{4,8}"
                               minlength="4" maxlength="8"
                               autocomplete="new-password" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check2-circle me-2"></i>Enregistrer le code PIN
                    </button>
                </form>

            </div>
        </div>
    </div>

</div>

<?php if ($isEntreprise): ?>
<!-- ── Identité entreprise ────────────────────────────────────────────────────── -->
<div class="row g-4 mt-0">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header d-flex align-items-center gap-2 fw-semibold">
                <i class="bi bi-building text-primary"></i>Identité entreprise
            </div>
            <div class="card-body">

                <?php if (!empty($pinError)): ?>
                <?php /* pinError réutilisé pour ne pas multiplier les variables flash */ ?>
                <?php endif; ?>

                <form method="post" action="/account/entreprise" novalidate>
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Raison sociale <span class="text-danger">*</span></label>
                            <input type="text" name="company_name" class="form-control"
                                   value="<?= htmlspecialchars($client['company_name'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">N° RCCM</label>
                            <input type="text" name="company_rccm" class="form-control font-monospace"
                                   value="<?= htmlspecialchars($client['company_rccm'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">N° DFE</label>
                            <input type="text" name="company_dfe" class="form-control font-monospace"
                                   value="<?= htmlspecialchars($client['company_dfe'] ?? '') ?>">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold">Adresse du siège</label>
                            <input type="text" name="company_address" class="form-control"
                                   value="<?= htmlspecialchars($client['company_address'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Ville</label>
                            <input type="text" name="company_city" class="form-control"
                                   value="<?= htmlspecialchars($client['company_city'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Interlocuteur</label>
                            <input type="text" name="company_contact_name" class="form-control"
                                   value="<?= htmlspecialchars($client['company_contact_name'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Téléphone du contact</label>
                            <input type="tel" name="company_contact_phone" class="form-control"
                                   value="<?= htmlspecialchars($client['company_contact_phone'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-floppy me-2"></i>Enregistrer les informations entreprise
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- Checklist côté client -->
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-header d-flex align-items-center gap-2 fw-semibold">
                <i class="bi bi-clipboard-check text-primary"></i>Dossier entreprise
            </div>
            <div class="card-body p-3">
                <p class="small text-muted mb-3">
                    Complétez ces informations pour accélérer vos souscriptions.
                </p>
                <ul class="list-unstyled mb-0 small">
                    <?php
                    $checks = [
                        'Raison sociale'    => !empty($client['company_name']),
                        'N° RCCM'          => !empty($client['company_rccm']),
                        'N° DFE'           => !empty($client['company_dfe']),
                        'Adresse'          => !empty($client['company_address']),
                        'Interlocuteur'    => !empty($client['company_contact_name']),
                    ];
                    foreach ($checks as $label => $done): ?>
                    <li class="d-flex align-items-center gap-2 py-1 border-bottom">
                        <i class="bi bi-<?= $done ? 'check-circle-fill text-success' : 'circle text-muted opacity-50' ?>"></i>
                        <span class="<?= $done ? '' : 'text-muted' ?>"><?= $label ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
