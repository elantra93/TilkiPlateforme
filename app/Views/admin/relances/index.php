<?php
$pageTitle = 'Échéancier & Relances – Administration TILKI';

// Groupes d'urgence
$overdue = array_values(array_filter($contracts, fn($c) => (int)$c['days_until_expiry'] < 0));
$urgent  = array_values(array_filter($contracts, fn($c) => (int)$c['days_until_expiry'] >= 0 && (int)$c['days_until_expiry'] <= 7));
$soon    = array_values(array_filter($contracts, fn($c) => (int)$c['days_until_expiry'] > 7  && (int)$c['days_until_expiry'] <= 30));
$later   = array_values(array_filter($contracts, fn($c) => (int)$c['days_until_expiry'] > 30));

function clientName(array $c): string {
    if (($c['account_type'] ?? '') === 'entreprise' && !empty($c['company_name'])) {
        return htmlspecialchars($c['company_name']);
    }
    return htmlspecialchars(trim(($c['first_name'] ?? '') . ' ' . ($c['last_name'] ?? '')));
}
?>
<?php require APP_PATH . '/Views/admin/layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h5 fw-bold mb-0"><i class="bi bi-bell me-2"></i>Échéancier & Relances</h2>
    <form method="post" action="/admin/relances/run"
          data-confirm="Lancer toutes les relances du jour pour les contrats en attente ?">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
        <button type="submit" class="btn btn-sm btn-primary">
            <i class="bi bi-send me-1"></i>Lancer les relances du jour
        </button>
    </form>
</div>

<!-- KPI cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card shadow-sm border-0 tk-kpi-card <?= count($overdue) ? 'tk-kpi-danger' : '' ?>">
            <div class="card-body py-3">
                <div class="tk-kpi-label">Échus</div>
                <div class="tk-kpi-value"><?= count($overdue) ?></div>
                <div class="tk-kpi-sub">contrats</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm border-0 tk-kpi-card <?= count($urgent) ? 'tk-kpi-warning' : '' ?>">
            <div class="card-body py-3">
                <div class="tk-kpi-label">Urgents ≤ 7 j.</div>
                <div class="tk-kpi-value"><?= count($urgent) ?></div>
                <div class="tk-kpi-sub">contrats</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm border-0 tk-kpi-card <?= count($soon) ? 'tk-kpi-ok' : '' ?>">
            <div class="card-body py-3">
                <div class="tk-kpi-label">Bientôt 8–30 j.</div>
                <div class="tk-kpi-value"><?= count($soon) ?></div>
                <div class="tk-kpi-sub">contrats</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm border-0 tk-kpi-card">
            <div class="card-body py-3">
                <div class="tk-kpi-label">À venir 31–60 j.</div>
                <div class="tk-kpi-value"><?= count($later) ?></div>
                <div class="tk-kpi-sub">contrats</div>
            </div>
        </div>
    </div>
</div>

<?php if (empty($contracts)): ?>
<div class="card shadow-sm">
    <div class="card-body text-center py-5 text-muted">
        <i class="bi bi-check-circle fs-2 d-block mb-2 opacity-50"></i>
        Aucun contrat en approche d'échéance dans les 60 prochains jours.
    </div>
</div>
<?php endif; ?>

