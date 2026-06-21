<?php $pageTitle = 'Clients – Administration TILKI'; ?>
<?php require APP_PATH . '/Views/admin/layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h5 fw-bold mb-0"><i class="bi bi-people me-2"></i>Clients</h2>
    <a href="/admin/clients/create" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Nouveau client
    </a>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 tbl-card-mobile">
            <thead class="table-dark">
                <tr>
                    <th>N° Compte</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Statut</th>
                    <th>Créé le</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($clients)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">Aucun client.</td></tr>
                <?php endif; ?>
                <?php foreach ($clients as $c): ?>
                <tr>
                    <td data-label="N° Compte"><code><?= htmlspecialchars($c['account_number']) ?></code></td>
                    <td data-label="Nom" class="fw-semibold"><?= htmlspecialchars($c['first_name'] . ' ' . $c['last_name']) ?></td>
                    <td data-label="Email"><?= htmlspecialchars($c['email']) ?></td>
                    <td data-label="Téléphone"><?= htmlspecialchars($c['phone'] ?? '—') ?></td>
                    <td data-label="Statut">
                        <span class="badge bg-<?= $c['status'] === 'actif' ? 'success' : ($c['status'] === 'suspendu' ? 'warning text-dark' : 'secondary') ?>">
                            <?= htmlspecialchars($c['status']) ?>
                        </span>
                    </td>
                    <td data-label="Créé le" class="text-muted small"><?= date('d/m/Y', strtotime($c['created_at'])) ?></td>
                    <td data-label="">
                        <a href="/admin/clients/<?= (int)$c['id'] ?>/edit"
                           class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-pencil me-1"></i>Fiche
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require APP_PATH . '/Views/admin/layout/footer.php'; ?>
