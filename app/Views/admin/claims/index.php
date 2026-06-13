<?php $pageTitle = 'Sinistres – Administration TILKI'; ?>
<?php require APP_PATH . '/Views/admin/layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h5 fw-bold mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Sinistres</h2>
    <a href="/admin/claims/create" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Nouveau sinistre
    </a>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Client</th>
                    <th>N° Sinistre</th>
                    <th>Branche</th>
                    <th>Assureur</th>
                    <th>N° Police</th>
                    <th>Date survenance</th>
                    <th>Statut</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($claims)): ?>
                <tr><td colspan="8" class="text-center text-muted py-4">Aucun sinistre.</td></tr>
                <?php endif; ?>
                <?php foreach ($claims as $cl): ?>
                <tr>
                    <td>
                        <div class="fw-semibold small"><?= htmlspecialchars($cl['first_name'] . ' ' . $cl['last_name']) ?></div>
                        <div class="text-muted" style="font-size:.75rem"><code><?= htmlspecialchars($cl['account_number']) ?></code></div>
                    </td>
                    <td><code class="text-body"><?= htmlspecialchars($cl['claim_number']) ?></code></td>
                    <td><?= htmlspecialchars($cl['branche']) ?></td>
                    <td><?= htmlspecialchars($cl['insurer']) ?></td>
                    <td><?= $cl['policy_number'] ? '<code>' . htmlspecialchars($cl['policy_number']) . '</code>' : '<span class="text-muted">—</span>' ?></td>
                    <td><?= date('d/m/Y', strtotime($cl['occurrence_date'])) ?></td>
                    <td>
                        <span class="badge bg-<?= $cl['status'] === 'ouvert' ? 'danger' : 'success' ?>">
                            <?= htmlspecialchars($cl['status']) ?>
                        </span>
                    </td>
                    <td>
                        <a href="/admin/claims/<?= (int)$cl['id'] ?>/edit"
                           class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-pencil"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require APP_PATH . '/Views/admin/layout/footer.php'; ?>
