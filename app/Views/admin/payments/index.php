<?php $pageTitle = 'Paiements – Administration TILKI'; ?>
<?php require APP_PATH . '/Views/admin/layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 fw-bold mb-0"><i class="bi bi-cash-coin me-2"></i>Paiements</h2>
    <a href="/admin/payments/create" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Enregistrer un paiement
    </a>
</div>

<?php if (empty($payments)): ?>
    <div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>Aucun paiement enregistré.</div>
<?php else: ?>
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Client</th>
                    <th>Police</th>
                    <th>Branche</th>
                    <th class="text-end">Montant</th>
                    <th>Mode</th>
                    <th>Référence</th>
                    <th>Preuve</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($payments as $p): ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($p['paid_at'])) ?></td>
                    <td>
                        <a href="/admin/clients/<?= (int)$p['client_id'] ?>/edit" class="text-decoration-none">
                            <?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?>
                        </a>
                        <br><small class="text-muted"><?= htmlspecialchars($p['account_number']) ?></small>
                    </td>
                    <td><code><?= htmlspecialchars($p['policy_number']) ?></code></td>
                    <td><?= htmlspecialchars($p['branche']) ?></td>
                    <td class="text-end fw-semibold">
                        <?= number_format((float)$p['amount'], 0, ',', ' ') ?>&nbsp;XOF
                    </td>
                    <td>
                        <?php
                        $labels = ['cheque'=>'Chèque','virement'=>'Virement','caisse'=>'Caisse','mobile_money'=>'Mobile Money'];
                        ?>
                        <span class="badge bg-secondary"><?= $labels[$p['method']] ?? htmlspecialchars($p['method']) ?></span>
                    </td>
                    <td><?= htmlspecialchars($p['reference'] ?? '—') ?></td>
                    <td>
                        <?php if ($p['doc_id']): ?>
                            <a href="/documents/<?= (int)$p['doc_id'] ?>/download"
                               class="btn btn-sm btn-outline-secondary" target="_blank">
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

<?php require APP_PATH . '/Views/admin/layout/footer.php'; ?>
