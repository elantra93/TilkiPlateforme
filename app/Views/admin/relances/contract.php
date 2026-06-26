<?php
$pageTitle = 'Relances — ' . htmlspecialchars($contract['policy_number'] ?? '') . ' – Administration TILKI';
$days = $contract['expiry_date']
    ? (int)round((strtotime($contract['expiry_date']) - strtotime('today')) / 86400)
    : null;
?>
<?php require APP_PATH . '/Views/admin/layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h5 fw-bold mb-0">
            <i class="bi bi-bell me-2"></i>Relances — <?= htmlspecialchars($contract['policy_number']) ?>
        </h2>
        <small class="text-muted">
            <?= htmlspecialchars($contract['branche']) ?>
            · Échéance :
            <?php if ($contract['expiry_date']): ?>
            <strong><?= date('d/m/Y', strtotime($contract['expiry_date'])) ?></strong>
            <?php if ($days !== null): ?>
            <span class="<?= $days < 0 ? 'text-danger' : ($days <= 7 ? 'text-warning' : 'text-success') ?> fw-semibold">
                (<?= $days >= 0 ? 'dans ' . $days . ' j.' : abs($days) . ' j. dépassé' ?>)
            </span>
            <?php endif; ?>
            <?php else: ?>—<?php endif; ?>
        </small>
    </div>
    <div class="d-flex gap-2">
        <a href="/admin/contracts/<?= (int)$contract['id'] ?>/edit"
           class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-file-earmark-text me-1"></i>Contrat
        </a>
        <a href="/admin/relances" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Échéancier
        </a>
    </div>
</div>

<!-- Envoyer une relance manuelle -->
<div class="card shadow-sm mb-4">
    <div class="card-header fw-semibold">
        <i class="bi bi-send me-2 text-primary"></i>Envoyer une relance manuelle
    </div>
    <div class="card-body">
        <form method="post" action="/admin/relances/<?= (int)$contract['id'] ?>/send" class="row g-3">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
            <div class="col-md-6">
                <label class="form-label small fw-semibold">Type de relance</label>
                <select name="type" class="form-select">
                    <?php foreach ($typeLabels as $key => $label):
                        $alreadySent = \App\Models\Relance::hasSentType((int)$contract['id'], $key);
                    ?>
                    <option value="<?= $key ?>"
                        <?= \App\Models\Relance::dueTypeForExpiry($contract['expiry_date']) === $key ? 'selected' : '' ?>>
                        <?= htmlspecialchars($label) ?>
                        <?= $alreadySent ? '✓ déjà envoyée' : '' ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text">Le type recommandé (basé sur la date du jour) est pré-sélectionné.</div>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-send me-2"></i>Envoyer
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Historique des relances -->
<div class="card shadow-sm">
    <div class="card-header fw-semibold d-flex align-items-center gap-2">
        <i class="bi bi-clock-history text-muted"></i>Historique des relances
        <?php if (!empty($relances)): ?>
        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle ms-1"><?= count($relances) ?></span>
        <?php endif; ?>
    </div>
    <?php if (empty($relances)): ?>
    <div class="card-body text-center py-4 text-muted small fst-italic">
        Aucune relance enregistrée pour ce contrat.
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table align-middle mb-0 small">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Canal</th>
                    <th>Statut</th>
                    <th>Envoyée le</th>
                    <th>Message d'erreur</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($relances as $r):
                $statusBadge = match($r['status']) {
                    'envoyee'  => 'bg-success-subtle text-success border-success-subtle',
                    'echouee'  => 'bg-danger-subtle text-danger border-danger-subtle',
                    default    => 'bg-secondary-subtle text-secondary border-secondary-subtle',
                };
            ?>
            <tr>
                <td class="text-muted"><?= date('d/m/Y H:i', strtotime($r['created_at'])) ?></td>
                <td><?= htmlspecialchars($typeLabels[$r['type']] ?? $r['type']) ?></td>
                <td class="text-muted"><?= ucfirst($r['channel']) ?></td>
                <td>
                    <span class="badge <?= $statusBadge ?> border">
                        <?= ucfirst($r['status']) ?>
                    </span>
                </td>
                <td class="text-muted">
                    <?= $r['sent_at'] ? date('d/m/Y H:i', strtotime($r['sent_at'])) : '—' ?>
                </td>
                <td class="text-danger small"><?= htmlspecialchars($r['error_message'] ?? '') ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php require APP_PATH . '/Views/admin/layout/footer.php'; ?>
