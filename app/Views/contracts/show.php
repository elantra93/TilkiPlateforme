<?php
$pageTitle = 'Contrat ' . htmlspecialchars($contract['policy_number']) . ' – TILKI';

function docIcon(string $mime): string {
    return match(true) {
        $mime === 'application/pdf'        => 'bi-file-earmark-pdf text-danger',
        str_starts_with($mime, 'image/')   => 'bi-file-earmark-image text-info',
        str_contains($mime, 'word')        => 'bi-file-earmark-word text-primary',
        str_contains($mime, 'excel') || str_contains($mime, 'sheet') => 'bi-file-earmark-excel text-success',
        default                            => 'bi-file-earmark text-secondary',
    };
}

$byCategory = ['cotation' => [], 'souscription' => []];
foreach ($documents as $doc) {
    $cat = $doc['category'] === 'cotation' ? 'cotation' : 'souscription';
    $byCategory[$cat][] = $doc;
}

// Index des docs souscription par doc_type pour la vue structurée
$souscriptionByType = [];
foreach ($byCategory['souscription'] as $doc) {
    $souscriptionByType[$doc['doc_type']][] = $doc;
}
?>
<?php require APP_PATH . '/Views/layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <a href="/contracts" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Retour aux contrats
    </a>
    <?php if (!empty($tallyClaimUrl)): ?>
    <a href="<?= htmlspecialchars($tallyClaimUrl) ?>" target="_blank" rel="noopener"
       class="btn btn-danger btn-sm">
        <i class="bi bi-exclamation-triangle me-1"></i>Déclarer un sinistre
    </a>
    <?php endif; ?>
</div>

