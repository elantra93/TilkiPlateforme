<?php $pageTitle = 'Nouveau client – Administration TILKI'; ?>
<?php require APP_PATH . '/Views/admin/layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h5 fw-bold mb-0"><i class="bi bi-person-plus me-2"></i>Nouveau client</h2>
    <a href="/admin/clients" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Retour
    </a>
</div>

<?php if (!empty($credentials)): ?>
<!-- ── Identifiants générés ──────────────────────────────────────────────── -->
<div class="alert alert-success border-success shadow-sm mb-4">
    <h5 class="fw-bold mb-3"><i class="bi bi-check-circle-fill me-2"></i>Client créé — identifiants initiaux</h5>
    <div class="alert alert-warning mb-3 small">
        <i class="bi bi-exclamation-triangle-fill me-1"></i>
        Ces identifiants ne seront affichés <strong>qu'une seule fois</strong>. Transmettez-les de façon sécurisée.
    </div>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label small fw-semibold text-muted">Client</label>
            <div class="form-control-plaintext fw-semibold">
                <?= htmlspecialchars($credentials['name']) ?>
                &lt;<?= htmlspecialchars($credentials['email']) ?>&gt;
            </div>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-muted">N° de compte</label>
            <div class="input-group">
                <input type="text" class="form-control form-control-sm font-monospace fw-bold"
                       value="<?= htmlspecialchars($credentials['account_number']) ?>" readonly id="acc">
                <button class="btn btn-outline-secondary btn-sm" type="button"
                        onclick="navigator.clipboard.writeText(document.getElementById('acc').value)">
                    <i class="bi bi-clipboard"></i>
                </button>
            </div>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-muted">Mot de passe initial</label>
            <div class="input-group">
                <input type="text" class="form-control form-control-sm font-monospace fw-bold"
                       value="<?= htmlspecialchars($credentials['password']) ?>" readonly id="pwd">
                <button class="btn btn-outline-secondary btn-sm" type="button"
                        onclick="navigator.clipboard.writeText(document.getElementById('pwd').value)">
                    <i class="bi bi-clipboard"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="mt-3">
        <a href="/admin/clients/create" class="btn btn-primary btn-sm me-2">
            <i class="bi bi-plus-lg me-1"></i>Créer un autre client
        </a>
        <a href="/admin/clients" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-list me-1"></i>Liste des clients
        </a>
    </div>
</div>
<?php else: ?>
<!-- ── Formulaire ─────────────────────────────────────────────────────────── -->
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body p-4">

                <?php if (!empty($error)): ?>
                <div class="alert alert-danger small"><i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="post" action="/admin/clients/create" novalidate>
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Prénom <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control"
                                   value="<?= htmlspecialchars($old['firstName'] ?? '') ?>" required autofocus>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nom <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control"
                                   value="<?= htmlspecialchars($old['lastName'] ?? '') ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control"
                                   value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Téléphone</label>
                            <input type="tel" name="phone" class="form-control"
                                   value="<?= htmlspecialchars($old['phone'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Statut</label>
                            <select name="status" class="form-select">
                                <?php foreach (['actif', 'inactif', 'suspendu'] as $s): ?>
                                <option value="<?= $s ?>" <?= ($old['status'] ?? 'actif') === $s ? 'selected' : '' ?>>
                                    <?= ucfirst($s) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <hr class="my-4">
                    <p class="small text-muted mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        Le numéro de compte (6 chiffres) et le mot de passe initial seront générés
                        automatiquement et affichés une seule fois.
                    </p>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-person-check me-2"></i>Créer le client
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require APP_PATH . '/Views/admin/layout/footer.php'; ?>
