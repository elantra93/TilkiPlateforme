<?php
declare(strict_types=1);
$pageTitle = 'Tableau de bord – TILKI';

// Helpers
function fmtDate(string $d): string {
    return $d ? date('d/m/Y', strtotime($d)) : '—';
}
function fmtAmount(float $v, string $cur): string {
    return number_format($v, 0, ',', ' ') . ' ' . $cur;
}
?>
<?php require APP_PATH . '/Views/layout/header.php'; ?>

<!-- ── En-tête ─────────────────────────────────────────────────────────── -->
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h2 class="h4 fw-bold mb-0">
            Bonjour, <?= htmlspecialchars($client['first_name']) ?>&nbsp;!
        </h2>
        <p class="text-muted small mb-0">
            Compte&nbsp;<code><?= htmlspecialchars($client['account_number']) ?></code>
        </p>
    </div>
    <?php if ($totalDue > 0): ?>
    <div class="alert alert-warning d-flex align-items-center gap-2 py-2 px-3 mb-0" style="max-width:380px">
        <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i>
        <span class="small">
            Solde restant dû sur vos contrats&nbsp;:
            <strong>
                <?= fmtAmount($totalDue, $contracts[0]['currency'] ?? 'XOF') ?>
            </strong>
        </span>
    </div>
    <?php endif; ?>
</div>

<!-- ── Section 1 : Contrats ────────────────────────────────────────────── -->
<section class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h3 class="h6 fw-bold text-uppercase text-muted letter-spacing mb-0">
            <i class="bi bi-file-earmark-text me-2"></i>Mes contrats
            <span class="badge bg-secondary ms-1 fw-normal"><?= count($contracts) ?></span>
        </h3>
    </div>

    <?php if (empty($contracts)): ?>
        <div class="card shadow-sm">
            <div class="card-body text-center text-muted py-5">
                <i class="bi bi-file-earmark-x fs-1 d-block mb-2 opacity-25"></i>
                Aucun contrat enregistré.
            </div>
        </div>
    <?php else: ?>
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tbl-contracts">
                <thead class="table-light">
                    <tr>
                        <th>Branche</th>
                        <th>N° Police</th>
                        <th>Assureur</th>
                        <th>Date d'effet</th>
                        <th>Date d'échéance</th>
                        <th class="text-end">Restant dû</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contracts as $c): ?>
                    <tr class="tbl-row-link"
                        data-href="/contracts/<?= (int)$c['id'] ?>"
                        title="Ouvrir le détail du contrat <?= htmlspecialchars($c['policy_number']) ?>">
                        <td class="fw-semibold"><?= htmlspecialchars($c['branche']) ?></td>
                        <td><code class="text-body"><?= htmlspecialchars($c['policy_number']) ?></code></td>
                        <td><?= htmlspecialchars($c['insurer']) ?></td>
                        <td><?= fmtDate($c['effective_date']) ?></td>
                        <td>
                            <?php
                                $isExpiring = $c['expiry_date'] && strtotime($c['expiry_date']) < strtotime('+30 days');
                                $cls = $isExpiring ? 'text-danger fw-semibold' : '';
                            ?>
                            <span class="<?= $cls ?>">
                                <?= fmtDate($c['expiry_date']) ?>
                                <?php if ($isExpiring): ?>
                                    <i class="bi bi-alarm ms-1" title="Expire bientôt"></i>
                                <?php endif; ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <?php if ((float)$c['premium_due'] <= 0): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle fw-normal">
                                    <i class="bi bi-check2 me-1"></i>À jour
                                </span>
                            <?php else: ?>
                                <span class="fw-semibold text-danger">
                                    <?= fmtAmount((float)$c['premium_due'], $c['currency']) ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-<?= $c['status'] === 'actif' ? 'success' : 'secondary' ?>">
                                <?= htmlspecialchars($c['status']) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</section>

<!-- ── Section 2 : Sinistres ouverts ───────────────────────────────────── -->
<section>
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h3 class="h6 fw-bold text-uppercase text-muted mb-0">
            <i class="bi bi-exclamation-triangle me-2 text-danger"></i>Sinistres ouverts
            <?php if (count($openClaims)): ?>
                <span class="badge bg-danger ms-1 fw-normal"><?= count($openClaims) ?></span>
            <?php endif; ?>
        </h3>
        <a href="/claims" class="btn btn-sm btn-outline-secondary">
            Tous les sinistres <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>

    <?php if (empty($openClaims)): ?>
        <div class="card shadow-sm">
            <div class="card-body text-center text-muted py-5">
                <i class="bi bi-shield-check fs-1 d-block mb-2 text-success opacity-50"></i>
                Aucun sinistre ouvert en cours.
            </div>
        </div>
    <?php else: ?>
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tbl-claims">
                <thead class="table-light">
                    <tr>
                        <th>N° Sinistre</th>
                        <th>Assureur</th>
                        <th>Branche</th>
                        <th>Date de survenance</th>
                        <th>Dernière mise à jour</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($openClaims as $cl): ?>
                    <tr class="tbl-row-link"
                        data-href="/claims/<?= (int)$cl['id'] ?>"
                        title="Ouvrir le sinistre <?= htmlspecialchars($cl['claim_number']) ?>">
                        <td>
                            <code class="text-body"><?= htmlspecialchars($cl['claim_number']) ?></code>
                        </td>
                        <td><?= htmlspecialchars($cl['insurer']) ?></td>
                        <td><?= htmlspecialchars($cl['branche']) ?></td>
                        <td><?= fmtDate($cl['occurrence_date']) ?></td>
                        <td class="text-muted small"><?= fmtDate($cl['updated_at']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</section>

<script>
// Lignes cliquables
document.querySelectorAll('.tbl-row-link').forEach(function(row) {
    row.style.cursor = 'pointer';
    row.addEventListener('click', function() {
        window.location.href = this.dataset.href;
    });
});
</script>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