<div class="row g-4">

    <!-- ── Détails du contrat ──────────────────────────────────────────────── -->
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-header fw-semibold">
                <i class="bi bi-file-earmark-text me-2 text-primary"></i>Détails du contrat
            </div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-6 text-muted">Branche</dt>
                    <dd class="col-6 fw-semibold"><?= htmlspecialchars($contract['branche']) ?></dd>

                    <dt class="col-6 text-muted">N° Police</dt>
                    <dd class="col-6"><code class="text-body"><?= htmlspecialchars($contract['policy_number']) ?></code></dd>

                    <dt class="col-6 text-muted">Assureur</dt>
                    <dd class="col-6"><?= htmlspecialchars($contract['insurer']) ?></dd>

                    <dt class="col-6 text-muted">Date d'effet</dt>
                    <dd class="col-6"><?= date('d/m/Y', strtotime($contract['effective_date'])) ?></dd>

                    <dt class="col-6 text-muted">Date d'échéance</dt>
                    <dd class="col-6"><?= date('d/m/Y', strtotime($contract['expiry_date'])) ?></dd>

                    <dt class="col-6 text-muted">Prime totale</dt>
                    <dd class="col-6">
                        <?= number_format((float)$contract['premium_total'], 0, ',', ' ') ?>
                        <?= htmlspecialchars($contract['currency']) ?>
                    </dd>

                    <dt class="col-6 text-muted">Restant dû</dt>
                    <dd class="col-6">
                        <?php if ((float)$contract['premium_due'] <= 0): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle fw-normal">
                                <i class="bi bi-check2 me-1"></i>À jour
                            </span>
                        <?php else: ?>
                            <span class="fw-semibold text-danger">
                                <?= number_format((float)$contract['premium_due'], 0, ',', ' ') ?>
                                <?= htmlspecialchars($contract['currency']) ?>
                            </span>
                        <?php endif; ?>
                    </dd>

                    <dt class="col-6 text-muted">Statut</dt>
                    <dd class="col-6">
                        <span class="badge bg-<?= $contract['status'] === 'actif' ? 'success' : 'secondary' ?>">
                            <?= htmlspecialchars($contract['status']) ?>
                        </span>
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    <!-- ── Documents + Upload ─────────────────────────────────────────────── -->
    <div class="col-lg-8 d-flex flex-column gap-4">

        <!-- ── Cotation (inchangée) ──────────────────────────────────────────── -->
        <div class="card shadow-sm">
            <div class="card-header d-flex align-items-center gap-2 fw-semibold">
                <i class="bi bi-clipboard-data text-info"></i>
                Cotation
                <span class="badge bg-secondary fw-normal ms-1"><?= count($byCategory['cotation']) ?></span>
            </div>
            <?php if (empty($byCategory['cotation'])): ?>
                <div class="card-body text-muted small py-4 text-center">
                    <i class="bi bi-inbox fs-4 d-block mb-1 opacity-25"></i>
                    Aucun document dans cette section.
                </div>
            <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($byCategory['cotation'] as $doc): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <div class="me-3 overflow-hidden">
                            <i class="bi <?= docIcon($doc['mime_type']) ?> me-2"></i>
                            <span class="small fw-semibold"><?= htmlspecialchars($doc['original_filename']) ?></span>
                            <div class="text-muted mt-1 fs-xs">
                                <?= htmlspecialchars($doc['doc_type']) ?>
                                &bull; <?= number_format($doc['file_size'] / 1024, 0) ?>&nbsp;Ko
                                &bull; <?= date('d/m/Y', strtotime($doc['created_at'])) ?>
                            </div>
                        </div>
                        <?php if ($doc['status'] === 'valide'): ?>
                            <a href="/documents/<?= (int)$doc['id'] ?>/download"
                               class="btn btn-sm btn-outline-primary flex-shrink-0">
                                <i class="bi bi-download me-1"></i>Télécharger
                            </a>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark flex-shrink-0">En attente</span>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <!-- ── Documents du contrat (structurés par branche) ─────────────────── -->
        <div class="card shadow-sm">
            <div class="card-header d-flex align-items-center gap-2 fw-semibold">
                <i class="bi bi-file-earmark-check text-primary"></i>
                Documents du contrat
                <span class="badge bg-secondary fw-normal ms-1"><?= count($byCategory['souscription']) ?></span>
            </div>

            <?php if (!empty($branchDocTypes)): ?>
            <!-- Vue structurée : types attendus pour cette branche -->
            <ul class="list-group list-group-flush">
                <?php foreach ($branchDocTypes as $expected):
                    $docs       = $souscriptionByType[$expected['key']] ?? [];
                    $isRequired = $expected['required'] ?? false;
                ?>
                <?php
                    $hasDocs     = !empty($docs);
                    $firstDoc    = $hasDocs ? $docs[0] : null;
                    $isFourni    = $hasDocs && $firstDoc['status'] === 'valide';
                    $isEnAttente = $hasDocs && $firstDoc['status'] !== 'valide';
                ?>
                <li class="list-group-item py-3">
                    <div class="d-flex justify-content-between align-items-center gap-3">
                        <div class="flex-grow-1 min-w-0">
                            <div class="small fw-semibold mb-1 text-body d-flex align-items-center gap-2 flex-wrap">
                                <?= htmlspecialchars($expected['label']) ?>
                                <?php if ($isFourni): ?>
                                    <span class="badge tk-doc-fourni">fourni</span>
                                <?php elseif ($isEnAttente): ?>
                                    <span class="badge tk-doc-attente">en attente</span>
                                <?php elseif ($isRequired): ?>
                                    <span class="badge tk-doc-requis">requis</span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($docs)): ?>
                                <?php foreach ($docs as $doc): ?>
                                <div class="d-flex align-items-center gap-2 mt-1">
                                    <i class="bi <?= docIcon($doc['mime_type']) ?> small"></i>
                                    <span class="fs-sm7 text-muted">
                                        <?= htmlspecialchars($doc['original_filename']) ?>
                                        &bull; <?= number_format($doc['file_size'] / 1024, 0) ?>&nbsp;Ko
                                        &bull; <?= date('d/m/Y', strtotime($doc['created_at'])) ?>
                                    </span>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-muted opacity-75 fs-sm7">
                                    <i class="bi bi-dash me-1"></i>Aucun document pour le moment
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex flex-column gap-1 flex-shrink-0">
                            <?php foreach ($docs as $doc): ?>
                                <?php if ($doc['status'] === 'valide'): ?>
                                <a href="/documents/<?= (int)$doc['id'] ?>/download"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-download me-1"></i>Télécharger
                                </a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </li>
                <?php endforeach; ?>

                <?php
                // Documents hors liste attendue (types libres ajoutés par l'admin)
                $expectedKeys = array_column($branchDocTypes, 'key');
                $extras = array_filter($byCategory['souscription'], fn($d) => !in_array($d['doc_type'], $expectedKeys, true));
                foreach ($extras as $doc):
                ?>
                <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                    <div class="me-3 overflow-hidden">
                        <i class="bi <?= docIcon($doc['mime_type']) ?> me-2"></i>
                        <span class="small fw-semibold"><?= htmlspecialchars($doc['original_filename']) ?></span>
                        <div class="text-muted mt-1 fs-xs">
                            <?= htmlspecialchars(str_replace('_', ' ', $doc['doc_type'])) ?>
                            &bull; <?= number_format($doc['file_size'] / 1024, 0) ?>&nbsp;Ko
                            &bull; <?= date('d/m/Y', strtotime($doc['created_at'])) ?>
                        </div>
                    </div>
                    <?php if ($doc['status'] === 'valide'): ?>
                        <a href="/documents/<?= (int)$doc['id'] ?>/download"
                           class="btn btn-sm btn-outline-primary flex-shrink-0">
                            <i class="bi bi-download me-1"></i>Télécharger
                        </a>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark flex-shrink-0">En attente</span>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>

            <?php else: ?>
            <!-- Vue générique : liste plate pour les autres branches -->
            <?php if (empty($byCategory['souscription'])): ?>
                <div class="card-body text-muted small py-4 text-center">
                    <i class="bi bi-inbox fs-4 d-block mb-1 opacity-25"></i>
                    Aucun document pour le moment.
                </div>
            <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($byCategory['souscription'] as $doc): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <div class="me-3 overflow-hidden">
                            <i class="bi <?= docIcon($doc['mime_type']) ?> me-2"></i>
                            <span class="small fw-semibold"><?= htmlspecialchars($doc['original_filename']) ?></span>
                            <div class="text-muted mt-1 fs-xs">
                                <?= htmlspecialchars(str_replace('_', ' ', $doc['doc_type'])) ?>
                                &bull; <?= number_format($doc['file_size'] / 1024, 0) ?>&nbsp;Ko
                                &bull; <?= date('d/m/Y', strtotime($doc['created_at'])) ?>
                                <?php if ($doc['source'] === 'client'): ?>
                                    &bull; <em>déposé par vous</em>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if ($doc['status'] === 'valide'): ?>
                            <a href="/documents/<?= (int)$doc['id'] ?>/download"
                               class="btn btn-sm btn-outline-primary flex-shrink-0">
                                <i class="bi bi-download me-1"></i>Télécharger
                            </a>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark flex-shrink-0">En attente</span>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- ── Flotte / Véhicules ─────────────────────────────────────── -->
        <?php
        $vehicles ??= [];
        $isVehicleBranche ??= false;
        ?>
        <?php if ($isVehicleBranche): ?>
        <div class="card shadow-sm">
            <div class="card-header fw-semibold d-flex align-items-center gap-2">
                <i class="bi bi-car-front text-primary"></i>
                Flotte / Véhicules
                <?php if (!empty($vehicles)): ?>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-normal ms-1"><?= count($vehicles) ?></span>
                <?php endif; ?>
            </div>
            <?php if (empty($vehicles)): ?>
            <div class="card-body text-muted small text-center py-4">
                <i class="bi bi-car-front opacity-25 fs-2 d-block mb-2"></i>
                Aucun véhicule enregistré. Contactez votre conseiller.
            </div>
            <?php else: ?>
            <ul class="list-group list-group-flush">
                <?php foreach ($vehicles as $veh): ?>
                <li class="list-group-item py-3">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div>
                            <div class="fw-semibold font-monospace text-body">
                                <?= htmlspecialchars($veh['immatriculation']) ?>
                            </div>
                            <div class="small text-muted mt-1 d-flex flex-wrap gap-2">
                                <span><?= htmlspecialchars($veh['marque']) ?><?= $veh['modele'] ? ' ' . htmlspecialchars($veh['modele']) : '' ?></span>
                                <?php if ($veh['annee']): ?>
                                <span>&middot; <?= (int)$veh['annee'] ?></span>
                                <?php endif; ?>
                                <?php if ($veh['energie']): ?>
                                <span>&middot; <?= ucfirst($veh['energie']) ?></span>
                                <?php endif; ?>
                                <span>&middot; <?= ucfirst($veh['usage']) ?></span>
                            </div>
                        </div>
                        <?php if ($veh['valeur_venale']): ?>
                        <div class="text-end flex-shrink-0">
                            <div class="small text-muted">Valeur vénale</div>
                            <div class="small fw-semibold font-monospace">
                                <?= number_format((float)$veh['valeur_venale'], 0, ',', ' ') ?>&nbsp;XOF
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- ── Bénéficiaires santé ───────────────────────────────────────── -->
        <?php
        $beneficiaries ??= [];
        $isSanteBranche ??= false;
        $relationLabels = [
            'souscripteur' => 'Souscripteur',
            'conjoint'     => 'Conjoint(e)',
            'enfant'       => 'Enfant',
            'parent'       => 'Parent',
            'autre'        => 'Autre',
        ];
        ?>
        <?php if ($isSanteBranche): ?>
        <div class="card shadow-sm">
            <div class="card-header fw-semibold d-flex align-items-center gap-2">
                <i class="bi bi-person-heart text-success"></i>
                Bénéficiaires
                <?php if (!empty($beneficiaries)): ?>
                <span class="badge bg-success-subtle text-success border border-success-subtle fw-normal ms-1"><?= count($beneficiaries) ?></span>
                <?php endif; ?>
            </div>
            <?php if (empty($beneficiaries)): ?>
            <div class="card-body text-muted small text-center py-4">
                <i class="bi bi-person-heart opacity-25 fs-2 d-block mb-2"></i>
                Aucun bénéficiaire enregistré. Contactez votre conseiller.
            </div>
            <?php else: ?>
            <ul class="list-group list-group-flush">
                <?php foreach ($beneficiaries as $ben): ?>
                <li class="list-group-item py-3">
                    <div class="d-flex justify-content-between align-items-center gap-3">
                        <div>
                            <div class="fw-semibold text-body d-flex align-items-center gap-2">
                                <?= htmlspecialchars($ben['first_name'] . ' ' . $ben['last_name']) ?>
                                <?php if ($ben['is_principal']): ?>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-normal" style="font-size:.65rem">Principal</span>
                                <?php endif; ?>
                            </div>
                            <div class="small text-muted mt-1 d-flex flex-wrap gap-2">
                                <span><?= $relationLabels[$ben['relation']] ?? ucfirst($ben['relation']) ?></span>
                                <?php if ($ben['gender']): ?>
                                <span>&middot; <?= $ben['gender'] === 'M' ? 'Homme' : 'Femme' ?></span>
                                <?php endif; ?>
                                <?php if ($ben['birth_date']): ?>
                                <span>&middot; né<?= $ben['gender'] === 'F' ? 'e' : '' ?> le <?= date('d/m/Y', strtotime($ben['birth_date'])) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if ($ben['matricule']): ?>
                        <div class="text-end flex-shrink-0">
                            <div class="small text-muted">N° adhérent</div>
                            <div class="small fw-semibold font-monospace"><?= htmlspecialchars($ben['matricule']) ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- ── Historique des paiements soumis ──────────────────────────── -->
        <?php if (!empty($payments)): ?>
        <?php
        $methodLabels = [
            'especes'      => 'Espèces',
            'virement'     => 'Virement',
            'cheque'       => 'Chèque',
            'mobile_money' => 'Mobile Money',
            'carte'        => 'Carte',
            'caisse'       => 'Caisse',
        ];
        ?>
        <div class="card shadow-sm">
            <div class="card-header fw-semibold">
                <i class="bi bi-clock-history me-2 text-secondary"></i>Paiements soumis
                <span class="badge bg-secondary fw-normal ms-1"><?= count($payments) ?></span>
            </div>
            <ul class="list-group list-group-flush">
                <?php foreach ($payments as $p): ?>
                <li class="list-group-item py-3">
                    <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                        <div>
                            <div class="fw-semibold small">
                                <?= number_format((float)$p['amount'], 0, ',', ' ') ?>&nbsp;<?= htmlspecialchars($contract['currency']) ?>
                                <span class="text-muted fw-normal ms-1">·</span>
                                <span class="text-muted fw-normal"><?= $methodLabels[$p['method']] ?? htmlspecialchars($p['method']) ?></span>
                            </div>
                            <div class="text-muted mt-1 fs-xs">
                                Soumis le <?= date('d/m/Y', strtotime($p['created_at'])) ?>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-shrink-0">
                            <?php if ($p['doc_id']): ?>
                            <a href="/documents/<?= (int)$p['doc_id'] ?>/download"
                               class="btn btn-sm btn-outline-secondary py-0">
                                <i class="bi bi-download me-1"></i><span class="small">Preuve</span>
                            </a>
                            <?php endif; ?>
                            <?php if ($p['status'] === 'valide'): ?>
                                <span class="badge bg-success">Validé</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">En attente</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- ── Formulaire de règlement ────────────────────────────────────── -->
        <div class="card shadow-sm">
            <div class="card-header fw-semibold">
                <i class="bi bi-cash-coin me-2 text-primary"></i>Effectuer un règlement
            </div>
            <div class="card-body">
                <form id="paymentForm"
                      method="post"
                      action="/contracts/<?= (int)$contract['id'] ?>/payment"
                      enctype="multipart/form-data"
                      novalidate>
                    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

                    <div class="row g-3">
                        <div class="col-sm-5">
                            <label class="form-label small fw-semibold">
                                Montant <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number"
                                       id="payAmt"
                                       name="amount"
                                       class="form-control"
                                       min="1"
                                       step="1"
                                       placeholder="0"
                                       required>
                                <span class="input-group-text font-mono"><?= htmlspecialchars($contract['currency']) ?></span>
                            </div>
                        </div>
                        <div class="col-sm-7">
                            <label class="form-label small fw-semibold">
                                Moyen <span class="text-danger">*</span>
                            </label>
                            <select id="payMethod" name="method" class="form-select" required>
                                <option value="">— Sélectionner —</option>
                                <option value="virement">Virement bancaire</option>
                                <option value="mobile_money">Mobile Money</option>
                                <option value="especes">Espèces</option>
                                <option value="cheque">Chèque</option>
                                <option value="carte">Carte</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">
                                Justificatif <span class="text-danger">*</span>
                                <span class="text-muted fw-normal">(PDF, JPG ou PNG – max&nbsp;10&nbsp;Mo)</span>
                            </label>
                            <input type="file"
                                   name="document"
                                   class="form-control"
                                   accept=".pdf,.jpg,.jpeg,.png"
                                   required>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="button" class="btn btn-primary" onclick="tkOpenPayModal()">
                            <i class="bi bi-send me-2"></i>Déclarer le règlement
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modale confirmation règlement -->
        <div class="modal fade" id="payConfirmModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold">Confirmer le règlement</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small mb-3">Vous déclarez un règlement par <span id="mc-method" class="fw-semibold"></span>. Il sera vérifié puis validé par votre conseiller.</p>
                        <div class="tk-confirm-row">
                            <span class="text-muted small">Contrat</span>
                            <span class="fw-semibold small"><?= htmlspecialchars($contract['branche']) ?> &middot; <span class="font-mono"><?= htmlspecialchars($contract['policy_number']) ?></span></span>
                        </div>
                        <div class="tk-confirm-row">
                            <span class="text-muted small">Montant</span>
                            <span class="fw-semibold font-mono" id="mc-amount"></span>
                        </div>
                        <div class="tk-confirm-row border-0">
                            <span class="text-muted small">Statut après envoi</span>
                            <span class="badge tk-badge-en_attente">en attente</span>
                        </div>
                        <p class="small text-muted mt-3 mb-0">
                            <i class="bi bi-info-circle me-1"></i>Votre règlement passe en <strong>attente</strong> jusqu'à validation par votre conseiller.
                        </p>
                    </div>
                    <div class="modal-footer border-0 pt-0 gap-2">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="button" class="btn btn-primary" onclick="document.getElementById('paymentForm').submit()">
                            Confirmer
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <script>
        function tkOpenPayModal() {
            const form = document.getElementById('paymentForm');
            if (!form.reportValidity()) return;
            const amt    = document.getElementById('payAmt').value;
            const method = document.getElementById('payMethod');
            const methodText = method.options[method.selectedIndex].text;
            document.getElementById('mc-amount').textContent = parseInt(amt).toLocaleString('fr-FR') + ' <?= htmlspecialchars($contract['currency']) ?>';
            document.getElementById('mc-method').textContent = methodText;
            new bootstrap.Modal(document.getElementById('payConfirmModal')).show();
        }
        </script>

    </div><!-- /col -->
</div><!-- /row -->

<?php require APP_PATH . '/Views/layout/footer.php'; ?>
