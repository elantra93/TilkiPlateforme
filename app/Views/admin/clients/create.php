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
                        data-copy-target="acc">
                    <i class="bi bi-clipboard"></i>
                </button>
            </div>
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold text-muted">PIN initial</label>
            <div class="input-group">
                <input type="text" class="form-control form-control-sm font-monospace fw-bold"
                       value="<?= htmlspecialchars($credentials['pin']) ?>" readonly id="pwd">
                <button class="btn btn-outline-secondary btn-sm" type="button"
                        data-copy-target="pwd">
                    <i class="bi bi-clipboard"></i>
                </button>
            </div>
            <div class="form-text small text-muted">Le client devra le modifier à la première connexion.</div>
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
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-body p-4">

                <?php if (!empty($error)): ?>
                <div class="alert alert-danger small"><i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="post" action="/admin/clients/create" novalidate>
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

                    <!-- Type de compte -->
                    <div class="mb-4">
                        <label class="form-label small fw-semibold d-block">Type de compte</label>
                        <div class="btn-group" role="group">
                            <?php $atVal = $old['accountType'] ?? 'individuel'; ?>
                            <input type="radio" class="btn-check" name="account_type" id="at_create_ind"
                                   value="individuel" data-account-type-toggle
                                   <?= $atVal === 'individuel' ? 'checked' : '' ?>>
                            <label class="btn btn-outline-primary" for="at_create_ind">
                                <i class="bi bi-person me-1"></i>Individuel
                            </label>
                            <input type="radio" class="btn-check" name="account_type" id="at_create_ent"
                                   value="entreprise" data-account-type-toggle
                                   <?= $atVal === 'entreprise' ? 'checked' : '' ?>>
                            <label class="btn btn-outline-primary" for="at_create_ent">
                                <i class="bi bi-building me-1"></i>Entreprise
                            </label>
                        </div>
                    </div>

                    <!-- Identité de base -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Prénoms <span class="text-danger">*</span></label>
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
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Statut</label>
                            <select name="status" class="form-select">
                                <?php foreach (['actif','inactif','suspendu'] as $s): ?>
                                <option value="<?= $s ?>" <?= ($old['status'] ?? 'actif') === $s ? 'selected' : '' ?>>
                                    <?= ucfirst($s) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">
                                N° de compte <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="account_number" class="form-control font-monospace"
                                   value="<?= htmlspecialchars($old['accountNumber'] ?? $nextAccountNumber) ?>"
                                   maxlength="6" pattern="\d{6}" inputmode="numeric" required>
                            <div class="form-text"><i class="bi bi-pencil me-1"></i>Suggéré — modifiable.</div>
                        </div>
                    </div>

                    <!-- Identité entreprise (masquée par défaut) -->
                    <div data-enterprise-section
                         class="<?= $atVal === 'entreprise' ? '' : 'd-none' ?>">
                        <hr class="my-4">
                        <p class="small fw-semibold text-primary mb-3">
                            <i class="bi bi-building me-1"></i>Identité entreprise
                        </p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Raison sociale <span class="text-danger">*</span></label>
                                <input type="text" name="company_name" class="form-control"
                                       value="<?= htmlspecialchars($old['company_name'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">N° RCCM</label>
                                <input type="text" name="company_rccm" class="form-control font-monospace"
                                       value="<?= htmlspecialchars($old['company_rccm'] ?? '') ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">N° DFE</label>
                                <input type="text" name="company_dfe" class="form-control font-monospace"
                                       value="<?= htmlspecialchars($old['company_dfe'] ?? '') ?>">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label small fw-semibold">Adresse du siège</label>
                                <input type="text" name="company_address" class="form-control"
                                       value="<?= htmlspecialchars($old['company_address'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Ville</label>
                                <input type="text" name="company_city" class="form-control"
                                       value="<?= htmlspecialchars($old['company_city'] ?? '') ?>">
                            </div>

                        </div>
                    </div>

                    <hr class="my-4">
                    <p class="small text-muted mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        Le code PIN initial <strong>12345678</strong> sera affiché une seule fois. Le client devra le modifier à sa première connexion.
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
