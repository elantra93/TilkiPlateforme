<?php $pageTitle = 'Tableau de bord – Administration TILKI'; ?>
<?php require APP_PATH . '/Views/admin/layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 mb-0 fw-bold">Tableau de bord</h2>
    <span class="text-muted small"><?= date('d/m/Y H:i') ?></span>
</div>

<!-- KPIs -->
<div class="row g-3 mb-4">
    <?php
    $kpis = [
        ['label' => 'Clients',       'value' => $stats['clients'],         'icon' => 'people',            'color' => 'primary'],
        ['label' => 'Contrats',      'value' => $stats['contracts'],       'icon' => 'file-earmark-text', 'color' => 'success'],
        ['label' => 'Contrats actifs','value'=> $stats['contracts_actif'], 'icon' => 'check-circle',      'color' => 'success'],
        ['label' => 'Sinistres',     'value' => $stats['claims'],          'icon' => 'exclamation-triangle','color'=> 'warning'],
        ['label' => 'Sinistres ouverts','value'=>$stats['claims_ouverts'], 'icon' => 'fire',              'color' => 'danger'],
        ['label' => 'Documents',     'value' => $stats['documents'],       'icon' => 'paperclip',         'color' => 'info'],
        ['label' => 'En attente',    'value' => $stats['docs_attente'],    'icon' => 'hourglass-split',   'color' => 'warning'],
    ];
    ?>
    <?php foreach ($kpis as $kpi): ?>
    <div class="col-6 col-md-4 col-xl-3">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="stat-icon bg-<?= $kpi['color'] ?> bg-opacity-10">
                    <i class="bi bi-<?= $kpi['icon'] ?> text-<?= $kpi['color'] ?>"></i>
                </div>
                <div>
                    <div class="fs-4 fw-bold lh-1"><?= $kpi['value'] ?></div>
                    <div class="text-muted small"><?= $kpi['label'] ?></div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Dernières tentatives de connexion -->
<div class="card shadow-sm">
    <div class="card-header fw-bold">
        <i class="bi bi-activity me-2"></i>Dernières tentatives de connexion
    </div>
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
                    <td class="x-small text-muted"><?= date('d/m/Y H:i:s', strtotime($a['created_at'])) ?></td>
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
                <?php if (empty($recentAttempts)): ?>
                <tr><td colspan="4" class="text-muted small text-center py-3">Aucune tentative enregistrée.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require APP_PATH . '/Views/admin/layout/footer.php'; ?>
