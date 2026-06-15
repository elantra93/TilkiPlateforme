<?php
$pageTitle = 'Sinistre ' . htmlspecialchars($claim['claim_number']) . ' – TILKI';

function docIcon(string $mime): string {
    return match(true) {
        $mime === 'application/pdf'        => 'bi-file-earmark-pdf text-danger',
        str_starts_with($mime, 'image/')   => 'bi-file-earmark-image text-info',
        str_contains($mime, 'word')        => 'bi-file-earmark-word text-primary',
        str_contains($mime, 'excel') || str_contains($mime, 'sheet') => 'bi-file-earmark-excel text-success',
        default                            => 'bi-file-earmark text-secondary',
    };
}

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
?>
<?php require APP_PATH . '/Views/layout/header.php'; ?>

<div class="mb-3">
    <a href="/claims" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Retour aux sinistres
    </a>
</div>

<div class="row g-4">

    <!-- ── Détails du sinistre ────────────────────────────────────────────────── -->
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
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
                            <a href="/contracts" class="text-decoration-none">
                                <code class="text-body"><?= htmlspecialchars($claim['policy_number']) ?></code>
                            </a>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </dd>

                    <dt class="col-6 text-muted">Date de survenance</dt>
                    <dd class="col-6"><?= date('d/m/Y', strtotime($claim['occurrence_date'])) ?></dd>

                    <dt class="col-6 text-muted">Dernière mise à jour</dt>
                    <dd class="col-6 text-muted"><?= date('d/m/Y', strtotime($claim['updated_at'])) ?></dd>

                    <dt class="col-6 text-muted">Statut</dt>
                    <dd class="col-6">
                        <span class="badge bg-<?= $claim['status'] === 'ouvert' ? 'danger' : 'success' ?>">
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
    </div>

    <!-- ── Documents ─────────────────────────────────────────────────────────── -->
    <div class="col-lg-8 d-flex flex-column gap-4">

        <?php
        $sections = [
            'declaration'               => ['label' => 'Déclaration',                    'icon' => 'bi-clipboard-data',    'color' => 'text-info'],
            'expertise_devis'           => ['label' => "Rapports d'expertises et devis", 'icon' => 'bi-file-earmark-check', 'color' => 'text-success'],
            'correspondances'           => ['label' => 'Correspondances',                 'icon' => 'bi-envelope-paper',    'color' => 'text-warning'],
            'reglements_remboursements' => ['label' => 'Règlements et remboursements',    'icon' => 'bi-cash-coin',         'color' => 'text-primary'],
        ];
        foreach ($sections as $cat => $meta):
            $docs = $byCategory[$cat];
        ?>
        <div class="card shadow-sm">
            <div class="card-header d-flex align-items-center gap-2 fw-semibold">
                <i class="bi <?= $meta['icon'] ?> <?= $meta['color'] ?>"></i>
                <?= $meta['label'] ?>
                <span class="badge bg-secondary fw-normal ms-1"><?= count($docs) ?></span>
            </div>

            <?php if (empty($docs)): ?>
                <div class="card-body text-muted small py-4 text-center">
                    <i class="bi bi-inbox fs-4 d-block mb-1 opacity-25"></i>
                    Aucun document dans cette section.
                </div>
            <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($docs as $doc): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <div class="me-3 overflow-hidden">
                            <i class="bi <?= docIcon($doc['mime_type']) ?> me-2"></i>
                            <span class="small fw-semibold"><?= htmlspecialchars($doc['original_filename']) ?></span>
                            <div class="text-muted mt-1" style="font-size:.75rem">
                                <?= htmlspecialchars($doc['doc_type']) ?>
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
                            <span class="badge bg-warning text-dark flex-shrink-0">En attente</span>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>

    </div><!-- /col -->
</div><!-- /row -->

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