<?php
function relanceGroup(
    string $title, array $rows, string $colorClass, string $icon,
    array $typeLabels, string $csrf
): void {
    if (empty($rows)) return;
    ?>
<div class="card shadow-sm mb-4">
    <div class="card-header d-flex align-items-center gap-2 fw-semibold text-<?= $colorClass ?>">
        <i class="bi bi-<?= $icon ?>"></i><?= htmlspecialchars($title) ?>
        <span class="badge bg-<?= $colorClass ?>-subtle text-<?= $colorClass ?> border border-<?= $colorClass ?>-subtle ms-1"><?= count($rows) ?></span>
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
                    <th>Palier du jour</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $c):
                $days        = (int)$c['days_until_expiry'];
                $daysText    = $days >= 0 ? $days . ' j.' : abs($days) . ' j. dépassé';
                $dayCls      = $days < 0 ? 'text-danger fw-bold' : ($days <= 7 ? 'text-warning fw-bold' : 'text-success');
                $dueType     = $c['due_type'] ?? null;
                $alreadySent = (bool)($c['already_sent'] ?? false);
            ?>
            <tr>
                <td>
                    <span class="fw-semibold"><?= clientName($c) ?></span>
                    <br><span class="text-muted font-monospace" style="font-size:0.72rem"><?= htmlspecialchars($c['account_number'] ?? '') ?></span>
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
                            <i class="bi bi-check2 me-1"></i><?= htmlspecialchars($typeLabels[$dueType] ?? $dueType) ?>
                        </span>
                        <?php else: ?>
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                            <i class="bi bi-clock me-1"></i><?= htmlspecialchars($typeLabels[$dueType] ?? $dueType) ?>
                        </span>
                        <?php endif; ?>
                    <?php else: ?>
                    <span class="text-muted fst-italic">—</span>
                    <?php endif; ?>
                </td>
                <td class="text-end text-nowrap">
                    <button type="button" class="btn btn-sm btn-outline-primary me-1"
                            data-bs-toggle="modal" data-bs-target="#notifierModal"
                            data-contract-id="<?= (int)$c['id'] ?>"
                            data-policy="<?= htmlspecialchars($c['policy_number'], ENT_QUOTES) ?>"
                            data-client="<?= htmlspecialchars(clientName($c), ENT_QUOTES) ?>"
                            data-due-type="<?= htmlspecialchars($dueType ?? '', ENT_QUOTES) ?>">
                        <i class="bi bi-send me-1"></i>Notifier
                    </button>
                    <a href="/admin/relances/<?= (int)$c['id'] ?>"
                       class="btn btn-sm btn-outline-secondary"
                       title="Historique des relances">
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

relanceGroup('Contrats échus (30 derniers jours)', $overdue, 'danger',   'exclamation-triangle-fill', $typeLabels, $csrf);
relanceGroup('Urgents — expiration dans 7 jours ou moins',  $urgent,  'warning',  'alarm',              $typeLabels, $csrf);
relanceGroup('Bientôt — 8 à 30 jours',                     $soon,    'primary',  'calendar-event',     $typeLabels, $csrf);
relanceGroup('À venir — 31 à 60 jours',                    $later,   'secondary','calendar3',          $typeLabels, $csrf);
?>

<!-- ── Modal Notifier ──────────────────────────────────────────────────────── -->
<div class="modal fade" id="notifierModal" tabindex="-1" aria-labelledby="notifierModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="notifierModalLabel">
                    <i class="bi bi-send me-2 text-primary"></i>Envoyer une relance
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" id="notifierForm" action="" novalidate>
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
                <div class="modal-body">
                    <p class="text-muted small mb-3">
                        Un email de relance sera envoyé au client pour le contrat
                        <strong id="nm-policy"></strong> (<span id="nm-client"></span>).
                    </p>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Type de relance</label>
                        <select name="type" class="form-select" id="nm-type" required>
                            <?php foreach ($typeLabels as $key => $label): ?>
                            <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Le palier recommandé pour aujourd'hui est pré-sélectionné.</div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-send me-2"></i>Envoyer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    const modal = document.getElementById('notifierModal');
    if (!modal) return;
    modal.addEventListener('show.bs.modal', function (e) {
        const btn      = e.relatedTarget;
        const id       = btn.dataset.contractId;
        const policy   = btn.dataset.policy;
        const client   = btn.dataset.client;
        const dueType  = btn.dataset.dueType;

        modal.querySelector('#nm-policy').textContent = policy;
        modal.querySelector('#nm-client').textContent = client;
        modal.querySelector('#notifierForm').action   = '/admin/relances/' + id + '/send';

        const sel = modal.querySelector('#nm-type');
        if (dueType) {
            for (let opt of sel.options) {
                opt.selected = (opt.value === dueType);
            }
        }
    });
})();
</script>

<?php require APP_PATH . '/Views/admin/layout/footer.php'; ?>
