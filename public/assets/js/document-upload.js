document.addEventListener('DOMContentLoaded', function () {
    var ctx = document.getElementById('docUploadCtx');
    if (!ctx) return;

    var contracts          = JSON.parse(ctx.dataset.contracts       || '{}');
    var claims             = JSON.parse(ctx.dataset.claims          || '{}');
    var docTypes           = JSON.parse(ctx.dataset.docTypes        || '{}');
    var branchDocTypes     = JSON.parse(ctx.dataset.branchDocTypes  || '{}');
    var genericSouscription= JSON.parse(ctx.dataset.generic         || '[]');
    var catContrat         = JSON.parse(ctx.dataset.catContrat      || '[]');
    var catSinistre        = JSON.parse(ctx.dataset.catSinistre     || '[]');

    var catLabels = {
        cotation:                  'Cotation',
        souscription:              'Documents du contrat',
        declaration:               'Déclaration',
        expertise_devis:           "Rapports d'expertises et devis",
        correspondances:           'Correspondances',
        reglements_remboursements: 'Règlements et remboursements',
    };

    var clientSel   = document.getElementById('clientSel');
    var contractSel = document.getElementById('contractSel');
    var claimSel    = document.getElementById('claimSel');
    var categorySel = document.getElementById('categorySel');
    var docTypeSel  = document.getElementById('docTypeSel');
    if (!clientSel) return;

    function show(id) { var el = document.getElementById(id); if (el) el.style.display = ''; }
    function hide(id) { var el = document.getElementById(id); if (el) el.style.display = 'none'; }

    function resetFrom(step) {
        if (step <= 2) {
            hide('scopeRow');
            document.querySelectorAll('input[name=scope]').forEach(function (r) { r.checked = false; });
        }
        if (step <= 3) {
            hide('contractRow'); hide('claimRow');
            if (contractSel) contractSel.innerHTML = '<option value="">—</option>';
            if (claimSel)    claimSel.innerHTML    = '<option value="">—</option>';
        }
        if (step <= 4) {
            hide('categoryRow'); hide('docTypeRow');
            if (categorySel) categorySel.innerHTML = '<option value="">— Choisir —</option>';
            if (docTypeSel)  docTypeSel.innerHTML  = '<option value="">—</option>';
        }
        if (step <= 5) { hide('fileRow'); hide('submitRow'); }
    }

    function populateCategories(cats) {
        if (!categorySel) return;
        categorySel.innerHTML = '<option value="">— Choisir —</option>';
        cats.forEach(function (c) {
            categorySel.innerHTML += '<option value="' + c + '">' + (catLabels[c] || c) + '</option>';
        });
    }

    function selectedContractBranche() {
        if (!contractSel || !contractSel.value) return null;
        var cid = contractSel.value;
        for (var clientId in contracts) {
            for (var i = 0; i < contracts[clientId].length; i++) {
                if (String(contracts[clientId][i].id) === String(cid)) {
                    return contracts[clientId][i].branche || null;
                }
            }
        }
        return null;
    }

    function populateDocTypes(category) {
        if (!docTypeSel) return;
        docTypeSel.innerHTML = '<option value="">— Sélectionner —</option>';
        if (category === 'souscription') {
            var branche  = selectedContractBranche();
            var specific = branche ? (branchDocTypes[branche] || null) : null;
            if (specific) {
                specific.forEach(function (t) {
                    var suffix = t.required ? '' : ' (optionnel)';
                    docTypeSel.innerHTML += '<option value="' + t.key + '">' + t.label + suffix + '</option>';
                });
            } else {
                genericSouscription.forEach(function (t) {
                    docTypeSel.innerHTML += '<option value="' + t + '">' + t.replace(/_/g, ' ') + '</option>';
                });
            }
        } else {
            (docTypes[category] || []).forEach(function (t) {
                docTypeSel.innerHTML += '<option value="' + t + '">' + t.replace(/_/g, ' ') + '</option>';
            });
        }
        show('docTypeRow');
        show('fileRow');
        show('submitRow');
    }

    clientSel.addEventListener('change', function () {
        resetFrom(2);
        if (!this.value) return;
        if (contractSel) {
            contractSel.innerHTML = '<option value="">— Sélectionner —</option>';
            (contracts[this.value] || []).forEach(function (c) {
                contractSel.innerHTML += '<option value="' + c.id + '">' + c.label + '</option>';
            });
        }
        if (claimSel) {
            claimSel.innerHTML = '<option value="">— Sélectionner —</option>';
            (claims[this.value] || []).forEach(function (c) {
                claimSel.innerHTML += '<option value="' + c.id + '">' + c.label + '</option>';
            });
        }
        show('scopeRow');
    });

    document.querySelectorAll('input[name=scope]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            resetFrom(3);
            var clientId = clientSel.value;
            if (this.value === 'contrat') {
                if (contractSel) {
                    contractSel.innerHTML = '<option value="">— Sélectionner un contrat —</option>';
                    (contracts[clientId] || []).forEach(function (c) {
                        contractSel.innerHTML += '<option value="' + c.id + '">' + c.label + '</option>';
                    });
                }
                show('contractRow'); hide('claimRow');
            } else {
                if (claimSel) {
                    claimSel.innerHTML = '<option value="">— Sélectionner un sinistre —</option>';
                    (claims[clientId] || []).forEach(function (c) {
                        claimSel.innerHTML += '<option value="' + c.id + '">' + c.label + '</option>';
                    });
                }
                show('claimRow'); hide('contractRow');
            }
            populateCategories(this.value === 'contrat' ? catContrat : catSinistre);
            show('categoryRow');
        });
    });

    [contractSel, claimSel].filter(Boolean).forEach(function (sel) {
        sel.addEventListener('change', function () {
            resetFrom(4);
            if (!this.value) return;
            var scope = document.querySelector('input[name=scope]:checked');
            populateCategories(scope && scope.value === 'sinistre' ? catSinistre : catContrat);
            show('categoryRow');
        });
    });

    if (categorySel) {
        categorySel.addEventListener('change', function () {
            resetFrom(5);
            if (!this.value) return;
            populateDocTypes(this.value);
        });
    }
});
