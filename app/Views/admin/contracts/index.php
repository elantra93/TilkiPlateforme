<?php $pageTitle = 'Contrats – Administration TILKI'; ?>
<?php require APP_PATH . '/Views/admin/layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h5 fw-bold mb-0"><i class="bi bi-file-earmark-text me-2"></i>Contrats</h2>
    <a href="/admin/contracts/create" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Nouveau contrat
    </a>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Client</th>
                    <th>Branche</th>
                    <th>N° Police</th>
                    <th>Assureur</th>
                    <th>Date d'effet</th>
                    <th>Date d'échéance</th>
                    <th class="text-end">Restant dû</th>
                    <th>Statut</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($contracts)): ?>
                <tr><td colspan="9" class="text-center text-muted py-4">Aucun contrat.</td></tr>
                <?php endif; ?>
                <?php foreach ($contracts as $c): ?>
                <tr>
                    <td>
                        <div class="fw-semibold small"><?= htmlspecialchars($c['first_name'] . ' ' . $c['last_name']) ?></div>
                        <div class="text-muted" style="font-size:.75rem"><code><?= htmlspecialchars($c['account_number']) ?></code></div>
                    </td>
                    <td><?= htmlspecialchars($c['branche']) ?></td>
                    <td><code class="text-body"><?= htmlspecialchars($c['policy_number']) ?></code></td>
                    <td><?= htmlspecialchars($c['insurer']) ?></td>
                    <td><?= date('d/m/Y', strtotime($c['effective_date'])) ?></td>
                    <td><?= date('d/m/Y', strtotime($c['expiry_date'])) ?></td>
                    <td class="text-end">
                        <?php if ((float)$c['premium_due'] <= 0): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle fw-normal">À jour</span>
                        <?php else: ?>
                            <span class="text-danger fw-semibold small">
                                <?= number_format((float)$c['premium_due'], 0, ',', ' ') ?> <?= htmlspecialchars($c['currency']) ?>
                            </span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge bg-<?= $c['status'] === 'actif' ? 'success' : 'secondary' ?>">
                            <?= htmlspecialchars($c['status']) ?>
                        </span>
                    </td>
                    <td>
                        <a href="/admin/contracts/<?= (int)$c['id'] ?>/edit"
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
