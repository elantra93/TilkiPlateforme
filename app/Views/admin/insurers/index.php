<?php $pageTitle = 'Assureurs – Administration TILKI'; ?>
<?php require APP_PATH . '/Views/admin/layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h5 fw-bold mb-0"><i class="bi bi-building me-2"></i>Assureurs</h2>
    <a href="/admin/insurers/create" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Ajouter un assureur
    </a>
</div>

<?php
$active   = array_filter($insurers, fn($i) => $i['is_active']);
$inactive = array_filter($insurers, fn($i) => !$i['is_active']);
?>

<!-- Actifs -->
<div class="card shadow-sm mb-4">
    <div class="card-header d-flex align-items-center gap-2 fw-semibold">
        <i class="bi bi-check-circle text-success"></i>
        Actifs
        <span class="badge bg-success-subtle text-success border border-success-subtle ms-1"><?= count($active) ?></span>
    </div>
    <?php if (empty($active)): ?>
    <div class="card-body text-muted small text-center py-4">
        <i class="bi bi-dash opacity-50 me-1"></i>Aucun assureur actif.
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Dénomination</th>
                    <th>Sigle</th>
                    <th>Pays</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($active as $ins): ?>
            <tr>
                <td class="fw-semibold small"><?= htmlspecialchars($ins['name']) ?></td>
                <td class="small text-muted font-monospace"><?= htmlspecialchars($ins['short_name'] ?? '—') ?></td>
                <td class="small text-muted"><?= htmlspecialchars($ins['country']) ?></td>
                <td class="text-end text-nowrap">
                    <a href="/admin/insurers/<?= (int)$ins['id'] ?>/edit"
                       class="btn btn-sm btn-outline-secondary me-1">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <form method="post" action="/admin/insurers/<?= (int)$ins['id'] ?>/toggle"
                          class="d-inline" data-confirm="Désactiver cet assureur ?">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <button type="submit" class="btn btn-sm btn-outline-warning"
                                title="Désactiver">
                            <i class="bi bi-toggle-on"></i>
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- Inactifs -->
<?php if (!empty($inactive)): ?>
<div class="card shadow-sm">
    <div class="card-header d-flex align-items-center gap-2 fw-semibold text-muted">
        <i class="bi bi-slash-circle"></i>
        Inactifs (masqués dans les formulaires)
        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle ms-1"><?= count($inactive) ?></span>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr><th>Dénomination</th><th>Sigle</th><th>Pays</th><th></th></tr>
            </thead>
            <tbody>
            <?php foreach ($inactive as $ins): ?>
            <tr class="text-muted">
                <td class="small"><?= htmlspecialchars($ins['name']) ?></td>
                <td class="small font-monospace"><?= htmlspecialchars($ins['short_name'] ?? '—') ?></td>
                <td class="small"><?= htmlspecialchars($ins['country']) ?></td>
                <td class="text-end text-nowrap">
                    <a href="/admin/insurers/<?= (int)$ins['id'] ?>/edit"
                       class="btn btn-sm btn-outline-secondary me-1">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <form method="post" action="/admin/insurers/<?= (int)$ins['id'] ?>/toggle"
                          class="d-inline" data-confirm="Réactiver cet assureur ?">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <button type="submit" class="btn btn-sm btn-outline-success"
                                title="Réactiver">
                            <i class="bi bi-toggle-off"></i>
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php require APP_PATH . '/Views/admin/layout/footer.php'; ?>
