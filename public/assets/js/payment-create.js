document.addEventListener('DOMContentLoaded', function () {
    var ctx         = document.getElementById('paymentCreateCtx');
    if (!ctx) return;
    var contracts   = JSON.parse(ctx.dataset.contracts   || '{}');
    var preContract = parseInt(ctx.dataset.preContract   || '0', 10);

    var clientSel   = document.getElementById('clientSel');
    var contractSel = document.getElementById('contractSel');
    if (!clientSel || !contractSel) return;

    function populateContracts(clientId) {
        contractSel.innerHTML = '';
        contractSel.add(new Option('— Sélectionner un contrat —', ''));
        var list = contracts[clientId] || [];
        list.forEach(function (c) {
            var opt = new Option(c.label, c.id);
            if (c.id === preContract) opt.selected = true;
            contractSel.add(opt);
        });
        if (list.length === 1) contractSel.selectedIndex = 1;
    }

    clientSel.addEventListener('change', function () {
        populateContracts(this.value);
    });

    if (clientSel.value) {
        populateContracts(clientSel.value);
    }
});
