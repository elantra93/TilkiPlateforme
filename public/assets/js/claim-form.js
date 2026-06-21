document.addEventListener('DOMContentLoaded', function () {
    var ctx = document.getElementById('claimFormCtx');
    if (!ctx) return;

    var docTypes  = JSON.parse(ctx.dataset.docTypes  || '{}');
    var contracts = JSON.parse(ctx.dataset.contracts || '{}');

    var docTypeLabels = {
        'declaration_sinistre':    'Déclaration de sinistre',
        'rapport_circonstances':   'Rapport de circonstances',
        'constat_amiable':         'Constat amiable',
        'plainte':                 'Plainte',
        'rapport_expertise':       "Rapport d'expertise",
        'devis_reparation':        'Devis de réparation',
        'constat_police':          'Constat de police',
        'contre_expertise':        'Contre-expertise',
        'estimation_perte':        'Estimation de perte',
        'courrier_assureur':       'Courrier assureur',
        'courrier_expert':         'Courrier expert',
        'courrier_client':         'Courrier client',
        'mise_en_demeure':         'Mise en demeure',
        'virement':                'Virement',
        'cheque':                  'Chèque',
        'quittance_reglement':     'Quittance de règlement',
        'decompte_indemnite':      "Décompte d'indemnité",
    };

    // ── Upload cascade: catégorie → type de document ─────────────────────────
    var catSel  = document.getElementById('claimCatSel');
    var typeSel = document.getElementById('claimDocTypeSel');
    if (catSel && typeSel) {
        catSel.addEventListener('change', function () {
            var types = docTypes[this.value] || [];
            typeSel.innerHTML = '<option value="">— Sélectionner —</option>';
            types.forEach(function (t) {
                typeSel.add(new Option(docTypeLabels[t] || t.replace(/_/g, ' '), t));
            });
        });
    }

    // ── Client → contrat cascade (mode création) ─────────────────────────────
    var clientSel   = document.getElementById('clientSel');
    var contractSel = document.getElementById('contractSel');
    if (clientSel && contractSel) {
        clientSel.addEventListener('change', function () {
            var items = contracts[this.value] || [];
            contractSel.innerHTML = '<option value="">— Aucun —</option>';
            items.forEach(function (c) {
                var opt = document.createElement('option');
                opt.value       = c.id;
                opt.textContent = c.label;
                contractSel.appendChild(opt);
            });
        });
    }

    // ── Étapes : sync checkboxes + dates vers hidden inputs + auto-submit ────
    document.querySelectorAll('.step-cb').forEach(function (cb) {
        var row       = cb.closest('tr');
        if (!row) return;
        var dateInput = row.querySelector('.step-date');
        var form      = row.querySelector('.step-form');
        if (!form) return;
        var hComp     = form.querySelector('.h-completed');
        var hDate     = form.querySelector('.h-date');

        cb.addEventListener('change', function () {
            if (hComp) hComp.value = this.checked ? '1' : '';
            if (dateInput) {
                dateInput.disabled = !this.checked;
                if (!this.checked) {
                    dateInput.value = '';
                    if (hDate) hDate.value = '';
                }
            }
            form.submit();
        });

        if (dateInput && hDate) {
            dateInput.addEventListener('change', function () {
                hDate.value = this.value;
            });
        }
    });
});
