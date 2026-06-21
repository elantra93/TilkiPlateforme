<?php $pageTitle = 'Mes paiements – TILKI'; ?>
<?php require APP_PATH . '/Views/layout/header.php'; ?>

<h2 class="h4 fw-bold mb-4"><i class="bi bi-cash-coin me-2"></i>Mes paiements</h2>

<?php if (empty($payments)): ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle me-2"></i>Aucun paiement enregistré sur votre compte.
    </div>
<?php else: ?>
<div class="card shadow-sm">
    <div class="table-responsive">
        <?php $methodLabels = ['cheque'=>'Chèque','virement'=>'Virement','caisse'=>'Caisse','mobile_money'=>'Mobile Money']; ?>
        <table class="table table-hover align-middle mb-0 tbl-card-mobile">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Police</th>
                    <th>Branche</th>
                    <th>Montant</th>
                    <th>Mode</th>
                    <th>Référence</th>
                    <th>Preuve</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($payments as $p): ?>
                <tr>
                    <td data-label="Date"><?= date('d/m/Y', strtotime($p['paid_at'])) ?></td>
                    <td data-label="Police"><code><?= htmlspecialchars($p['policy_number']) ?></code></td>
                    <td data-label="Branche"><?= htmlspecialchars($p['branche']) ?></td>
                    <td data-label="Montant" class="fw-semibold">
                        <?= number_format((float)$p['amount'], 0, ',', ' ') ?>&nbsp;XOF
                    </td>
                    <td data-label="Mode">
                        <span class="badge bg-secondary">
                            <?= $methodLabels[$p['method']] ?? htmlspecialchars($p['method']) ?>
                        </span>
                    </td>
                    <td data-label="Référence"><?= htmlspecialchars($p['reference'] ?? '—') ?></td>
                    <td data-label="Preuve">
                        <?php if ($p['doc_id']): ?>
                            <a href="/documents/<?= (int)$p['doc_id'] ?>/download"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-download me-1"></i>Télécharger
                            </a>
                        <?php else: ?>
                            <span class="text-muted small">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
