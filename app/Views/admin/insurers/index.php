<?php $pageTitle = 'Assureurs – Administration TILKI'; ?>
<?php require APP_PATH . '/Views/admin/layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h5 fw-bold mb-0">
        <i class="bi bi-building me-2"></i>Référentiel assureurs
    </h2>
    <a href="/admin/insurers/create" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Ajouter un assureur
    </a>
</div>

<?php
$active   = array_filter($insurers, fn($i) => $i['is_active']);
$inactive = array_filter($insurers, fn($i) => !$i['is_active']);
?>

<!-- Actifs -->
<div class="card mb-4">
    <div class="card-header d-flex align-items-center gap-2 fw-semibold">
        <i class="bi bi-check-circle text-success"></i>
        Compagnies partenaires
        <span class="badge bg-success-subtle text-success border border-success-subtle ms-1"><?= count($active) ?></span>
    </div>
    <?php if (empty($active)): ?>
    <div class="card-body text-muted small text-center py-4">
        <i class="bi bi-dash opacity-50 me-1"></i>Aucun assureur actif.
    </div>
    <?php else: ?>
    <ul class="list-group list-group-flush">
        <?php foreach ($active as $ins): ?>
        <li class="list-group-item px-4 py-3">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                <div class="min-w-0">
                    <div class="fw-semibold text-body mb-1">
                        <?= htmlspecialchars($ins['name']) ?>
                        <?php if (!empty($ins['short_name'])): ?>
                        <span class="text-muted fw-normal small ms-1 font-mono"><?= htmlspecialchars($ins['short_name']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex flex-wrap gap-1 mt-1">
                        <?php if (!empty($ins['branches'])): ?>
                            <?php foreach ($ins['branches'] as $b): ?>
                            <span class="badge tk-branch-badge"><?= htmlspecialchars($b) ?></span>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span class="small text-muted fst-italic">Aucune branche renseignée</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="d-flex gap-1 flex-shrink-0">
                    <a href="/admin/insurers/<?= (int)$ins['id'] ?>/edit"
                       class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <form method="post" action="/admin/insurers/<?= (int)$ins['id'] ?>/toggle"
                          class="d-inline"
                          onsubmit="return confirm('Désactiver cet assureur ?')">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <button type="submit" class="btn btn-sm btn-outline-warning" title="Désactiver">
                            <i class="bi bi-toggle-on"></i>
                        </button>
                    </form>
                </div>
            </div>
        </li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>
</div>

<!-- Inactifs -->
<?php if (!empty($inactive)): ?>
<div class="card">
    <div class="card-header d-flex align-items-center gap-2 fw-semibold text-muted">
        <i class="bi bi-slash-circle"></i>
        Inactifs
        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle ms-1"><?= count($inactive) ?></span>
        <span class="small fw-normal ms-1">(masqués dans les formulaires de contrats)</span>
    </div>
    <ul class="list-group list-group-flush">
        <?php foreach ($inactive as $ins): ?>
        <li class="list-group-item px-4 py-3 opacity-60">
            <div class="d-flex justify-content-between align-items-center gap-3">
                <div>
                    <div class="small fw-semibold text-muted">
                        <?= htmlspecialchars($ins['name']) ?>
                        <?php if (!empty($ins['short_name'])): ?>
                        <span class="fw-normal ms-1 font-mono"><?= htmlspecialchars($ins['short_name']) ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($ins['branches'])): ?>
                    <div class="d-flex flex-wrap gap-1 mt-1">
                        <?php foreach ($ins['branches'] as $b): ?>
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle" style="font-size:.65rem"><?= htmlspecialchars($b) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="d-flex gap-1 flex-shrink-0">
                    <a href="/admin/insurers/<?= (int)$ins['id'] ?>/edit"
                       class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <form method="post" action="/admin/insurers/<?= (int)$ins['id'] ?>/toggle"
                          class="d-inline"
                          onsubmit="return confirm('Réactiver cet assureur ?')">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <button type="submit" class="btn btn-sm btn-outline-success" title="Réactiver">
                            <i class="bi bi-toggle-off"></i>
                        </button>
                    </form>
                </div>
            </div>
        </li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<?php require APP_PATH . '/Views/admin/layout/footer.php'; ?>
