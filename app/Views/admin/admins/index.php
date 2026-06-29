<?php $pageTitle = 'Comptes administrateurs – TILKI'; ?>
<?php require APP_PATH . '/Views/admin/layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h5 fw-bold mb-0">
        <i class="bi bi-person-badge me-2"></i>Comptes administrateurs
    </h2>
    <a href="/admin/admins/create" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Nouveau compte
    </a>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 tbl-card-mobile">
            <thead class="table-dark">
                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Créé le</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($admins)): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">Aucun compte.</td></tr>
                <?php endif; ?>
                <?php foreach ($admins as $a): ?>
                <tr>
                    <td data-label="Nom" class="fw-semibold"><?= htmlspecialchars($a['name']) ?></td>
                    <td data-label="Email"><?= htmlspecialchars($a['email']) ?></td>
                    <td data-label="Rôle">
                        <?php
                        $badgeClass = match($a['role']) {
                            'superadmin' => 'bg-danger',
                            'admin'      => 'bg-primary',
                            default      => 'bg-secondary',
                        };
                        ?>
                        <span class="badge <?= $badgeClass ?>">
                            <?= htmlspecialchars($a['role']) ?>
                        </span>
                    </td>
                    <td data-label="Créé le" class="text-muted small">
                        <?= date('d/m/Y', strtotime($a['created_at'])) ?>
                    </td>
                    <td data-label="">
                        <?php if ($a['role'] !== 'superadmin'): ?>
                        <a href="/admin/admins/<?= (int)$a['id'] ?>/edit"
                           class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <?php else: ?>
                        <span class="text-muted small">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require APP_PATH . '/Views/admin/layout/footer.php'; ?>
