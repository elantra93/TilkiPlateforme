<?php
$pageTitle = 'Sinistre ' . htmlspecialchars($claim['claim_number']) . ' – TILKI';

function docIcon(string $mime): string {
    return match(true) {
        $mime === 'application/pdf'      => 'bi-file-earmark-pdf text-danger',
        str_starts_with($mime, 'image/') => 'bi-file-earmark-image text-info',
        str_contains($mime, 'word')      => 'bi-file-earmark-word text-primary',
        default                          => 'bi-file-earmark text-secondary',
    };
}

// ── Répartition des documents par catégorie ───────────────────────────────────
$byCategory = [
    'declaration'               => [],
    'expertise_devis'           => [],
    'correspondances'           => [],
    'reglements_remboursements' => [],
];
foreach ($documents as $doc) {
    $cat = $doc['category'];
    if (array_key_exists($cat, $byCategory)) {
        $byCategory[$cat][] = $doc;
    }
}

// Dans "expertise_devis" : regrouper les 3 types distincts
$expertiseTypes = [
    'devis_reparation' => ['label' => 'Devis',               'icon' => 'bi-calculator',         'docs' => []],
    'rapport_expertise'=> ['label' => "Rapport d'expertise", 'icon' => 'bi-clipboard2-check',   'docs' => []],
    'constat_police'   => ['label' => 'Constat de police',   'icon' => 'bi-shield-exclamation', 'docs' => []],
];
$expertiseOther = [];
foreach ($byCategory['expertise_devis'] as $doc) {
    if (array_key_exists($doc['doc_type'], $expertiseTypes)) {
        $expertiseTypes[$doc['doc_type']]['docs'][] = $doc;
    } else {
        $expertiseOther[] = $doc;
    }
}

// Déclarations : séparer Tally des autres
$declTally  = array_values(array_filter($byCategory['declaration'], fn($d) => $d['source'] === 'tally'));
$declOther  = array_values(array_filter($byCategory['declaration'], fn($d) => $d['source'] !== 'tally'));

// Sinistre ouvert ?
$isOpen = $claim['status'] === 'ouvert';
?>
<?php require APP_PATH . '/Views/layout/header.php'; ?>

<div class="mb-3">
    <a href="/claims" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Retour aux sinistres
    </a>
</div>

