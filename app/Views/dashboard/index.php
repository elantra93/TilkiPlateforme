<?php
declare(strict_types=1);
$pageTitle = 'Tableau de bord – TILKI';

$isEntreprise = ($client['account_type'] ?? 'individuel') === 'entreprise';
$displayName  = $isEntreprise
    ? htmlspecialchars($client['company_name'] ?? $client['first_name'])
    : htmlspecialchars($client['first_name']);

function fmtAmt(float $v, string $cur = 'FCFA'): string {
    return number_format($v, 0, ',', ' ') . ' ' . $cur;
}
function fmtDate(string $d): string {
    return $d ? date('d/m/Y', strtotime($d)) : '—';
}

// Contrat avec le plus grand solde dû (pour le lien "Régler")
$dueContract = null;
foreach ($contracts as $c) {
    if ((float)$c['premium_due'] > 0) { $dueContract = $c; break; }
}

// Contrat en échéance proche pour le lien "Voir"
$expiringContract = $nextExpiry;
?>
<?php require APP_PATH . '/Views/layout/header.php'; ?>

<!-- ── En-tête ─────────────────────────────────────────────────────────── -->
<div class="mb-4">
    <h2 class="h4 fw-bold mb-0">
        Bonjour<?= $isEntreprise ? '' : ', ' . $displayName ?><?= $isEntreprise ? ', ' . $displayName : '' ?>&nbsp;!
    </h2>
    <p class="text-muted small mb-0">
        <?php if ($isEntreprise): ?>
            Entreprise &middot; n°&nbsp;<span class="font-mono"><?= htmlspecialchars($client['account_number']) ?></span>
        <?php else: ?>
            Particulier &middot; n°&nbsp;<span class="font-mono"><?= htmlspecialchars($client['account_number']) ?></span>
        <?php endif; ?>
    </p>
</div>

<!-- ── Alertes ──────────────────────────────────────────────────────────── -->
<?php if ($totalDue > 0 && $dueContract): ?>
<div class="tk-alert-card tk-alert-warning mb-3">
    <div class="tk-alert-body">
        <div class="tk-alert-label">Solde dû sur votre contrat <?= htmlspecialchars($dueContract['branche']) ?></div>
        <div class="tk-alert-sub">Restant à régler</div>
        <div class="tk-alert-amount font-mono"><?= fmtAmt((float)$totalDue, $dueContract['currency'] ?? 'FCFA') ?></div>
    </div>
    <a href="/contracts/<?= (int)$dueContract['id'] ?>" class="btn btn-sm btn-primary flex-shrink-0">
        Régler
    </a>
</div>
<?php endif; ?>

<?php if ($nextExpiry && $nextExpiryDays !== null && $nextExpiryDays <= 30): ?>
<div class="tk-alert-card tk-alert-info mb-3">
    <div class="tk-alert-body">
        <div class="tk-alert-label">Échéance <?= htmlspecialchars($nextExpiry['branche']) ?> à venir</div>
        <div class="tk-alert-sub">Renouvellement le</div>
        <div class="tk-alert-amount font-mono"><?= fmtDate($nextExpiry['expiry_date']) ?></div>
    </div>
    <a href="/contracts/<?= (int)$nextExpiry['id'] ?>" class="btn btn-sm btn-outline-primary flex-shrink-0">
        Voir
    </a>
</div>
<?php endif; ?>

<!-- ── KPIs ─────────────────────────────────────────────────────────────── -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card tk-kpi-card">
            <div class="tk-kpi-label">Contrats actifs</div>
            <div class="tk-kpi-value"><?= $activeCount ?></div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card tk-kpi-card">
            <div class="tk-kpi-label">Restant dû</div>
            <?php if ($totalDue > 0): ?>
            <div class="tk-kpi-value font-mono tk-kpi-danger"><?= number_format($totalDue, 0, ',', ' ') ?></div>
            <div class="tk-kpi-sub">FCFA</div>
            <?php else: ?>
            <div class="tk-kpi-value tk-kpi-ok"><i class="bi bi-check2-circle"></i></div>
            <div class="tk-kpi-sub">À jour</div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card tk-kpi-card">
            <div class="tk-kpi-label">Prochaine échéance</div>
            <?php if ($nextExpiry): ?>
            <div class="tk-kpi-value font-mono <?= ($nextExpiryDays <= 30) ? 'tk-kpi-warning' : '' ?>"><?= fmtDate($nextExpiry['expiry_date']) ?></div>
            <?php else: ?>
            <div class="tk-kpi-value text-muted" style="font-size:1.1rem">—</div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card tk-kpi-card">
            <div class="tk-kpi-label">Sinistres ouverts</div>
            <div class="tk-kpi-value <?= count($openClaims) > 0 ? 'tk-kpi-danger' : '' ?>"><?= count($openClaims) ?></div>
        </div>
    </div>
