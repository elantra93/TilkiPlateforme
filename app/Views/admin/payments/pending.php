<?php $pageTitle = 'Paiements en attente – Administration TILKI'; ?>
<?php require APP_PATH . '/Views/admin/layout/header.php'; ?>

<?php
$labels      = ['especes'=>'Espèces','cheque'=>'Chèque','virement'=>'Virement','caisse'=>'Caisse','mobile_money'=>'Mobile Money','carte'=>'Carte'];
$methodBadge = ['cheque'=>'badge-method-cheque','virement'=>'badge-method-virement','caisse'=>'badge-method-caisse','mobile_money'=>'badge-method-mobile'];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="/admin/payments" class="btn btn-sm btn-outline-secondary me-2">
            <i class="bi bi-arrow-left me-1"></i>Tous les paiements
        </a>
        <h2 class="h5 fw-bold mb-0 d-inline-block align-middle">
            <i class="bi bi-hourglass-split me-2 text-warning"></i>Paiements en attente
            <?php if (!empty($payments)): ?>
            <span class="badge bg-warning text-dark ms-1"><?= count($payments) ?></span>
            <?php endif; ?>
        </h2>
    </div>
    <a href="/admin/payments/create" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Enregistrer un paiement
    </a>
</div>

<?php if (empty($payments)): ?>
<div class="card shadow-sm">
    <div class="card-body text-center text-muted py-5">
        <i class="bi bi-check2-circle fs-1 d-block mb-2 text-success opacity-50"></i>
        <p class="fw-semibold mb-1">Aucun paiement en attente</p>
        <p class="small mb-0">Tous les paiements ont été traités.</p>
    </div>
</div>
<?php else: ?>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Reçu le</th>
                    <th>Client</th>
                    <th>Police</th>
                    <th>Branche</th>
                    <th>Montant</th>
                    <th>Mode</th>
                    <th>Réf.</th>
                    <th>Preuve</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($payments as $p): ?>
                <!-- Ligne principale -->
                <tr>
                    <td class="small text-muted text-nowrap"><?= date('d/m/Y', strtotime($p['created_at'])) ?></td>
                    <td>
                        <a href="/admin/clients/<?= (int)$p['client_id'] ?>/edit"
                           class="text-decoration-none fw-semibold small">
                            <?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?>
                        </a>
                        <div class="text-muted fs-xs"><?= htmlspecialchars($p['account_number']) ?></div>
                    </td>
                    <td><code class="small"><?= htmlspecialchars($p['policy_number']) ?></code></td>
                    <td class="small"><?= htmlspecialchars($p['branche']) ?></td>
                    <td class="fw-bold small text-nowrap">
                        <?= number_format((float)$p['amount'], 0, ',', ' ') ?>&nbsp;XOF
                    </td>
                    <td>
                        <span class="badge <?= $methodBadge[$p['method']] ?? 'bg-secondary' ?>">
                            <?= $labels[$p['method']] ?? htmlspecialchars($p['method']) ?>
                        </span>
                    </td>
                    <td class="small text-muted"><?= htmlspecialchars($p['reference'] ?? '—') ?></td>
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
                    <td class="text-nowrap">
                        <!-- Valider -->
                        <form method="post" action="/admin/payments/<?= (int)$p['id'] ?>/validate"
                              class="d-inline">
                            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf ?? '') ?>">
                            <button type="submit" class="btn btn-sm btn-success">
                                <i class="bi bi-check-lg me-1"></i>Valider
                            </button>
                        </form>
                        <!-- Rejeter (toggle collapse) -->
                        <button class="btn btn-sm btn-outline-danger ms-1"
                                data-bs-toggle="collapse"
                                data-bs-target="#reject-<?= (int)$p['id'] ?>"
                                aria-expanded="false">
                            <i class="bi bi-x-lg me-1"></i>Rejeter
                        </button>
                    </td>
                </tr>
                <!-- Ligne collapse motif de rejet -->
                <tr class="collapse" id="reject-<?= (int)$p['id'] ?>">
                    <td colspan="9" class="bg-danger-subtle border-top-0 pt-0 pb-3 px-4">
                        <form method="post" action="/admin/payments/<?= (int)$p['id'] ?>/reject"
                              class="d-flex gap-2 align-items-end mt-2">
                            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf ?? '') ?>">
                            <div class="flex-grow-1">
                                <label class="form-label small fw-semibold mb-1 text-danger">
                                    <i class="bi bi-chat-text me-1"></i>Motif de rejet <span class="fw-normal text-danger">(obligatoire)</span>
                                </label>
                                <textarea name="rejected_reason" class="form-control form-control-sm"
                                          rows="2" maxlength="500" required
                                          placeholder="Ex : Preuve de paiement illisible, montant incorrect…"></textarea>
                            </div>
                            <button type="submit" class="btn btn-danger btn-sm mb-0">
                                <i class="bi bi-x-circle me-1"></i>Confirmer le rejet
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php endif; ?>

<?php require APP_PATH . '/Views/admin/layout/footer.php'; ?>
