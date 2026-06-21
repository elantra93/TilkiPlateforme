'use strict';

document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('verifyModal');
    if (!modal) return;

    var bsModal = new bootstrap.Modal(modal);

    modal.addEventListener('hidden.bs.modal', function () {
        modal.querySelector('.verify-preview').innerHTML = '';
        modal.querySelector('.verify-actions').innerHTML =
            '<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>';
        modal.querySelector('.verify-details').innerHTML = '';
        modal.querySelector('.modal-title').textContent = 'Vérifier';
        modal.querySelector('.btn-verify-download').classList.add('d-none');
        modal.querySelector('.btn-verify-open').classList.add('d-none');
    });

    document.querySelectorAll('.btn-verify').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            if (this.dataset.verifyType === 'document') {
                fillDocModal(this);
            } else if (this.dataset.verifyType === 'payment') {
                fillPaymentModal(this);
            }
            bsModal.show();
        });
    });

    /* ── Document ─────────────────────────────────────────────────────── */
    function fillDocModal(btn) {
        var id   = btn.dataset.id;
        var csrf = btn.dataset.csrf;
        var mime = btn.dataset.mime || '';

        modal.querySelector('.modal-title').textContent = 'Vérifier le document';

        var previewArea = modal.querySelector('.verify-preview');
        previewArea.innerHTML = '';
        if (mime.startsWith('image/')) {
            var img = document.createElement('img');
            img.src       = '/admin/documents/' + id + '/preview';
            img.className = 'img-fluid rounded';
            img.alt       = btn.dataset.name || '';
            previewArea.appendChild(img);
        } else {
            var iframe = document.createElement('iframe');
            iframe.src       = '/admin/documents/' + id + '/preview';
            iframe.className = 'w-100 border-0 rounded';
            iframe.style.cssText = 'height:360px';
            iframe.title    = btn.dataset.name || 'Aperçu';
            previewArea.appendChild(iframe);
        }

        showFileButtons(modal, id, id);

        setDetails(modal, [
            ['Fichier',    btn.dataset.name     || '—'],
            ['Type',       btn.dataset.docType  || '—'],
            ['Catégorie',  btn.dataset.category || '—'],
            ['Taille',     fmtSize(btn.dataset.size)],
            ['Déposé le',  btn.dataset.date     || '—'],
            ['Source',     fmtSource(btn.dataset.source)],
            ['Déposé par', btn.dataset.uploader || '—'],
        ]);

        modal.querySelector('.verify-actions').innerHTML =
            '<form method="post" action="/admin/documents/' + esc(id) + '/validate" class="d-inline">' +
                '<input type="hidden" name="_csrf" value="' + esc(csrf) + '">' +
                '<button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Valider</button>' +
            '</form>' +
            '<form method="post" action="/admin/documents/' + esc(id) + '/reject" class="d-inline ms-2">' +
                '<input type="hidden" name="_csrf" value="' + esc(csrf) + '">' +
                '<button type="submit" class="btn btn-danger"><i class="bi bi-x-lg me-1"></i>Refuser</button>' +
            '</form>' +
            '<button type="button" class="btn btn-outline-secondary ms-auto" data-bs-dismiss="modal">Annuler</button>';
    }

    /* ── Paiement ─────────────────────────────────────────────────────── */
    function fillPaymentModal(btn) {
        var id         = btn.dataset.id;
        var contractId = btn.dataset.contractId;
        var csrf       = btn.dataset.csrf;
        var docId      = btn.dataset.docId || '';
        var amount     = parseFloat(btn.dataset.amount) || 0;

        modal.querySelector('.modal-title').textContent = 'Vérifier le paiement';

        var previewArea = modal.querySelector('.verify-preview');
        previewArea.innerHTML = '';
        if (docId) {
            var iframe = document.createElement('iframe');
            iframe.src       = '/admin/documents/' + docId + '/preview';
            iframe.className = 'w-100 border-0 rounded';
            iframe.style.cssText = 'height:360px';
            iframe.title    = 'Justificatif';
            previewArea.appendChild(iframe);
            showFileButtons(modal, docId, docId);
        } else {
            previewArea.innerHTML =
                '<p class="text-muted fst-italic text-center py-5 w-100">' +
                '<i class="bi bi-image fs-2 d-block mb-2 opacity-25"></i>Aucun justificatif joint</p>';
            modal.querySelector('.btn-verify-download').classList.add('d-none');
            modal.querySelector('.btn-verify-open').classList.add('d-none');
        }

        var mLabels = {cheque:'Chèque', virement:'Virement', caisse:'Caisse', mobile_money:'Mobile Money'};
        var sLabels = {en_attente:'En attente', valide:'Validé', rejeté:'Rejeté'};
        var cLabels = {admin:'Admin', client:'Client'};

        setDetails(modal, [
            ['Paiement #',   id],
            ['Mode',         mLabels[btn.dataset.method] || btn.dataset.method || '—'],
            ['Statut',       sLabels[btn.dataset.status] || btn.dataset.status || '—'],
            ['Montant',      amount > 0 ? fmtAmount(amount) + ' XOF' : '— (à saisir)'],
            ['Date',         btn.dataset.date      || '—'],
            ['Soumis par',   cLabels[btn.dataset.createdBy] || btn.dataset.createdBy || '—'],
            ['Référence',    btn.dataset.reference || '—'],
        ]);

        var amountVal = amount > 0 ? amount.toFixed(2) : '';
        modal.querySelector('.verify-actions').innerHTML =
            '<form method="post"' +
                ' action="/admin/contracts/' + esc(contractId) + '/payment/' + esc(id) + '/validate"' +
                ' class="d-flex gap-2 align-items-center flex-wrap">' +
                '<input type="hidden" name="_csrf" value="' + esc(csrf) + '">' +
                '<div class="input-group input-group-sm" style="width:200px">' +
                    '<input type="number" name="amount" class="form-control" step="0.01" min="0.01"' +
                        ' value="' + esc(amountVal) + '" placeholder="Montant *" required>' +
                    '<span class="input-group-text">XOF</span>' +
                '</div>' +
                '<button type="submit" class="btn btn-success">' +
                    '<i class="bi bi-check-lg me-1"></i>Valider le paiement' +
                '</button>' +
            '</form>' +
            '<button type="button" class="btn btn-outline-secondary ms-auto" data-bs-dismiss="modal">Annuler</button>';
    }

    /* ── Helpers ──────────────────────────────────────────────────────── */
    function showFileButtons(modal, downloadId, previewId) {
        var dl   = modal.querySelector('.btn-verify-download');
        var open = modal.querySelector('.btn-verify-open');
        dl.href  = '/admin/documents/' + downloadId + '/download';
        open.href = '/admin/documents/' + previewId + '/preview';
        dl.classList.remove('d-none');
        open.classList.remove('d-none');
    }

    function setDetails(modal, rows) {
        var dl = modal.querySelector('.verify-details');
        dl.innerHTML = '';
        rows.forEach(function (item) {
            var dt = document.createElement('dt');
            dt.className = 'col-5 text-muted fw-normal mb-2';
            dt.textContent = item[0];
            var dd = document.createElement('dd');
            dd.className = 'col-7 mb-2 text-break';
            dd.textContent = item[1];
            dl.appendChild(dt);
            dl.appendChild(dd);
        });
    }

    function fmtSize(b) {
        b = parseInt(b, 10) || 0;
        if (!b) return '—';
        if (b < 1024) return b + ' o';
        if (b < 1048576) return Math.round(b / 1024) + ' Ko';
        return (b / 1048576).toFixed(1) + ' Mo';
    }

    function fmtAmount(n) {
        return new Intl.NumberFormat('fr-FR').format(Math.round(n));
    }

    function fmtSource(s) {
        return s === 'admin' ? 'Admin' : s === 'client' ? 'Client' : s === 'tally' ? 'Formulaire Tally' : (s || '—');
    }

    function esc(s) {
        return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
});