<div class="row g-4">

    <!-- ── Colonne gauche ────────────────────────────────────────────────────── -->
    <div class="col-lg-4 d-flex flex-column gap-4">

        <!-- Détails du sinistre -->
        <div class="card shadow-sm">
            <div class="card-header fw-semibold">
                <i class="bi bi-exclamation-triangle me-2 text-danger"></i>Détails du sinistre
            </div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-6 text-muted">N° Sinistre</dt>
                    <dd class="col-6"><code class="text-body"><?= htmlspecialchars($claim['claim_number']) ?></code></dd>

                    <dt class="col-6 text-muted">Branche</dt>
                    <dd class="col-6 fw-semibold"><?= htmlspecialchars($claim['branche']) ?></dd>

                    <dt class="col-6 text-muted">Assureur</dt>
                    <dd class="col-6"><?= htmlspecialchars($claim['insurer']) ?></dd>

                    <dt class="col-6 text-muted">N° Police</dt>
                    <dd class="col-6">
                        <?php if (!empty($claim['policy_number'])): ?>
                            <code class="text-body"><?= htmlspecialchars($claim['policy_number']) ?></code>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </dd>

                    <dt class="col-6 text-muted">Survenance</dt>
                    <dd class="col-6"><?= date('d/m/Y', strtotime($claim['occurrence_date'])) ?></dd>

                    <dt class="col-6 text-muted">Mis à jour</dt>
                    <dd class="col-6 text-muted"><?= date('d/m/Y', strtotime($claim['updated_at'])) ?></dd>

                    <dt class="col-6 text-muted">Statut</dt>
                    <dd class="col-6">
                        <span class="badge tk-badge-<?= htmlspecialchars($claim['status']) ?>">
                            <?= htmlspecialchars($claim['status']) ?>
                        </span>
                    </dd>

                    <?php if (!empty($claim['description'])): ?>
                        <dt class="col-12 text-muted mt-2">Description</dt>
                        <dd class="col-12"><?= nl2br(htmlspecialchars($claim['description'])) ?></dd>
                    <?php endif; ?>
                </dl>
            </div>
        </div>

        <!-- Frise d'avancement -->
        <?php if (!empty($steps)): ?>
        <div class="card shadow-sm">
            <div class="card-header fw-semibold">
                <i class="bi bi-list-check me-2 text-primary"></i>Avancement du dossier
            </div>
            <div class="card-body">
                <div class="claim-timeline">
                <?php foreach ($steps as $step):
                    $done = (bool)$step['completed'];
                ?>
                <div class="ct-item <?= $done ? 'ct-done' : 'ct-pending' ?>">
                    <div class="ct-dot">
                        <?php if ($done): ?>
                            <i class="bi bi-check-lg"></i>
                        <?php else: ?>
                            <?= (int)$step['position'] ?>
                        <?php endif; ?>
                    </div>
                    <div class="ct-content">
                        <div class="ct-label"><?= htmlspecialchars($step['label']) ?></div>
                        <?php if ($done && !empty($step['completed_date'])): ?>
                        <div class="ct-date">
                            <i class="bi bi-calendar-check me-1"></i><?= date('d/m/Y', strtotime($step['completed_date'])) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div><!-- /col-lg-4 -->

    <!-- ── Colonne droite : documents ────────────────────────────────────────── -->
    <div class="col-lg-8 d-flex flex-column gap-4">

        <!-- ════════════════════════════════════════════════════════════════════
             SECTION 1 — Déclaration
             ════════════════════════════════════════════════════════════════════ -->
        <?php $declEmpty = empty($byCategory['declaration']); ?>
        <div class="card shadow-sm <?= $declEmpty ? 'border-danger' : 'border-success-subtle' ?>">
            <div class="card-header d-flex align-items-center gap-2 fw-semibold <?= $declEmpty ? 'text-danger bg-danger bg-opacity-10' : 'text-success' ?>">
                <i class="bi bi-clipboard-data"></i>
                Déclaration
                <span class="badge <?= $declEmpty ? 'bg-danger' : 'bg-success' ?> fw-normal ms-1"><?= count($byCategory['declaration']) ?></span>
            </div>
            <div class="card-body d-flex flex-column gap-3 p-3">

                <?php if ($declEmpty): ?>
                <div class="d-flex align-items-center gap-2 text-danger small">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <strong>Aucun document — déclaration non encore reçue.</strong>
                </div>
                <?php else: ?>

                <!-- Documents non-Tally (déposés par le client ou l'admin) -->
                <?php if (!empty($declOther)): ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($declOther as $doc): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                        <div class="me-3 overflow-hidden">
                            <i class="bi <?= docIcon($doc['mime_type']) ?> me-2"></i>
                            <span class="small fw-semibold"><?= htmlspecialchars($doc['original_filename']) ?></span>
                            <div class="text-muted mt-1 fs-xs">
                                <?= number_format($doc['file_size'] / 1024, 0) ?>&nbsp;Ko
                                &bull; <?= date('d/m/Y', strtotime($doc['created_at'])) ?>
                                <?php if ($doc['source'] === 'client'): ?>
                                    &bull; <em>déposé par vous</em>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if ($doc['status'] === 'valide'): ?>
                            <a href="/documents/<?= (int)$doc['id'] ?>/download"
                               class="btn btn-sm btn-outline-primary flex-shrink-0">
                                <i class="bi bi-download me-1"></i>Télécharger
                            </a>
                        <?php else: ?>
                            <span class="badge tk-badge-en_attente flex-shrink-0">en attente</span>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>

                <!-- Réponses Tally -->
                <?php if (!empty($declTally) && !empty($tallyFields)): ?>
                <div class="border rounded p-0 overflow-hidden">
                    <button class="btn btn-link w-100 text-start d-flex align-items-center gap-2 px-3 py-2 text-decoration-none fw-semibold"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#tallyAnswers"
                            aria-expanded="false">
                        <i class="bi bi-ui-checks-grid text-primary"></i>
                        Consulter mes réponses au formulaire
                        <i class="bi bi-chevron-down ms-auto small"></i>
                    </button>
                    <div class="collapse" id="tallyAnswers">
                        <div class="border-top px-3 py-2">
                            <dl class="row mb-0 small">
                                <?php foreach ($tallyFields as $f): ?>
                                <dt class="col-sm-5 text-muted py-1"><?= htmlspecialchars($f['label']) ?></dt>
                                <dd class="col-sm-7 py-1"><?= nl2br(htmlspecialchars($f['value'])) ?></dd>
                                <?php endforeach; ?>
                            </dl>
                        </div>
                    </div>
                </div>
                <?php elseif (!empty($declTally)): ?>
                <div class="small text-muted">
                    <i class="bi bi-check2-circle text-success me-1"></i>
                    Déclaration reçue via formulaire Tally.
                </div>
                <?php endif; ?>

                <?php endif; /* /declEmpty */ ?>

                <!-- Formulaire de dépôt (sinistre ouvert) -->
                <?php if ($isOpen): ?>
                <div class="border-top pt-3 mt-1">
                    <p class="small text-muted mb-2">
                        <i class="bi bi-upload me-1"></i>
                        Déposer votre déclaration de sinistre (PDF ou image, max&nbsp;10&nbsp;Mo) :
                    </p>
                    <form method="post" action="/claims/<?= (int)$claim['id'] ?>/upload"
                          enctype="multipart/form-data" novalidate class="d-flex gap-2 flex-wrap align-items-end">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
                        <input type="hidden" name="doc_type" value="declaration_sinistre">
                        <div class="flex-grow-1">
                            <input type="file" name="document" class="form-control form-control-sm"
                                   accept=".pdf,.jpg,.jpeg,.png" required>
                        </div>
                        <button type="submit" class="btn btn-sm btn-outline-primary flex-shrink-0">
                            <i class="bi bi-upload me-1"></i>Envoyer
                        </button>
                    </form>
                </div>
                <?php endif; ?>

            </div>
        </div>


        <!-- ════════════════════════════════════════════════════════════════════
             SECTION 2 — Rapports d'expertises et devis
             ════════════════════════════════════════════════════════════════════ -->
        <?php $expertEmpty = empty($byCategory['expertise_devis']); ?>
        <div class="card shadow-sm <?= $expertEmpty ? '' : 'border-success-subtle' ?>">
            <div class="card-header d-flex align-items-center gap-2 fw-semibold <?= $expertEmpty ? 'text-secondary' : 'text-success' ?>">
                <i class="bi bi-file-earmark-check <?= $expertEmpty ? 'text-secondary opacity-50' : 'text-success' ?>"></i>
                Rapports d'expertises et devis
                <span class="badge <?= $expertEmpty ? 'bg-secondary opacity-50' : 'bg-success' ?> fw-normal ms-1"><?= count($byCategory['expertise_devis']) ?></span>
            </div>
            <div class="card-body d-flex flex-column gap-0 p-0">

                <?php foreach ($expertiseTypes as $typeKey => $typeMeta):
                    $typeDocs   = $typeMeta['docs'];
                    $typeEmpty  = empty($typeDocs);
                ?>
                <div class="border-bottom px-3 py-2">
                    <div class="d-flex align-items-center gap-2 mb-<?= $typeEmpty ? '0' : '2' ?>">
                        <i class="bi <?= $typeMeta['icon'] ?> <?= $typeEmpty ? 'text-secondary opacity-50' : 'text-secondary' ?> small"></i>
                        <span class="small fw-semibold <?= $typeEmpty ? 'text-muted' : '' ?>"><?= $typeMeta['label'] ?></span>
                        <?php if ($typeEmpty): ?>
                            <span class="ms-auto small text-muted"><i class="bi bi-dash me-1"></i>Aucun document</span>
                        <?php else: ?>
                            <span class="ms-auto badge bg-secondary fw-normal"><?= count($typeDocs) ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if (!$typeEmpty): ?>
                    <ul class="list-unstyled mb-0 ps-3">
                        <?php foreach ($typeDocs as $doc): ?>
                        <li class="d-flex justify-content-between align-items-center py-1">
                            <div class="me-3 overflow-hidden">
                                <i class="bi <?= docIcon($doc['mime_type']) ?> me-1 small"></i>
                                <span class="small"><?= htmlspecialchars($doc['original_filename']) ?></span>
                                <span class="text-muted ms-1 fs-xxs">
                                    <?= number_format($doc['file_size'] / 1024, 0) ?>&nbsp;Ko
                                    &bull; <?= date('d/m/Y', strtotime($doc['created_at'])) ?>
                                    <?php if ($doc['source'] === 'client'): ?>
                                        &bull; <em>vous</em>
                                    <?php elseif ($doc['source'] === 'admin'): ?>
                                        &bull; TILKI
                                    <?php endif; ?>
                                </span>
                            </div>
                            <?php if ($doc['status'] === 'valide'): ?>
                                <a href="/documents/<?= (int)$doc['id'] ?>/download"
                                   class="btn btn-sm btn-outline-primary flex-shrink-0 py-0">
                                    <i class="bi bi-download me-1"></i><span class="small">Télécharger</span>
                                </a>
                            <?php else: ?>
                                <span class="badge tk-badge-en_attente flex-shrink-0">en attente</span>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>

                <!-- Documents d'autres types dans expertise_devis (ex. contre_expertise) -->
                <?php foreach ($expertiseOther as $doc): ?>
                <div class="border-bottom px-3 py-2 d-flex justify-content-between align-items-center">
                    <div class="me-3 overflow-hidden">
                        <span class="badge bg-light text-dark border me-1 fs-2xs">
                            <?= htmlspecialchars(str_replace('_', ' ', $doc['doc_type'])) ?>
                        </span>
                        <i class="bi <?= docIcon($doc['mime_type']) ?> me-1 small"></i>
                        <span class="small"><?= htmlspecialchars($doc['original_filename']) ?></span>
                        <span class="text-muted ms-1 fs-xxs">
                            <?= number_format($doc['file_size'] / 1024, 0) ?>&nbsp;Ko
                            &bull; <?= date('d/m/Y', strtotime($doc['created_at'])) ?>
                        </span>
                    </div>
                    <?php if ($doc['status'] === 'valide'): ?>
                        <a href="/documents/<?= (int)$doc['id'] ?>/download"
                           class="btn btn-sm btn-outline-primary flex-shrink-0 py-0">
                            <i class="bi bi-download me-1"></i><span class="small">Télécharger</span>
                        </a>
                    <?php else: ?>
                        <span class="badge tk-badge-en_attente flex-shrink-0">en attente</span>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>

                <!-- Formulaire dépôt (expertise/devis/constat) -->
                <?php if ($isOpen): ?>
                <div class="px-3 py-3">
                    <p class="small text-muted mb-2">
                        <i class="bi bi-upload me-1"></i>
                        Déposer un devis, rapport d'expertise ou constat de police (PDF ou image, max&nbsp;10&nbsp;Mo) :
                    </p>
                    <form method="post" action="/claims/<?= (int)$claim['id'] ?>/upload"
                          enctype="multipart/form-data" novalidate>
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-5">
                                <label class="form-label small fw-semibold mb-1">Type</label>
                                <select name="doc_type" class="form-select form-select-sm" required>
                                    <option value="">— Choisir —</option>
                                    <option value="devis_reparation">Devis</option>
                                    <option value="rapport_expertise">Rapport d'expertise</option>
                                    <option value="constat_police">Constat de police</option>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label small fw-semibold mb-1">Fichier</label>
                                <input type="file" name="document" class="form-control form-control-sm"
                                       accept=".pdf,.jpg,.jpeg,.png" required>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-sm btn-outline-primary w-100">
                                    <i class="bi bi-upload"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <?php endif; ?>

            </div>
        </div>


        <!-- ════════════════════════════════════════════════════════════════════
             SECTION 3 — Correspondances (lecture + upload client)
             ════════════════════════════════════════════════════════════════════ -->
        <?php $corrDocs = $byCategory['correspondances']; ?>
        <div class="card shadow-sm">
            <div class="card-header d-flex align-items-center gap-2 fw-semibold">
                <i class="bi bi-envelope-paper text-warning"></i>
                Correspondances
                <span class="badge bg-secondary fw-normal ms-1"><?= count($corrDocs) ?></span>
            </div>
            <div class="card-body d-flex flex-column gap-0 p-0">

                <?php if (empty($corrDocs)): ?>
                <div class="px-3 py-3">
                    <span class="text-muted small"><i class="bi bi-dash me-1 opacity-50"></i>Aucun document pour le moment.</span>
                </div>
                <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($corrDocs as $doc): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <div class="me-3 overflow-hidden">
                            <i class="bi <?= docIcon($doc['mime_type']) ?> me-2"></i>
                            <span class="small fw-semibold"><?= htmlspecialchars($doc['original_filename']) ?></span>
                            <div class="text-muted mt-1 fs-xs">
                                <?= htmlspecialchars(str_replace('_', ' ', $doc['doc_type'])) ?>
                                &bull; <?= number_format($doc['file_size'] / 1024, 0) ?>&nbsp;Ko
                                &bull; <?= date('d/m/Y', strtotime($doc['created_at'])) ?>
                                <?php if ($doc['source'] === 'client'): ?>
                                    &bull; <em>déposé par vous</em>
                                <?php elseif ($doc['source'] === 'admin'): ?>
                                    &bull; TILKI
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if ($doc['status'] === 'valide'): ?>
                            <a href="/documents/<?= (int)$doc['id'] ?>/download"
                               class="btn btn-sm btn-outline-primary flex-shrink-0">
                                <i class="bi bi-download me-1"></i>Télécharger
                            </a>
                        <?php else: ?>
                            <span class="badge tk-badge-en_attente flex-shrink-0">en attente</span>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>

                <?php if ($isOpen): ?>
                <div class="border-top px-3 py-3">
                    <p class="small text-muted mb-2">
                        <i class="bi bi-upload me-1"></i>
                        Déposer un courrier ou document de correspondance (PDF ou image, max&nbsp;10&nbsp;Mo) :
                    </p>
                    <form method="post" action="/claims/<?= (int)$claim['id'] ?>/upload"
                          enctype="multipart/form-data" novalidate class="d-flex gap-2 flex-wrap align-items-end">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
                        <input type="hidden" name="doc_type" value="courrier_client">
                        <div class="flex-grow-1">
                            <input type="file" name="document" class="form-control form-control-sm"
                                   accept=".pdf,.jpg,.jpeg,.png" required>
                        </div>
                        <button type="submit" class="btn btn-sm btn-outline-primary flex-shrink-0">
                            <i class="bi bi-upload me-1"></i>Envoyer
                        </button>
                    </form>
                </div>
                <?php endif; ?>

            </div>
        </div>


        <!-- ════════════════════════════════════════════════════════════════════
             SECTION 4 — Règlements et remboursements (admin-only, lecture seule)
             ════════════════════════════════════════════════════════════════════ -->
        <?php $reglDocs = $byCategory['reglements_remboursements']; ?>
        <div class="card shadow-sm">
            <div class="card-header d-flex align-items-center gap-2 fw-semibold">
                <i class="bi bi-cash-coin text-primary"></i>
                Règlements et remboursements
                <span class="badge bg-secondary fw-normal ms-1"><?= count($reglDocs) ?></span>
            </div>
            <?php if (empty($reglDocs)): ?>
                <div class="card-body py-3">
                    <span class="text-muted small"><i class="bi bi-dash me-1 opacity-50"></i>Aucun document pour le moment.</span>
                </div>
            <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($reglDocs as $doc): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <div class="me-3 overflow-hidden">
                            <i class="bi <?= docIcon($doc['mime_type']) ?> me-2"></i>
                            <span class="small fw-semibold"><?= htmlspecialchars($doc['original_filename']) ?></span>
                            <div class="text-muted mt-1 fs-xs">
                                <?= htmlspecialchars(str_replace('_', ' ', $doc['doc_type'])) ?>
                                &bull; <?= number_format($doc['file_size'] / 1024, 0) ?>&nbsp;Ko
                                &bull; <?= date('d/m/Y', strtotime($doc['created_at'])) ?>
                            </div>
                        </div>
                        <?php if ($doc['status'] === 'valide'): ?>
                            <a href="/documents/<?= (int)$doc['id'] ?>/download"
                               class="btn btn-sm btn-outline-primary flex-shrink-0">
                                <i class="bi bi-download me-1"></i>Télécharger
                            </a>
                        <?php else: ?>
                            <span class="badge tk-badge-en_attente flex-shrink-0">en attente</span>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

    </div><!-- /col-lg-8 -->
</div><!-- /row -->

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
