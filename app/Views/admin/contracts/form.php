<?php
$isEdit    = $contract !== null;
$pageTitle = ($isEdit ? 'Modifier contrat' : 'Nouveau contrat') . ' – Administration TILKI';
$action    = $isEdit ? '/admin/contracts/' . (int)$contract['id'] . '/edit' : '/admin/contracts/create';

function v(string $key, array $old, ?array $contract, mixed $default = ''): mixed {
    return $old[$key] ?? $contract[$key] ?? $default;
}

// Labels des types de documents
$docTypeLabels = [
    'questionnaire'            => 'Questionnaire',
    'cotation'                 => 'Cotation',
    'bordereau'                => 'Bordereau',
    'note_de_couverture'       => 'Note de couverture',
    'conditions_particulieres' => 'Conditions particulières',
    'attestation_assurance'    => "Attestation d'assurance",
    'attestation_cedeao'       => 'Attestation CEDEAO',
    'conditions_generales'     => 'Conditions générales',
    'contrat'                  => 'Contrat',
    'avenant'                  => 'Avenant',
    'preuve_paiement'          => 'Preuve de paiement',
    'quittance'                => 'Quittance',
    'attestation'              => 'Attestation',
    'decompte'                 => 'Décompte',
    'tableau_garanties'        => 'Tableau de garanties',
    'reseau_soins'             => 'Réseau de soins',
];
?>
<?php require APP_PATH . '/Views/admin/layout/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h5 fw-bold mb-0">
        <i class="bi bi-<?= $isEdit ? 'pencil' : 'plus-lg' ?> me-2"></i>
        <?= $isEdit ? 'Modifier le contrat' : 'Nouveau contrat' ?>
    </h2>
    <div class="d-flex gap-2">
        <?php if ($isEdit): ?>
        <a href="/admin/payments/create?client_id=<?= (int)$contract['client_id'] ?>&contract_id=<?= (int)$contract['id'] ?>"
           class="btn btn-sm btn-outline-primary">
            <i class="bi bi-cash-coin me-1"></i>Enregistrer un paiement
        </a>
        <?php endif; ?>
        <a href="/admin/contracts" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Retour
        </a>
    </div>
</div>

<div class="row justify-content-center">
<div class="col-xl-9">
<div class="card shadow-sm">
<div class="card-body p-4">

