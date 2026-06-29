document.addEventListener('DOMContentLoaded', function () {
    var ctx         = document.getElementById('claimTallyCtx');
    if (!ctx) return;
    var contracts   = JSON.parse(ctx.dataset.contracts || '{}');

    var clientSel   = document.getElementById('clientSel');
    var contractSel = document.getElementById('contractSel');
    if (!clientSel || !contractSel) return;

    function show(id) { var el = document.getElementById(id); if (el) el.style.display = ''; }
    function hide(id) { var el = document.getElementById(id); if (el) el.style.display = 'none'; }

    clientSel.addEventListener('change', function () {
        hide('contractRow');
        hide('submitRow');
        contractSel.innerHTML = '<option value="">— Sélectionner une police —</option>';
        if (!this.value) return;
        (contracts[this.value] || []).forEach(function (c) {
            var opt = document.createElement('option');
            opt.value       = c.id;
            opt.textContent = c.label;
            contractSel.appendChild(opt);
        });
        show('contractRow');
    });

    contractSel.addEventListener('change', function () {
        if (this.value) show('submitRow');
        else            hide('submitRow');
    });
});
