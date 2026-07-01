<?php
$pageTitle     = 'Mes contrats – TILKI';
$isEntreprise  = ($client['account_type'] ?? 'individuel') === 'entreprise';
$titre         = $isEntreprise ? 'Nos contrats' : 'Mes contrats';
$actifs        = count(array_filter($contracts, fn($c) => $c['status'] === 'actif'));
$vehicleCounts      = $vehicleCounts ?? [];
$beneficiaryCounts  = $beneficiaryCounts ?? [];
$vehicleBranches    = ['auto', 'automobile', 'moto', 'flotte automobile'];
$santeBranches      = ['santé individuelle', 'santé groupe', 'santé'];
?>
<?php require APP_PATH . '/Views/layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 mb-0 fw-bold">
        <?= $titre ?>
        <?php if ($actifs): ?>
        <span class="badge bg-primary ms-2 fw-normal" style="font-size:.7rem"><?= $actifs ?> actif<?= $actifs > 1 ? 's' : '' ?></span>
        <?php endif; ?>
    </h2>
</div>

<?php if (empty($contracts)): ?>
<div class="card">
    <div class="card-body text-center text-muted py-5">
        <i class="bi bi-file-earmark-x fs-1 d-block mb-2 opacity-25"></i>
        <p class="mb-0">Aucun contrat enregistré.</p>
    </div>
</div>
<?php else: ?>
<div class="card">
    <ul class="list-group list-group-flush">
        <?php foreach ($contracts as $c):
            $due = (float)$c['premium_due'];
        ?>
        <li class="list-group-item list-group-item-action px-4 py-3 tk-list-row"
            onclick="window.location='/contracts/<?= (int)$c['id'] ?>'" style="cursor:pointer">
            <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                <div class="d-flex align-items-center gap-3 min-w-0">
                    <span class="tk-icon-tile"><i class="bi <?= tk_branche_icon($c['branche']) ?>"></i></span>
                    <div class="min-w-0">
                    <div class="fw-semibold text-body">
                        <?= htmlspecialchars($c['branche']) ?> &middot; <?= htmlspecialchars($c['insurer']) ?>
                    </div>
                    <div class="small text-muted mt-1 d-flex flex-wrap gap-2">
                        <span class="font-mono"><?= htmlspecialchars($c['policy_number']) ?></span>
                        <?php
                        $nbVeh  = (int)($vehicleCounts[(int)$c['id']] ?? 0);
                        $nbBen  = (int)($beneficiaryCounts[(int)$c['id']] ?? 0);
                        $bLower = mb_strtolower(trim($c['branche']));
                        if ($nbVeh > 0 && in_array($bLower, $vehicleBranches, true)):
                        ?>
                        <span>&middot; <?= $nbVeh ?>&nbsp;véhicule<?= $nbVeh > 1 ? 's' : '' ?></span>
                        <?php endif; ?>
                        <?php if ($nbBen > 0 && in_array($bLower, $santeBranches, true)): ?>
                        <span>&middot; <?= $nbBen ?>&nbsp;bénéficiaire<?= $nbBen > 1 ? 's' : '' ?></span>
                        <?php endif; ?>
                        <?php if (!empty($c['expiry_date'])): ?>
                        <span>&middot; échéance <?= date('d/m/Y', strtotime($c['expiry_date'])) ?></span>
                        <?php endif; ?>
                    </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    <?php if ($due > 0): ?>
                    <span class="small fw-semibold text-danger font-mono">
                        <?= number_format($due, 0, ',', ' ') ?>&nbsp;<?= htmlspecialchars($c['currency'] ?? 'FCFA') ?>&nbsp;dû
                    </span>
                    <?php else: ?>
                    <span class="badge bg-success-subtle text-success border border-success-subtle fw-normal">
                        <i class="bi bi-check2 me-1"></i>À jour
                    </span>
                    <?php endif; ?>
                    <span class="badge tk-badge-<?= htmlspecialchars($c['status']) ?>">
                        <?= htmlspecialchars($c['status']) ?>
                    </span>
                </div>
            </div>
        </li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