<?php if (!empty($error)): ?>
<div class="alert alert-danger small"><i class="bi bi-exclamation-circle me-1"></i><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="post" action="<?= $action ?>" novalidate>
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">

    <div class="row g-3">

        <!-- Client -->
        <?php if (!$isEdit): ?>
        <div class="col-12">
            <label class="form-label small fw-semibold">Client <span class="text-danger">*</span></label>
            <select name="client_id" class="form-select" required>
                <option value="">— Sélectionner un client —</option>
                <?php foreach ($clients as $cl): ?>
                <option value="<?= (int)$cl['id'] ?>"
                    <?= (int)v('client_id', $old, null, 0) === (int)$cl['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cl['account_number'] . ' — ' . $cl['first_name'] . ' ' . $cl['last_name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php else: ?>
        <div class="col-12">
            <label class="form-label small fw-semibold text-muted">Client</label>
            <?php
                $owner = null;
                foreach ($clients as $cl) {
                    if ((int)$cl['id'] === (int)$contract['client_id']) { $owner = $cl; break; }
                }
            ?>
            <div class="form-control-plaintext fw-semibold">
                <?= $owner ? htmlspecialchars($owner['account_number'] . ' — ' . $owner['first_name'] . ' ' . $owner['last_name']) : '—' ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Branche -->
        <div class="col-md-5">
            <label class="form-label small fw-semibold">Branche <span class="text-danger">*</span></label>
            <select name="branche" class="form-select" required>
                <option value="">— Sélectionner —</option>
                <?php foreach ($branches as $b): ?>
                <option value="<?= htmlspecialchars($b) ?>"
                    <?= v('branche', $old, $contract) === $b ? 'selected' : '' ?>>
                    <?= htmlspecialchars($b) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- N° Police -->
        <div class="col-md-7">
            <label class="form-label small fw-semibold">N° Police <span class="text-danger">*</span></label>
            <input type="text" name="policy_number" class="form-control font-monospace"
                   value="<?= htmlspecialchars((string)v('policy_number', $old, $contract)) ?>" required>
        </div>

        <!-- Assureur -->
        <div class="col-12">
            <label class="form-label small fw-semibold">Assureur <span class="text-danger">*</span></label>
            <?php
            // Pré-sélection : insurer_id en priorité, sinon match par nom sur l'ancienne valeur texte
            $selInsId = (int)v('insurer_id', $old, $contract, 0);
            if (!$selInsId && ($oldInsurerText = v('insurer', $old, $contract, ''))) {
                foreach ($insurers as $_i) {
                    if ($_i['name'] === $oldInsurerText) { $selInsId = (int)$_i['id']; break; }
                }
            }
            ?>
            <select name="insurer_id" class="form-select" required>
                <option value="">— Sélectionner un assureur —</option>
                <?php foreach ($insurers as $ins): ?>
                <option value="<?= (int)$ins['id'] ?>"
                    <?= $selInsId === (int)$ins['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($ins['name']) ?>
                    <?php if ($ins['short_name']): ?>
                    (<?= htmlspecialchars($ins['short_name']) ?>)
                    <?php endif; ?>
                </option>
                <?php endforeach; ?>
            </select>
            <div class="form-text">
                <a href="/admin/insurers" target="_blank" class="small">
                    <i class="bi bi-box-arrow-up-right me-1"></i>Gérer les assureurs
                </a>
            </div>
        </div>

        <!-- Dates -->
        <div class="col-md-4">
            <label class="form-label small fw-semibold">Date d'émission</label>
            <input type="date" name="emission_date" class="form-control"
                   value="<?= htmlspecialchars((string)v('emission_date', $old, $contract)) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label small fw-semibold">Date d'effet <span class="text-danger">*</span></label>
            <input type="date" name="effective_date" class="form-control"
                   value="<?= htmlspecialchars((string)v('effective_date', $old, $contract)) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label small fw-semibold">Date d'échéance <span class="text-danger">*</span></label>
            <input type="date" name="expiry_date" class="form-control"
                   value="<?= htmlspecialchars((string)v('expiry_date', $old, $contract)) ?>" required>
        </div>

        <!-- Primes -->
        <div class="col-md-3">
            <label class="form-label small fw-semibold">Prime nette</label>
            <input type="number" name="premium_net" class="form-control" step="0.01" min="0"
                   value="<?= htmlspecialchars((string)v('premium_net', $old, $contract, '0')) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold">Frais &amp; taxes</label>
            <input type="number" name="premium_fees" class="form-control" step="0.01" min="0"
                   value="<?= htmlspecialchars((string)v('premium_fees', $old, $contract, '0')) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold">Prime totale</label>
            <input type="number" name="premium_total" class="form-control" step="0.01" min="0"
                   value="<?= htmlspecialchars((string)v('premium_total', $old, $contract, '0')) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold">Restant dû</label>
            <?php if ($contract): ?>
                <?php $currency = htmlspecialchars((string)v('currency', $old, $contract, 'XOF')); ?>
                <?php if (($premiumDue ?? 0) <= 0): ?>
                <div class="form-control-plaintext">
                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                        <i class="bi bi-check2 me-1"></i>À jour
                    </span>
                    <div class="form-text">Calculé automatiquement</div>
                </div>
                <?php else: ?>
                <div class="form-control-plaintext fw-semibold text-danger">
                    <?= number_format((float)($premiumDue ?? 0), 0, ',', ' ') ?> <?= $currency ?>
                    <div class="form-text text-muted">prime totale – paiements validés</div>
                </div>
                <?php endif; ?>
            <?php else: ?>
            <div class="form-control-plaintext text-muted fst-italic small">Calculé après création</div>
            <?php endif; ?>
        </div>

        <!-- Devise / Statut -->
        <div class="col-md-3">
            <label class="form-label small fw-semibold">Devise</label>
            <input type="text" name="currency" class="form-control" maxlength="3"
                   value="<?= htmlspecialchars((string)v('currency', $old, $contract, 'XOF')) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label small fw-semibold">Statut</label>
            <select name="status" class="form-select">
                <?php foreach (['actif', 'expiré', 'résilié', 'suspendu'] as $s): ?>
                <option value="<?= $s ?>" <?= v('status', $old, $contract, 'actif') === $s ? 'selected' : '' ?>>
                    <?= htmlspecialchars(ucfirst($s)) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <hr class="my-4">
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-<?= $isEdit ? 'save' : 'plus-lg' ?> me-2"></i>
            <?= $isEdit ? 'Enregistrer les modifications' : 'Créer le contrat' ?>
        </button>
        <a href="/admin/contracts" class="btn btn-outline-secondary">Annuler</a>
    </div>
</form>

</div>
</div>
</div>
</div>

<?php if ($isEdit): ?>
<!-- ── Documents du contrat ──────────────────────────────────────────────────── -->
<?php
// Lookup des docs déjà uploadés par type
$uploadedByType = [];
foreach ($documents ?? [] as $doc) {
    $uploadedByType[$doc['doc_type']][] = $doc;
}
?>
<div class="row justify-content-center mt-4">
<div class="col-xl-9">
<div class="card shadow-sm"
     id="contractDocSection"
     data-doc-types="<?= htmlspecialchars(json_encode($contractDocTypes ?? [], JSON_HEX_TAG)) ?>"
     data-doc-labels="<?= htmlspecialchars(json_encode($docTypeLabels, JSON_HEX_TAG)) ?>">

    <div class="card-header fw-semibold">
        <i class="bi bi-paperclip me-2 text-secondary"></i>Documents du contrat
    </div>

    <!-- ── Ajouter un document ────────────────────────────────────────────── -->
    <div class="p-3 border-bottom bg-light">
        <p class="small fw-semibold mb-2"><i class="bi bi-upload me-1"></i>Ajouter un document</p>
        <form method="post"
              action="/admin/contracts/<?= (int)$contract['id'] ?>/upload"
              enctype="multipart/form-data"
              class="row g-2 align-items-end">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
            <div class="col-md-3">
                <label class="form-label small mb-1">Catégorie</label>
                <select name="category" id="contractCatSel" class="form-select form-select-sm" required>
                    <option value="">— Choisir —</option>
                    <option value="cotation">Cotation</option>
                    <option value="souscription">Souscription</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Type de document</label>
                <select name="doc_type" id="contractDocTypeSel" class="form-select form-select-sm" required>
                    <option value="">— Choisir une catégorie —</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small mb-1">
                    Fichier <span class="text-muted fw-normal">(PDF, image, Word, Excel – max 10 Mo)</span>
                </label>
                <input type="file" name="document" class="form-control form-control-sm"
                       accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx" required>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-upload me-1"></i>Envoyer
                </button>
            </div>
        </form>
    </div>

    <!-- ── Checklist par type attendu ─────────────────────────────────────── -->
    <?php
    $catLabels = ['cotation' => 'Cotation', 'souscription' => 'Souscription'];
    foreach ($contractDocTypes ?? [] as $cat => $types):
        if (empty($types)) continue;
    ?>
    <div class="px-3 pt-3 pb-2 border-bottom">
        <p class="small fw-semibold text-muted text-uppercase mb-2">
            <?= htmlspecialchars($catLabels[$cat] ?? $cat) ?>
        </p>
        <?php foreach ($types as $typeKey): ?>
        <?php
            $typeLabel   = $docTypeLabels[$typeKey] ?? str_replace('_', ' ', $typeKey);
            $uploadedDocs = $uploadedByType[$typeKey] ?? [];
            $rowId        = 'na_' . htmlspecialchars($typeKey);
        ?>
        <div class="doc-type-row d-flex align-items-start gap-3 py-2 border-bottom border-light">
            <!-- Type label -->
            <div class="flex-grow-1">
                <div class="small fw-semibold"><?= htmlspecialchars($typeLabel) ?></div>
                <?php if (!empty($uploadedDocs)): ?>
                    <?php foreach ($uploadedDocs as $ud): ?>
                    <div class="d-flex align-items-center gap-2 mt-1">
                        <i class="bi bi-file-earmark-check text-success small"></i>
                        <span class="small text-truncate" style="max-width:240px">
                            <?= htmlspecialchars($ud['original_filename']) ?>
                        </span>
                        <span class="text-muted small"><?= date('d/m/Y', strtotime($ud['created_at'])) ?></span>
                        <a href="/admin/documents/<?= (int)$ud['id'] ?>/download"
                           class="btn btn-sm btn-outline-secondary py-0 px-1" target="_blank">
                            <i class="bi bi-download"></i>
                        </a>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                <div class="doc-upload-area">
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle small mt-1">
                        <i class="bi bi-hourglass me-1"></i>En attente
                    </span>
                </div>
                <div class="doc-na-badge mt-1" style="display:none">
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle small">
                        <i class="bi bi-slash-circle me-1"></i>Non applicable
                    </span>
                </div>
                <?php endif; ?>
            </div>

            <!-- Checkbox non applicable -->
            <?php if (empty($uploadedDocs)): ?>
            <div class="flex-shrink-0 mt-1">
                <div class="form-check">
                    <input class="form-check-input doc-na-check" type="checkbox"
                           id="<?= $rowId ?>" title="Marquer comme non applicable">
                    <label class="form-check-label small text-muted" for="<?= $rowId ?>">N/A</label>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>

    <!-- ── Documents hors checklist (catégories libres) ──────────────────── -->
    <?php
    $listedTypes = array_merge(...array_values($contractDocTypes ?? []));
    $extraDocs   = array_filter($documents ?? [], fn($d) => !in_array($d['doc_type'], $listedTypes, true));
    if (!empty($extraDocs)):
    ?>
    <div class="px-3 pt-3 pb-2">
        <p class="small fw-semibold text-muted text-uppercase mb-2">Autres documents</p>
        <?php foreach ($extraDocs as $doc): ?>
        <div class="d-flex align-items-center gap-2 py-1">
            <i class="bi bi-file-earmark-text text-secondary flex-shrink-0"></i>
            <span class="small flex-grow-1 text-truncate">
                <?= htmlspecialchars($doc['original_filename']) ?>
                <span class="text-muted">(<?= htmlspecialchars($docTypeLabels[$doc['doc_type']] ?? $doc['doc_type']) ?>)</span>
            </span>
            <span class="small text-muted"><?= date('d/m/Y', strtotime($doc['created_at'])) ?></span>
            <a href="/admin/documents/<?= (int)$doc['id'] ?>/download"
               class="btn btn-sm btn-outline-secondary" target="_blank">
                <i class="bi bi-download"></i>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
    <?php elseif (empty($documents)): ?>
    <p class="text-muted small p-3 mb-0">
        <i class="bi bi-dash me-1 opacity-50"></i>Aucun document pour ce contrat.
    </p>
    <?php endif; ?>

</div>
</div>
</div>

<script src="/assets/js/contract-form.js"></script>

<!-- ── Paiements du contrat ──────────────────────────────────────────────────── -->
<div class="row justify-content-center mt-4">
<div class="col-xl-9">
<div class="card shadow-sm">
    <div class="card-header fw-semibold d-flex align-items-center justify-content-between">
        <span><i class="bi bi-cash-coin me-2 text-secondary"></i>Paiements</span>
        <?php
        $pendingCount = count(array_filter($payments ?? [], fn($p) => $p['status'] === 'en_attente'));
        ?>
        <?php if ($pendingCount > 0): ?>
        <span class="badge bg-warning text-dark"><?= $pendingCount ?> en attente</span>
        <?php endif; ?>
    </div>

    <!-- Formulaire : ajouter un paiement admin -->
    <div class="p-3 border-bottom bg-light">
        <p class="small fw-semibold mb-2"><i class="bi bi-plus-circle me-1"></i>Enregistrer un paiement</p>
        <form method="post"
              action="/admin/contracts/<?= (int)$contract['id'] ?>/payment"
              enctype="multipart/form-data"
              class="row g-2 align-items-end">
            <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
            <div class="col-md-2">
                <label class="form-label small mb-1">Montant <span class="text-danger">*</span></label>
                <input type="number" name="amount" class="form-control form-control-sm"
                       step="0.01" min="0.01" placeholder="0.00" required>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Mode <span class="text-danger">*</span></label>
                <select name="method" class="form-select form-select-sm" required>
                    <option value="">—</option>
                    <?php foreach ($paymentMethods ?? [] as $m): ?>
                    <option value="<?= $m ?>"><?= htmlspecialchars(ucfirst($m)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Date de paiement</label>
                <input type="date" name="paid_at" class="form-control form-control-sm"
                       value="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Référence</label>
                <input type="text" name="reference" class="form-control form-control-sm" placeholder="optionnel">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Justificatif</label>
                <input type="file" name="proof" class="form-control form-control-sm"
                       accept=".pdf,.jpg,.jpeg,.png">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-save me-1"></i>Enregistrer
                </button>
            </div>
        </form>
    </div>

    <!-- Liste des paiements -->
    <?php if (empty($payments)): ?>
    <p class="text-muted small p-3 mb-0">
        <i class="bi bi-dash me-1 opacity-50"></i>Aucun paiement enregistré pour ce contrat.
    </p>
    <?php else: ?>
    <div class="table-responsive">
    <table class="table table-sm align-middle mb-0 tbl-card-mobile">
        <thead class="table-light">
            <tr>
                <th>Date</th>
                <th>Montant</th>
                <th>Mode</th>
                <th>Créé par</th>
                <th>Statut</th>
                <th>Justificatif</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($payments ?? [] as $pay): ?>
        <?php
            $statusBadge = match($pay['status']) {
                'valide'      => 'bg-success',
                'en_attente'  => 'bg-warning text-dark',
                'rejeté'      => 'bg-danger',
                default       => 'bg-secondary',
            };
            $statusLabel = match($pay['status']) {
                'valide'     => 'Validé',
                'en_attente' => 'En attente',
                'rejeté'     => 'Rejeté',
                default      => htmlspecialchars($pay['status']),
            };
        ?>
        <tr>
            <td data-label="Date" class="small text-muted">
                <?= $pay['paid_at'] ? date('d/m/Y', strtotime($pay['paid_at'])) : date('d/m/Y', strtotime($pay['created_at'])) ?>
            </td>
            <td data-label="Montant" class="fw-semibold">
                <?php if ((float)$pay['amount'] > 0): ?>
                    <?= number_format((float)$pay['amount'], 0, ',', ' ') ?>
                    <?= htmlspecialchars($contract['currency']) ?>
                <?php else: ?>
                    <span class="text-muted fst-italic small">—</span>
                <?php endif; ?>
            </td>
            <td data-label="Mode">
                <span class="small"><?= htmlspecialchars(ucfirst($pay['method'])) ?></span>
            </td>
            <td data-label="Créé par">
                <span class="badge <?= $pay['created_by'] === 'admin' ? 'bg-primary' : 'bg-secondary' ?> small">
                    <?= htmlspecialchars(ucfirst($pay['created_by'])) ?>
                </span>
            </td>
            <td data-label="Statut">
                <span class="badge <?= $statusBadge ?>"><?= $statusLabel ?></span>
            </td>
            <td data-label="Justificatif">
                <?php if ($pay['doc_id'] ?? null): ?>
                <a href="/admin/documents/<?= (int)$pay['doc_id'] ?>/download"
                   class="btn btn-sm btn-outline-secondary py-0 px-1" target="_blank">
                    <i class="bi bi-download"></i>
                </a>
                <?php else: ?>
                <span class="text-muted small">—</span>
                <?php endif; ?>
            </td>
            <td data-label="">
                <?php if ($pay['status'] === 'en_attente'): ?>
                <button type="button" class="btn btn-sm btn-outline-primary btn-verify"
                    data-verify-type="payment"
                    data-id="<?= (int)$pay['id'] ?>"
                    data-contract-id="<?= (int)$contract['id'] ?>"
                    data-amount="<?= (float)$pay['amount'] ?>"
                    data-method="<?= htmlspecialchars($pay['method']) ?>"
                    data-reference="<?= htmlspecialchars($pay['reference'] ?? '') ?>"
                    data-date="<?= htmlspecialchars($pay['paid_at'] ? date('d/m/Y', strtotime($pay['paid_at'])) : date('d/m/Y', strtotime($pay['created_at']))) ?>"
                    data-created-by="<?= htmlspecialchars($pay['created_by']) ?>"
                    data-status="<?= htmlspecialchars($pay['status']) ?>"
                    data-doc-id="<?= ($pay['doc_id'] ?? '') ? (int)$pay['doc_id'] : '' ?>"
                    data-csrf="<?= htmlspecialchars($csrf) ?>">
                    <i class="bi bi-eye me-1"></i>Vérifier
                </button>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>
</div>
</div>

<!-- ── Flotte / Véhicules ────────────────────────────────────────────────────── -->
<?php
$_isVehicleBranche = in_array(strtolower(trim($contract['branche'] ?? '')), ['automobile', 'moto'], true);
$vehicles ??= [];
?>
<?php if ($isEdit): ?>
<div class="card shadow-sm mt-4">
    <div class="card-header fw-semibold d-flex align-items-center justify-content-between">
        <span><i class="bi bi-car-front me-2 text-primary"></i>Flotte / Véhicules
            <?php if (!empty($vehicles)): ?>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-1"><?= count($vehicles) ?></span>
            <?php endif; ?>
        </span>
        <button type="button" class="btn btn-sm btn-outline-primary"
                data-bs-toggle="collapse" data-bs-target="#addVehicleCollapse"
                aria-expanded="false">
            <i class="bi bi-plus-lg me-1"></i>Ajouter
        </button>
    </div>

    <!-- Formulaire d'ajout inline -->
    <div class="collapse" id="addVehicleCollapse">
        <div class="card-body border-bottom bg-light p-3">
            <form method="post"
                  action="/admin/contracts/<?= (int)$contract['id'] ?>/vehicles/create"
                  novalidate>
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
                <div class="row g-2 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Immat. <span class="text-danger">*</span></label>
                        <input type="text" name="immatriculation" class="form-control form-control-sm font-monospace text-uppercase"
                               placeholder="AB 123 CI" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Marque <span class="text-danger">*</span></label>
                        <input type="text" name="marque" class="form-control form-control-sm"
                               placeholder="Toyota" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Modèle</label>
                        <input type="text" name="modele" class="form-control form-control-sm"
                               placeholder="Corolla">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label small fw-semibold">Année</label>
                        <input type="number" name="annee" class="form-control form-control-sm"
                               min="1970" max="<?= date('Y') + 1 ?>" placeholder="<?= date('Y') ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Énergie</label>
                        <select name="energie" class="form-select form-select-sm">
                            <option value="">—</option>
                            <?php foreach (\App\Models\Vehicle::ENERGIES as $e): ?>
                            <option value="<?= $e ?>"><?= ucfirst($e) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Usage</label>
                        <select name="usage" class="form-select form-select-sm">
                            <?php foreach (\App\Models\Vehicle::USAGES as $u): ?>
                            <option value="<?= $u ?>"><?= ucfirst($u) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Valeur vénale (XOF)</label>
                        <input type="number" name="valeur_venale" class="form-control form-control-sm"
                               min="0" step="1000" placeholder="5000000">
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des véhicules -->
    <?php if (empty($vehicles)): ?>
    <div class="card-body text-muted small text-center py-4">
        <i class="bi bi-car-front opacity-25 fs-2 d-block mb-2"></i>
        Aucun véhicule enregistré pour ce contrat.
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table align-middle mb-0 small">
            <thead class="table-light">
                <tr>
                    <th>Immatriculation</th>
                    <th>Marque / Modèle</th>
                    <th>Année</th>
                    <th>Énergie</th>
                    <th>Usage</th>
                    <th class="text-end">Valeur vénale</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($vehicles as $veh): ?>
            <tr>
                <td class="font-monospace fw-semibold"><?= htmlspecialchars($veh['immatriculation']) ?></td>
                <td>
                    <?= htmlspecialchars($veh['marque']) ?>
                    <?php if ($veh['modele']): ?>
                    <span class="text-muted"><?= htmlspecialchars($veh['modele']) ?></span>
                    <?php endif; ?>
                </td>
                <td class="text-muted"><?= $veh['annee'] ?: '—' ?></td>
                <td class="text-muted"><?= $veh['energie'] ? ucfirst($veh['energie']) : '—' ?></td>
                <td class="text-muted"><?= ucfirst($veh['usage']) ?></td>
                <td class="text-end text-muted">
                    <?= $veh['valeur_venale'] ? number_format((float)$veh['valeur_venale'], 0, ',', ' ') . ' XOF' : '—' ?>
                </td>
                <td class="text-end text-nowrap">
                    <a href="/admin/vehicles/<?= (int)$veh['id'] ?>/edit"
                       class="btn btn-sm btn-outline-secondary me-1">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <form method="post" action="/admin/vehicles/<?= (int)$veh['id'] ?>/delete"
                          class="d-inline" data-confirm="Supprimer ce véhicule ?">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- ── Bénéficiaires ─────────────────────────────────────────────────────────── -->
<?php $beneficiaries ??= []; ?>
<?php if ($isEdit): ?>
<div class="card shadow-sm mt-4">
    <div class="card-header fw-semibold d-flex align-items-center justify-content-between">
        <span><i class="bi bi-person-heart me-2 text-success"></i>Bénéficiaires
            <?php if (!empty($beneficiaries)): ?>
            <span class="badge bg-success-subtle text-success border border-success-subtle ms-1"><?= count($beneficiaries) ?></span>
            <?php endif; ?>
        </span>
        <button type="button" class="btn btn-sm btn-outline-success"
                data-bs-toggle="collapse" data-bs-target="#addBeneficiaryCollapse"
                aria-expanded="false">
            <i class="bi bi-plus-lg me-1"></i>Ajouter
        </button>
    </div>

    <!-- Formulaire d'ajout inline -->
    <div class="collapse" id="addBeneficiaryCollapse">
        <div class="card-body border-bottom bg-light p-3">
            <form method="post"
                  action="/admin/contracts/<?= (int)$contract['id'] ?>/beneficiaries/create"
                  novalidate>
                <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
                <div class="row g-2 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Nom <span class="text-danger">*</span></label>
                        <input type="text" name="last_name" class="form-control form-control-sm text-uppercase"
                               placeholder="NOM" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Prénom <span class="text-danger">*</span></label>
                        <input type="text" name="first_name" class="form-control form-control-sm"
                               placeholder="Prénom" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Naissance</label>
                        <input type="date" name="birth_date" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label small fw-semibold">Genre</label>
                        <select name="gender" class="form-select form-select-sm">
                            <option value="">—</option>
                            <?php foreach (\App\Models\Beneficiary::GENDERS as $code => $lbl): ?>
                            <option value="<?= $code ?>"><?= $lbl ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Lien</label>
                        <select name="relation" class="form-select form-select-sm">
                            <?php foreach (\App\Models\Beneficiary::RELATIONS as $rel): ?>
                            <option value="<?= $rel ?>"><?= ucfirst($rel) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold">Matricule</label>
                        <input type="text" name="matricule" class="form-control form-control-sm font-monospace"
                               placeholder="N° adhérent">
                    </div>
                    <div class="col-md-1 d-flex flex-column align-items-center">
                        <label class="form-label small fw-semibold text-center">Principal</label>
                        <input type="checkbox" name="is_principal" class="form-check-input mt-1">
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-success btn-sm w-100">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des bénéficiaires -->
    <?php if (empty($beneficiaries)): ?>
    <div class="card-body text-muted small text-center py-4">
        <i class="bi bi-people opacity-25 fs-2 d-block mb-2"></i>
        Aucun bénéficiaire enregistré pour ce contrat.
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table align-middle mb-0 small">
            <thead class="table-light">
                <tr>
                    <th>Nom / Prénom</th>
                    <th>Naissance</th>
                    <th>Genre</th>
                    <th>Lien</th>
                    <th>Matricule</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($beneficiaries as $ben): ?>
            <tr>
                <td>
                    <span class="fw-semibold"><?= htmlspecialchars($ben['last_name']) ?></span>
                    <?= htmlspecialchars($ben['first_name']) ?>
                    <?php if ($ben['is_principal']): ?>
                    <span class="badge bg-success-subtle text-success border border-success-subtle ms-1 small">Principal</span>
                    <?php endif; ?>
                </td>
                <td class="text-muted">
                    <?= $ben['birth_date'] ? date('d/m/Y', strtotime($ben['birth_date'])) : '—' ?>
                </td>
                <td class="text-muted"><?= $ben['gender'] ?: '—' ?></td>
                <td class="text-muted"><?= ucfirst($ben['relation']) ?></td>
                <td class="font-monospace text-muted small"><?= htmlspecialchars($ben['matricule'] ?? '—') ?></td>
                <td class="text-end text-nowrap">
                    <a href="/admin/beneficiaries/<?= (int)$ben['id'] ?>/edit"
                       class="btn btn-sm btn-outline-secondary me-1">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <form method="post" action="/admin/beneficiaries/<?= (int)$ben['id'] ?>/delete"
                          class="d-inline" data-confirm="Supprimer ce bénéficiaire ?">
                        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrf) ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php require APP_PATH . '/Views/admin/layout/footer.php'; ?>
