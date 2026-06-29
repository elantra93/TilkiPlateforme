document.addEventListener('DOMContentLoaded', function () {
    // ── Upload form: category → doc type dropdown ─────────────────────────────
    var section  = document.getElementById('contractDocSection');
    var catSel   = document.getElementById('contractCatSel');
    var typeSel  = document.getElementById('contractDocTypeSel');

    if (section && catSel && typeSel) {
        var docTypes = JSON.parse(section.dataset.docTypes || '{}');
        var docLabels = JSON.parse(section.dataset.docLabels || '{}');

        catSel.addEventListener('change', function () {
            var types = docTypes[this.value] || [];
            typeSel.innerHTML = '<option value="">— Sélectionner —</option>';
            types.forEach(function (t) {
                var label = docLabels[t] || t.replace(/_/g, ' ');
                typeSel.add(new Option(label, t));
            });
        });
    }

    // ── Applicable / Non applicable toggle ────────────────────────────────────
    document.querySelectorAll('.doc-na-check').forEach(function (chk) {
        chk.addEventListener('change', function () {
            var row = this.closest('.doc-type-row');
            if (!row) return;
            var uploadArea = row.querySelector('.doc-upload-area');
            var naBadge   = row.querySelector('.doc-na-badge');
            if (this.checked) {
                if (uploadArea) uploadArea.style.display = 'none';
                if (naBadge)   naBadge.style.display = '';
            } else {
                if (uploadArea) uploadArea.style.display = '';
                if (naBadge)   naBadge.style.display = 'none';
            }
        });
    });
});
