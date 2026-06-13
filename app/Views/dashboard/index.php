<?php $pageTitle = 'Tableau de bord – TILKI'; ?>
<?php require APP_PATH . '/Views/layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 mb-0 fw-bold">
            Bonjour, <?= htmlspecialchars($client['first_name']) ?>&nbsp;!
        </h2>
        <p class="text-muted small mb-0">Compte n° <code><?= htmlspecialchars($client['account_number']) ?></code></p>
    </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card h-100 shadow-sm position-relative">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-primary bg-opacity-10">
                    <i class="bi bi-file-earmark-text text-primary"></i>
                </div>
                <div>
                    <div class="fs-3 fw-bold lh-1"><?= count($contracts) ?></div>
                    <div class="text-muted small">Contrat(s)</div>
                </div>
            </div>
            <a href="/contracts" class="stretched-link"></a>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100 shadow-sm position-relative">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-warning bg-opacity-10">
                    <i class="bi bi-exclamation-triangle text-warning"></i>
                </div>
                <div>
                    <div class="fs-3 fw-bold lh-1"><?= count($claims) ?></div>
                    <div class="text-muted small">Sinistre(s)</div>
                </div>
            </div>
            <a href="/claims" class="stretched-link"></a>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100 shadow-sm position-relative">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-success bg-opacity-10">
                    <i class="bi bi-check-circle text-success"></i>
                </div>
                <div>
                    <div class="fs-3 fw-bold lh-1">
                        <?= count(array_filter($contracts, fn($c) => $c['status'] === 'actif')) ?>
                    </div>
                    <div class="text-muted small">Actif(s)</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card h-100 shadow-sm position-relative">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-danger bg-opacity-10">
                    <i class="bi bi-fire text-danger"></i>
                </div>
                <div>
                    <div class="fs-3 fw-bold lh-1">
                        <?= count(array_filter($claims, fn($cl) => $cl['status'] === 'ouvert')) ?>
                    </div>
                    <div class="text-muted small">Ouvert(s)</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Derniers contrats + sinistres -->
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-file-earmark-text me-2"></i>Derniers contrats</span>
                <a href="/contracts" class="btn btn-sm btn-outline-primary">Voir tout</a>
            </div>
            <?php if (empty($contracts)): ?>
                <div class="card-body text-muted small">Aucun contrat.</div>
            <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach (array_slice($contracts, 0, 5) as $c): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <div>
                                <div class="fw-semibold small"><?= htmlspecialchars($c['branche']) ?></div>
                                <div class="text-muted x-small">
                                    <?= htmlspecialchars($c['policy_number']) ?> &bull; <?= htmlspecialchars($c['insurer']) ?>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-<?= $c['status'] === 'actif' ? 'success' : 'secondary' ?>">
                                    <?= htmlspecialchars($c['status']) ?>
                                </span>
                                <a href="/contracts/<?= $c['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-exclamation-triangle me-2"></i>Derniers sinistres</span>
                <a href="/claims" class="btn btn-sm btn-outline-warning">Voir tout</a>
            </div>
            <?php if (empty($claims)): ?>
                <div class="card-body text-muted small">Aucun sinistre.</div>
            <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach (array_slice($claims, 0, 5) as $cl): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <div>
                                <div class="fw-semibold small"><?= htmlspecialchars($cl['claim_number']) ?></div>
                                <div class="text-muted x-small">
                                    <?= htmlspecialchars($cl['branche']) ?> &bull;
                                    <?= date('d/m/Y', strtotime($cl['occurrence_date'])) ?>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-<?= $cl['status'] === 'ouvert' ? 'danger' : 'success' ?>">
                                    <?= htmlspecialchars($cl['status']) ?>
                                </span>
                                <a href="/claims/<?= $cl['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
