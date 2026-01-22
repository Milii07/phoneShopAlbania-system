<!-- Edit Currency Modal -->
<div class="modal fade" id="editCurrencyModal" tabindex="-1" aria-labelledby="editCurrencyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editCurrencyModalLabel">
                    <i class="ri-pencil-line align-middle me-1"></i> Modifiko Currency
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editCurrencyForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body" id="editCurrencyModalBody">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Duke ngarkuar...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Anulo</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line align-middle me-1"></i> Ruaj Ndryshimet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>