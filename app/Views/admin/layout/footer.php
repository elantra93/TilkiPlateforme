    </div><!-- /.tk-content -->
</div><!-- /.tk-main -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/app.js"></script>
<script src="/assets/js/verify-modal.js"></script>

<!-- Modale de vérification (documents & paiements) -->
<div class="modal fade" id="verifyModal" tabindex="-1" aria-labelledby="verifyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold" id="verifyModalLabel">Vérifier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body p-0">
                <div class="row g-0 verify-modal-body">
                    <div class="col-12 col-md-7 p-3 border-end border-bottom border-md-bottom-0 d-flex flex-column">
                        <div class="verify-preview flex-grow-1 d-flex align-items-center justify-content-center bg-light rounded verify-preview-area"></div>
                        <div class="mt-2 d-flex gap-2">
                            <a href="#" class="btn btn-sm btn-outline-secondary btn-verify-download d-none" target="_blank">
                                <i class="bi bi-download me-1"></i>Télécharger
                            </a>
                            <a href="#" class="btn btn-sm btn-outline-secondary btn-verify-open d-none" target="_blank">
                                <i class="bi bi-box-arrow-up-right me-1"></i>Ouvrir dans un onglet
                            </a>
                        </div>
                    </div>
                    <div class="col-12 col-md-5 p-3">
                        <h6 class="text-uppercase fw-semibold text-muted mb-3 fs-2xs ls-wide">Détails</h6>
                        <dl class="row g-0 small verify-details mb-0"></dl>
                    </div>
                </div>
            </div>
            <div class="modal-footer verify-actions flex-wrap gap-2">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
            </div>
        </div>
    </div>
</div>
</body>
</html>
