<?php $pageTitle = 'Mes sinistres – TILKI'; ?>
<?php require APP_PATH . '/Views/layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 mb-0 fw-bold"><i class="bi bi-exclamation-triangle me-2"></i>Mes sinistres</h2>
    <a href="/claims/declare" class="btn btn-danger">
        <i class="bi bi-plus-lg me-1"></i>Déclarer un sinistre
    </a>
</div>

<?php if (empty($claims)): ?>
    <div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>Aucun sinistre enregistré.</div>
<?php else: ?>
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>N° Sinistre</th>
                        <th>Branche</th>
                        <th>Assureur</th>
                        <th>Survenance</th>
                        <th>N° Police</th>
                        <th>Statut</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($claims as $cl): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($cl['claim_number']) ?></code></td>
                            <td><?= htmlspecialchars($cl['branche']) ?></td>
                            <td><?= htmlspecialchars($cl['insurer']) ?></td>
                            <td><?= date('d/m/Y', strtotime($cl['occurrence_date'])) ?></td>
                            <td><?= htmlspecialchars($cl['policy_number'] ?? '—') ?></td>
                            <td>
                                <span class="badge bg-<?= $cl['status'] === 'ouvert' ? 'danger' : 'success' ?>">
                                    <?= htmlspecialchars($cl['status']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="/claims/<?= $cl['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye me-1"></i>Détail
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
