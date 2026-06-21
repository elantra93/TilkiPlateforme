<?php $pageTitle = 'Sinistres – Administration TILKI'; ?>
<?php require APP_PATH . '/Views/admin/layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h5 fw-bold mb-0">
        <i class="bi bi-exclamation-triangle me-2"></i>Sinistres
        <?php if (($total ?? 0) > 0): ?>
            <span class="text-muted fw-normal small ms-1">(<?= $total ?>)</span>
        <?php endif; ?>
    </h2>
    <a href="/admin/claims/create" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Nouveau sinistre
    </a>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 tbl-card-mobile">
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
                <tr class="tbl-row-link" data-href="/admin/claims/<?= (int)$cl['id'] ?>/edit">
                    <td data-label="Client">
                        <div class="fw-semibold small"><?= htmlspecialchars($cl['first_name'] . ' ' . $cl['last_name']) ?></div>
                        <div class="text-muted" style="font-size:.75rem"><code><?= htmlspecialchars($cl['account_number']) ?></code></div>
                    </td>
                    <td data-label="N° Sinistre"><code class="text-body"><?= htmlspecialchars($cl['claim_number']) ?></code></td>
                    <td data-label="Branche"><?= htmlspecialchars($cl['branche']) ?></td>
                    <td data-label="Assureur"><?= htmlspecialchars($cl['insurer']) ?></td>
                    <td data-label="N° Police"><?= $cl['policy_number'] ? '<code>' . htmlspecialchars($cl['policy_number']) . '</code>' : '<span class="text-muted">—</span>' ?></td>
                    <td data-label="Survenance"><?= date('d/m/Y', strtotime($cl['occurrence_date'])) ?></td>
                    <td data-label="Statut">
                        <span class="badge bg-<?= $cl['status'] === 'ouvert' ? 'danger' : 'success' ?>">
                            <?= htmlspecialchars($cl['status']) ?>
                        </span>
                    </td>
                    <td data-label="">
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

<?php if (($pages ?? 1) > 1): ?>
<nav class="d-flex justify-content-center mt-3" aria-label="Pagination sinistres">
    <ul class="pagination pagination-sm">
        <?php if ($page > 1): ?>
        <li class="page-item">
            <a class="page-link" href="?page=<?= $page - 1 ?>" aria-label="Précédent">‹</a>
        </li>
        <?php endif; ?>
        <?php for ($i = max(1, $page - 2); $i <= min($pages, $page + 2); $i++): ?>
        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
        <?php if ($page < $pages): ?>
        <li class="page-item">
            <a class="page-link" href="?page=<?= $page + 1 ?>" aria-label="Suivant">›</a>
        </li>
        <?php endif; ?>
    </ul>
</nav>
<?php endif; ?>

<?php require APP_PATH . '/Views/admin/layout/footer.php'; ?>
