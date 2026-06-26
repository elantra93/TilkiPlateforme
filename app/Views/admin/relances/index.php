<?php $pageTitle = 'Échéancier & Relances – Administration TILKI'; ?>
<?php require APP_PATH . '/Views/admin/layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h5 fw-bold mb-0"><i class="bi bi-bell me-2"></i>Échéancier & Relances</h2>
    <form method="post" action="/admin/relances/run"
          data-confirm="Lancer toutes les relances du jour ?">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
        <button type="submit" class="btn btn-sm btn-primary">
            <i class="bi bi-send me-1"></i>Lancer les relances du jour
        </button>
    </form>
</div>

<?php
// Group by urgency
$overdue = array_filter($contracts, fn($c) => (int)$c['days_until_expiry'] < 0);
$urgent  = array_filter($contracts, fn($c) => (int)$c['days_until_expiry'] >= 0 && (int)$c['days_until_expiry'] <= 7);
$soon    = array_filter($contracts, fn($c) => (int)$c['days_until_expiry'] > 7 && (int)$c['days_until_expiry'] <= 30);
$later   = array_filter($contracts, fn($c) => (int)$c['days_until_expiry'] > 30);

function relanceGroup(string $title, array $rows, string $color, string $icon, array $typeLabels, string $csrf): void {
    if (empty($rows)) return;
    ?>
<div class="card shadow-sm mb-4">
    <div class="card-header d-flex align-items-center gap-2 fw-semibold text-<?= $color ?>">
        <i class="bi bi-<?= $icon ?>"></i><?= $title ?>
        <span class="badge bg-<?= $color ?>-subtle text-<?= $color ?> border border-<?= $color ?>-subtle ms-1"><?= count($rows) ?></span>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0 small">
            <thead class="table-light">
                <tr>
                    <th>Client</th>
                    <th>Police / Branche</th>
                    <th>Assureur</th>
                    <th>Échéance</th>
                    <th>J. restants</th>
                    <th>Dernière relance</th>
                    <th>Relance du jour</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $c):
                $days     = (int)$c['days_until_expiry'];
                $daysText = $days >= 0 ? $days . ' j.' : abs($days) . ' j. dépassé';
                $dayCls   = $days < 0 ? 'text-danger fw-bold' : ($days <= 7 ? 'text-warning fw-bold' : 'text-success');
                $dueType  = $c['due_type'] ?? null;
                $alreadySent = (bool)($c['already_sent'] ?? false);
            ?>
            <tr>
                <td>
                    <span class="fw-semibold"><?= htmlspecialchars($c['first_name'] . ' ' . $c['last_name']) ?></span>
                    <br><span class="text-muted font-monospace" style="font-size:0.75rem"><?= htmlspecialchars($c['account_number'] ?? '') ?></span>
                </td>
                <td>
                    <span class="font-monospace"><?= htmlspecialchars($c['policy_number']) ?></span>
                    <br><span class="text-muted"><?= htmlspecialchars($c['branche']) ?></span>
                </td>
                <td class="text-muted"><?= htmlspecialchars($c['insurer'] ?? '—') ?></td>
                <td><?= date('d/m/Y', strtotime($c['expiry_date'])) ?></td>
                <td class="<?= $dayCls ?>"><?= $daysText ?></td>
                <td class="text-muted">
                    <?php if ($c['relance_derniere_at']): ?>
                    <?= date('d/m/Y', strtotime($c['relance_derniere_at'])) ?>
                    <?php if ($c['relance_statut'] === 'envoyee'): ?>
                    <span class="badge bg-success-subtle text-success border border-success-subtle ms-1">OK</span>
                    <?php elseif ($c['relance_statut'] === 'echouee'): ?>
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle ms-1">Échec</span>
                    <?php endif; ?>
                    <?php else: ?>
                    <span class="text-muted fst-italic">Aucune</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($dueType): ?>
                    <?php if ($alreadySent): ?>
                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                        <i class="bi bi-check2 me-1"></i><?= htmlspecialchars($typeLabels[$dueType]) ?>
                    </span>
                    <?php else: ?>
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                        <i class="bi bi-clock me-1"></i><?= htmlspecialchars($typeLabels[$dueType]) ?>
                    </span>
                    <?php endif; ?>
                    <?php else: ?>
                    <span class="text-muted fst-italic small">—</span>
                    <?php endif; ?>
                </td>
                <td class="text-end">
                    <a href="/admin/relances/<?= (int)$c['id'] ?>"
                       class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-clock-history"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
    <?php
}

relanceGroup('Contrats échus (30 derniers jours)', $overdue, 'danger', 'exclamation-triangle-fill', $typeLabels, $csrf);
relanceGroup('Urgents — expiration dans 7 jours ou moins', $urgent, 'warning', 'alarm', $typeLabels, $csrf);
relanceGroup('Bientôt — 8 à 30 jours', $soon, 'primary', 'calendar-event', $typeLabels, $csrf);
relanceGroup('À venir — 31 à 60 jours', $later, 'secondary', 'calendar3', $typeLabels, $csrf);
?>

<?php if (empty($contracts)): ?>
<div class="card shadow-sm">
    <div class="card-body text-center py-5 text-muted">
        <i class="bi bi-check-circle fs-2 d-block mb-2 opacity-50"></i>
        Aucun contrat en approche d'échéance dans les 60 prochains jours.
    </div>
</div>
<?php endif; ?>

<?php require APP_PATH . '/Views/admin/layout/footer.php'; ?>
