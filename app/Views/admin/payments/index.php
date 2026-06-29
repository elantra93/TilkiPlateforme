<?php $pageTitle = 'Paiements – Administration TILKI'; ?>
<?php require APP_PATH . '/Views/admin/layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h5 fw-bold mb-0"><i class="bi bi-cash-coin me-2"></i>Paiements</h2>
    <div class="d-flex gap-2">
        <?php if ($countPending > 0): ?>
        <a href="/admin/payments/pending" class="btn btn-warning btn-sm">
            <i class="bi bi-hourglass-split me-1"></i>En attente
            <span class="badge bg-dark ms-1"><?= $countPending ?></span>
        </a>
        <?php else: ?>
        <a href="/admin/payments/pending" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-hourglass-split me-1"></i>En attente
        </a>
        <?php endif; ?>
        <a href="/admin/payments/create" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i>Enregistrer un paiement
        </a>
    </div>
</div>

<?php if (empty($payments)): ?>
<div class="card shadow-sm">
    <div class="card-body text-center text-muted py-5">
        <i class="bi bi-cash-coin fs-1 d-block mb-2 opacity-25"></i>
        <p class="mb-0">Aucun paiement enregistré.</p>
    </div>
</div>
<?php else: ?>
<div class="card shadow-sm">
    <div class="table-responsive">
        <?php
        $labels      = ['especes'=>'Espèces','cheque'=>'Chèque','virement'=>'Virement','caisse'=>'Caisse','mobile_money'=>'Mobile Money','carte'=>'Carte'];
        $methodBadge = ['cheque'=>'badge-method-cheque','virement'=>'badge-method-virement','caisse'=>'badge-method-caisse','mobile_money'=>'badge-method-mobile'];
        ?>
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Client</th>
                    <th>Police</th>
                    <th>Branche</th>
                    <th>Montant</th>
                    <th>Mode</th>
                    <th>Statut</th>
                    <th>Preuve</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($payments as $p): ?>
                <tr>
                    <td class="small text-muted"><?= date('d/m/Y', strtotime($p['paid_at'])) ?></td>
                    <td>
                        <a href="/admin/clients/<?= (int)$p['client_id'] ?>/edit" class="text-decoration-none fw-semibold small">
                            <?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?>
                        </a>
                        <div class="text-muted fs-xs"><?= htmlspecialchars($p['account_number']) ?></div>
                    </td>
                    <td><code class="small"><?= htmlspecialchars($p['policy_number']) ?></code></td>
                    <td class="small"><?= htmlspecialchars($p['branche']) ?></td>
                    <td class="fw-semibold small">
                        <?= number_format((float)$p['amount'], 0, ',', ' ') ?>&nbsp;XOF
                    </td>
                    <td>
                        <span class="badge <?= $methodBadge[$p['method']] ?? 'bg-secondary' ?>">
                            <?= $labels[$p['method']] ?? htmlspecialchars($p['method']) ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($p['status'] === 'valide'): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                <i class="bi bi-check-circle me-1"></i>Validé
                            </span>
                        <?php elseif ($p['status'] === 'en_attente'): ?>
                            <a href="/admin/payments/pending" class="badge bg-warning-subtle text-warning border border-warning-subtle text-decoration-none">
                                <i class="bi bi-hourglass-split me-1"></i>En attente
                            </a>
                        <?php else: ?>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle"
                                  title="<?= htmlspecialchars($p['rejected_reason'] ?? '') ?>">
                                <i class="bi bi-x-circle me-1"></i>Rejeté
                            </span>
                            <?php if (!empty($p['rejected_reason'])): ?>
                            <div class="text-muted fs-xs mt-1"><?= htmlspecialchars(mb_strimwidth($p['rejected_reason'], 0, 50, '…')) ?></div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($p['doc_id']): ?>
                            <a href="/admin/documents/<?= (int)$p['doc_id'] ?>/download"
                               class="btn btn-sm btn-outline-secondary" target="_blank">
                                <i class="bi bi-download"></i>
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

<?php require APP_PATH . '/Views/admin/layout/footer.php'; ?>
