<?php $pageTitle = 'Paramètres de compte – TILKI'; ?>
<?php require APP_PATH . '/Views/layout/header.php'; ?>

<div class="mb-3">
    <h4 class="fw-bold mb-0"><i class="bi bi-person-gear me-2 text-primary"></i>Paramètres de compte</h4>
</div>

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

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
