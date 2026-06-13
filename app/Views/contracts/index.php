<?php $pageTitle = 'Mes contrats – TILKI'; ?>
<?php require APP_PATH . '/Views/layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 mb-0 fw-bold"><i class="bi bi-file-earmark-text me-2"></i>Mes contrats</h2>
</div>

<?php if (empty($contracts)): ?>
    <div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>Aucun contrat trouvé.</div>
<?php else: ?>
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Branche</th>
                        <th>N° Police</th>
                        <th>Assureur</th>
                        <th>Début</th>
                        <th>Expiration</th>
                        <th>Prime TTC</th>
                        <th>Statut</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contracts as $c): ?>
                        <tr>
                            <td class="fw-semibold"><?= htmlspecialchars($c['branche']) ?></td>
                            <td><code><?= htmlspecialchars($c['policy_number']) ?></code></td>
                            <td><?= htmlspecialchars($c['insurer']) ?></td>
                            <td><?= date('d/m/Y', strtotime($c['effective_date'])) ?></td>
                            <td><?= date('d/m/Y', strtotime($c['expiry_date'])) ?></td>
                            <td><?= number_format((float)$c['premium_total'], 0, ',', ' ') ?> <?= htmlspecialchars($c['currency']) ?></td>
                            <td>
                                <span class="badge bg-<?= $c['status'] === 'actif' ? 'success' : 'secondary' ?>">
                                    <?= htmlspecialchars($c['status']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="/contracts/<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary">
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
