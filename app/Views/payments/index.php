<?php $pageTitle = 'Mes paiements – TILKI'; ?>
<?php require APP_PATH . '/Views/layout/header.php'; ?>

<h2 class="h4 fw-bold mb-4"><i class="bi bi-cash-coin me-2"></i>Mes paiements</h2>

<?php if (empty($payments)): ?>
<div class="card shadow-sm">
    <div class="card-body text-center text-muted py-5">
        <i class="bi bi-cash-coin fs-1 d-block mb-2 opacity-25"></i>
        <p class="mb-0">Aucun paiement enregistré sur votre compte.</p>
    </div>
</div>
<?php else: ?>
<div class="card shadow-sm">
    <div class="table-responsive">
        <?php
        $methodLabels = ['especes'=>'Espèces','cheque'=>'Chèque','virement'=>'Virement','caisse'=>'Caisse','mobile_money'=>'Mobile Money','carte'=>'Carte'];
        $methodBadge  = ['cheque'=>'badge-method-cheque','virement'=>'badge-method-virement','caisse'=>'badge-method-caisse','mobile_money'=>'badge-method-mobile'];
        ?>
        <table class="table table-hover align-middle mb-0 tbl-card-mobile">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
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
                    <td data-label="Date" class="small text-muted"><?= date('d/m/Y', strtotime($p['paid_at'])) ?></td>
                    <td data-label="Police"><code class="small"><?= htmlspecialchars($p['policy_number']) ?></code></td>
                    <td data-label="Branche" class="small"><?= htmlspecialchars($p['branche']) ?></td>
                    <td data-label="Montant" class="fw-semibold small">
                        <?= number_format((float)$p['amount'], 0, ',', ' ') ?>&nbsp;XOF
                    </td>
                    <td data-label="Mode">
                        <span class="badge <?= $methodBadge[$p['method']] ?? 'bg-secondary' ?>">
                            <?= $methodLabels[$p['method']] ?? htmlspecialchars($p['method']) ?>
                        </span>
                    </td>
                    <td data-label="Statut">
                        <?php if ($p['status'] === 'valide'): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                <i class="bi bi-check-circle me-1"></i>Validé
                            </span>
                        <?php elseif ($p['status'] === 'en_attente'): ?>
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                <i class="bi bi-hourglass-split me-1"></i>En cours de vérification
                            </span>
                        <?php else: ?>
                            <div>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                    <i class="bi bi-x-circle me-1"></i>Rejeté
                                </span>
                                <?php if (!empty($p['rejected_reason'])): ?>
                                <div class="text-danger fs-xs mt-1">
                                    <i class="bi bi-chat-text me-1"></i><?= htmlspecialchars($p['rejected_reason']) ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </td>
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
