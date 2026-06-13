<?php $pageTitle = 'Documents en attente – Administration TILKI'; ?>
<?php require APP_PATH . '/Views/admin/layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h5 fw-bold mb-0">
        <i class="bi bi-hourglass-split me-2 text-warning"></i>Documents en attente de validation
        <?php if (count($docs)): ?>
            <span class="badge bg-warning text-dark ms-1"><?= count($docs) ?></span>
        <?php endif; ?>
    </h2>
    <a href="/admin/documents/upload" class="btn btn-sm btn-primary">
        <i class="bi bi-upload me-1"></i>Uploader un document
    </a>
</div>

<?php if (empty($docs)): ?>
<div class="card shadow-sm">
    <div class="card-body text-center text-muted py-5">
        <i class="bi bi-check-circle fs-1 d-block mb-2 text-success opacity-50"></i>
        Aucun document en attente de validation.
    </div>
</div>
<?php else: ?>
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>Date dépôt</th>
                    <th>Client</th>
                    <th>Contrat / Sinistre</th>
                    <th>Famille</th>
                    <th>Type</th>
                    <th>Fichier</th>
                    <th>Taille</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($docs as $doc): ?>
                <tr>
                    <td class="text-muted small"><?= date('d/m/Y H:i', strtotime($doc['created_at'])) ?></td>
                    <td>
                        <div class="fw-semibold small"><?= htmlspecialchars($doc['first_name'] . ' ' . $doc['last_name']) ?></div>
                        <div class="text-muted" style="font-size:.75rem"><code><?= htmlspecialchars($doc['account_number']) ?></code></div>
                    </td>
                    <td class="small">
                        <?php if ($doc['scope'] === 'contrat' && $doc['policy_number']): ?>
                            <i class="bi bi-file-earmark-text text-primary me-1"></i>
                            <code><?= htmlspecialchars($doc['policy_number']) ?></code>
                        <?php elseif ($doc['scope'] === 'sinistre' && $doc['claim_number']): ?>
                            <i class="bi bi-exclamation-triangle text-danger me-1"></i>
                            <code><?= htmlspecialchars($doc['claim_number']) ?></code>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge bg-<?= $doc['category'] === 'cotation' ? 'info' : 'success' ?> bg-opacity-75">
                            <?= htmlspecialchars($doc['category']) ?>
                        </span>
                    </td>
                    <td class="small"><?= htmlspecialchars($doc['doc_type']) ?></td>
                    <td class="small text-truncate" style="max-width:180px" title="<?= htmlspecialchars($doc['original_filename']) ?>">
                        <?= htmlspecialchars($doc['original_filename']) ?>
                    </td>
                    <td class="small text-muted"><?= number_format($doc['file_size'] / 1024, 0) ?>&nbsp;Ko</td>
                    <td>
                        <form method="post" action="/admin/documents/<?= (int)$doc['id'] ?>/validate"
                              onsubmit="return confirm('Valider ce document ?')">
                            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
                            <button type="submit" class="btn btn-sm btn-success">
                                <i class="bi bi-check-lg me-1"></i>Valider
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