</div>

<!-- ── Contrats ─────────────────────────────────────────────────────────── -->
<section class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="h6 fw-bold mb-0">
            <?= $isEntreprise ? 'Nos contrats' : 'Vos contrats' ?>
        </h3>
        <a href="/contracts" class="small text-primary text-decoration-none fw-semibold">
            Tout voir <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>

    <?php if (empty($contracts)): ?>
    <div class="card">
        <div class="card-body text-center text-muted py-5">
            <i class="bi bi-file-earmark-x fs-1 d-block mb-2 opacity-25"></i>
            Aucun contrat enregistré.
        </div>
    </div>
    <?php else: ?>
    <div class="card">
        <ul class="list-group list-group-flush">
            <?php foreach (array_slice($contracts, 0, 4) as $c): ?>
            <li class="list-group-item list-group-item-action px-4 py-3 tk-list-row"
                onclick="window.location='/contracts/<?= (int)$c['id'] ?>'" style="cursor:pointer">
                <div class="d-flex justify-content-between align-items-center gap-3">
                    <div class="min-w-0">
                        <div class="fw-semibold text-body">
                            <?= htmlspecialchars($c['branche']) ?> &middot; <?= htmlspecialchars($c['insurer']) ?>
                        </div>
                        <div class="small text-muted mt-1">
                            <span class="font-mono"><?= htmlspecialchars($c['policy_number']) ?></span>
                            <?php if (!empty($c['expiry_date'])): ?>
                                &middot; échéance <?= fmtDate($c['expiry_date']) ?>
                            <?php endif; ?>
                            <?php if ((float)$c['premium_due'] > 0): ?>
                                &middot; <span class="text-danger fw-semibold"><?= fmtAmt((float)$c['premium_due'], $c['currency'] ?? 'FCFA') ?> dû</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <span class="badge tk-badge-<?= $c['status'] ?> flex-shrink-0">
                        <?= htmlspecialchars($c['status']) ?>
                    </span>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
</section>

<!-- ── Raccourcis ──────────────────────────────────────────────────────── -->
<section class="mb-4">
    <h3 class="h6 fw-bold mb-3">Raccourcis</h3>
    <div class="d-flex gap-2 flex-wrap">
        <a href="/claims" class="btn btn-outline-danger">
            <i class="bi bi-exclamation-triangle me-2"></i>Déclarer un sinistre
        </a>
        <a href="/devis" class="btn btn-outline-primary">
            <i class="bi bi-pencil-square me-2"></i>Demander un devis
        </a>
    </div>
</section>

<?php if (!empty($openClaims)): ?>
<!-- ── Sinistres ouverts ─────────────────────────────────────────────────── -->
<section>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="h6 fw-bold mb-0">
            <?= $isEntreprise ? 'Nos sinistres ouverts' : 'Sinistres ouverts' ?>
            <span class="badge bg-danger ms-1 fw-normal"><?= count($openClaims) ?></span>
        </h3>
        <a href="/claims" class="small text-primary text-decoration-none fw-semibold">
            Tout voir <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>
    <div class="card">
        <ul class="list-group list-group-flush">
            <?php foreach ($openClaims as $cl): ?>
            <li class="list-group-item list-group-item-action px-4 py-3 tk-list-row"
                onclick="window.location='/claims/<?= (int)$cl['id'] ?>'" style="cursor:pointer">
                <div class="d-flex justify-content-between align-items-center gap-3">
                    <div class="min-w-0">
                        <div class="fw-semibold text-body">
                            <?= htmlspecialchars($cl['branche']) ?> &middot; <?= htmlspecialchars($cl['insurer']) ?>
                        </div>
                        <div class="small text-muted mt-1">
                            <span class="font-mono"><?= htmlspecialchars($cl['claim_number']) ?></span>
                            &middot; déclaré le <?= fmtDate($cl['occurrence_date']) ?>
                        </div>
                    </div>
                    <span class="badge tk-badge-ouvert flex-shrink-0">ouvert</span>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
<?php endif; ?>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
