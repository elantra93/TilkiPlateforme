<?php $pageTitle = 'Tableau de bord – Administration TILKI'; ?>
<?php require APP_PATH . '/Views/admin/layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h2 class="h5 fw-bold mb-0">Tableau de bord</h2>
        <p class="text-muted small mb-0"><?= date('l d F Y') ?></p>
    </div>
</div>

<!-- KPIs -->
<div class="row g-3 mb-4">
    <?php
    $kpis = [
        ['label' => 'Clients',          'value' => $stats['clients'],          'icon' => 'people',              'color' => 'primary',  'href' => '/admin/clients'],
        ['label' => 'Contrats actifs',  'value' => $stats['contracts_actif'],  'icon' => 'check-circle',        'color' => 'success',  'href' => '/admin/contracts'],
        ['label' => 'Contrats',         'value' => $stats['contracts'],        'icon' => 'file-earmark-text',   'color' => 'secondary','href' => '/admin/contracts'],
        ['label' => 'Sinistres ouverts','value' => $stats['claims_ouverts'],   'icon' => 'fire',                'color' => 'danger',   'href' => '/admin/claims'],
        ['label' => 'Sinistres',        'value' => $stats['claims'],           'icon' => 'exclamation-triangle','color' => 'warning',  'href' => '/admin/claims'],
        ['label' => 'Paiements en att.', 'value' => $stats['payments_pending'], 'icon' => 'cash-coin',           'color' => 'warning',  'href' => '/admin/payments/pending'],
        ['label' => 'Docs en attente',  'value' => $stats['docs_attente'],     'icon' => 'hourglass-split',     'color' => 'info',     'href' => '/admin/documents/pending'],
        ['label' => 'Documents',        'value' => $stats['documents'],        'icon' => 'paperclip',           'color' => 'secondary','href' => '/admin/documents/pending'],
    ];
    ?>
    <?php foreach ($kpis as $kpi): ?>
    <div class="col-6 col-md-4 col-xl-3">
        <a href="<?= $kpi['href'] ?>" class="card shadow-sm h-100 text-decoration-none">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="stat-icon bg-<?= $kpi['color'] ?> bg-opacity-10 flex-shrink-0">
                    <i class="bi bi-<?= $kpi['icon'] ?> text-<?= $kpi['color'] ?>"></i>
                </div>
                <div class="min-w-0">
                    <div class="fs-4 fw-bold lh-1 text-body"><?= $kpi['value'] ?></div>
                    <div class="text-muted small text-truncate"><?= $kpi['label'] ?></div>
                </div>
            </div>
        </a>
    </div>
    <?php endforeach; ?>
</div>

<!-- Dernières tentatives de connexion -->
<div class="card shadow-sm">
    <div class="card-header d-flex align-items-center gap-2 fw-semibold">
        <i class="bi bi-activity text-primary"></i>Dernières tentatives de connexion
    </div>
    <?php if (empty($recentAttempts)): ?>
    <div class="card-body text-center text-muted py-4">
        <i class="bi bi-shield-check fs-2 d-block mb-2 opacity-25"></i>
        Aucune tentative enregistrée.
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Identifiant</th>
                    <th>IP</th>
                    <th>Résultat</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentAttempts as $a): ?>
                <tr>
                    <td class="fs-xxs text-muted"><?= date('d/m/Y H:i:s', strtotime($a['created_at'])) ?></td>
                    <td class="small"><code><?= htmlspecialchars($a['identifier']) ?></code></td>
                    <td class="small"><?= htmlspecialchars($a['ip']) ?></td>
                    <td>
                        <?php if ($a['success']): ?>
                            <span class="badge bg-success">Succès</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Échec</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php require APP_PATH . '/Views/admin/layout/footer.php'; ?>
