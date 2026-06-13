<?php
$pageTitle = 'File Tally – Administration TILKI';

function tallyFieldPreview(string $jsonPayload, int $max = 6): array
{
    $data   = json_decode($jsonPayload, true);
    $fields = $data['data']['fields'] ?? [];
    $result = [];
    foreach ($fields as $f) {
        $value = $f['value'] ?? null;
        if ($value === null || $value === '' || is_array($value)) continue;
        $result[] = [
            'label' => $f['label'] ?? $f['key'] ?? '?',
            'value' => is_bool($value) ? ($value ? 'Oui' : 'Non') : (string)$value,
        ];
        if (count($result) >= $max) break;
    }
    return $result;
}
?>
<?php require APP_PATH . '/Views/admin/layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h5 fw-bold mb-0">
        <i class="bi bi-inbox me-2"></i>File de soumissions Tally
        <?php if ($pendingCount): ?>
            <span class="badge bg-danger ms-1"><?= $pendingCount ?></span>
        <?php endif; ?>
    </h2>
    <div class="d-flex gap-2 align-items-center">
        <span class="text-muted small">Webhook : <code>POST /webhooks/tally</code></span>
    </div>
</div>

<?php if (empty($entries)): ?>
<div class="card shadow-sm">
    <div class="card-body text-center text-muted py-5">
        <i class="bi bi-check-circle fs-1 d-block mb-2 text-success opacity-50"></i>
        Aucune soumission dans la file.
    </div>
</div>
<?php else: ?>

<!-- Filtres statut -->
<ul class="nav nav-pills mb-3" id="statusFilter">
    <li class="nav-item"><a class="nav-link active" href="#" data-filter="all">Toutes</a></li>
    <li class="nav-item"><a class="nav-link" href="#" data-filter="pending">En attente</a></li>
    <li class="nav-item"><a class="nav-link" href="#" data-filter="matched">Rattachées</a></li>
    <li class="nav-item"><a class="nav-link" href="#" data-filter="ignored">Ignorées</a></li>
</ul>

<div class="d-flex flex-column gap-3" id="entriesList">
    <?php foreach ($entries as $entry):
        $fields  = tallyFieldPreview($entry['payload']);
        $isPending = $entry['status'] === 'pending';
    ?>
    <div class="card shadow-sm entry-card" data-status="<?= htmlspecialchars($entry['status']) ?>">
        <div class="card-header d-flex justify-content-between align-items-center py-2">
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-<?= $entry['status'] === 'pending' ? 'warning text-dark' : ($entry['status'] === 'matched' ? 'success' : 'secondary') ?>">
                    <?= htmlspecialchars($entry['status']) ?>
                </span>
                <span class="small fw-semibold"><?= htmlspecialchars($entry['form_name'] ?? 'Formulaire sans nom') ?></span>
                <code class="small text-muted"><?= htmlspecialchars($entry['response_id']) ?></code>
            </div>
            <span class="text-muted small"><?= date('d/m/Y H:i', strtotime($entry['created_at'])) ?></span>
        </div>

        <div class="card-body py-3">
            <div class="row g-3">

                <!-- Aperçu des champs -->
                <div class="col-lg-6">
                    <p class="small fw-semibold text-muted mb-2">Champs du formulaire</p>
                    <?php if (empty($fields)): ?>
                        <span class="text-muted small">Aucune donnée lisible.</span>
                    <?php else: ?>
                    <dl class="row small mb-0">
                        <?php foreach ($fields as $f): ?>
                        <dt class="col-5 text-muted text-truncate" title="<?= htmlspecialchars($f['label']) ?>">
                            <?= htmlspecialchars($f['label']) ?>
                        </dt>
                        <dd class="col-7 fw-semibold mb-1">
                            <?= htmlspecialchars(mb_strimwidth($f['value'], 0, 60, '…')) ?>
                        </dd>
                        <?php endforeach; ?>
                    </dl>
                    <?php endif; ?>
                </div>

                <!-- Actions -->
                <div class="col-lg-6">
                    <?php if ($entry['status'] === 'matched' && $entry['first_name']): ?>
                        <p class="small fw-semibold text-muted mb-1">Client rattaché</p>
                        <div class="fw-semibold">
                            <?= htmlspecialchars($entry['first_name'] . ' ' . $entry['last_name']) ?>
                            <code class="ms-1"><?= htmlspecialchars($entry['account_number']) ?></code>
                        </div>

                    <?php elseif ($isPending): ?>
                        <p class="small fw-semibold text-muted mb-2">Rattacher à un client</p>
                        <form method="post" action="/admin/tally/<?= (int)$entry['id'] ?>/match"
                              class="d-flex gap-2 align-items-end flex-wrap">
                            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
                            <div class="flex-grow-1">
                                <select name="client_id" class="form-select form-select-sm" required>
                                    <option value="">— Sélectionner un client —</option>
                                    <?php foreach ($clients as $c): ?>
                                    <option value="<?= (int)$c['id'] ?>">
                                        <?= htmlspecialchars($c['account_number'] . ' — ' . $c['first_name'] . ' ' . $c['last_name']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-sm btn-success flex-shrink-0">
                                <i class="bi bi-link-45deg me-1"></i>Rattacher
                            </button>
                        </form>

                        <form method="post" action="/admin/tally/<?= (int)$entry['id'] ?>/ignore"
                              class="mt-2"
                              onsubmit="return confirm('Ignorer définitivement cette soumission ?')">
                            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-x me-1"></i>Ignorer
                            </button>
                        </form>

                    <?php else: ?>
                        <span class="text-muted small">—</span>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<script>
document.querySelectorAll('#statusFilter .nav-link').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelectorAll('#statusFilter .nav-link').forEach(l => l.classList.remove('active'));
        this.classList.add('active');
        const filter = this.dataset.filter;
        document.querySelectorAll('.entry-card').forEach(card => {
            card.style.display = (filter === 'all' || card.dataset.status === filter) ? '' : 'none';
        });
    });
});
</script>

<?php require APP_PATH . '/Views/admin/layout/footer.php'; ?>
