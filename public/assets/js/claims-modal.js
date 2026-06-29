document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('declarerModal');
    if (!modal) return;
    var sel = document.getElementById('modalContractSel');
    var btn = document.getElementById('declarerSubmitBtn');

    function syncBtn() { btn.disabled = !sel.value; }
    sel.addEventListener('change', syncBtn);
    modal.addEventListener('shown.bs.modal', syncBtn);
});
