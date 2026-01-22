<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title text-white" id="createModalLabel">
                    <i class="ri-add-line align-middle me-1"></i> Shto Seller të Ri
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('sellers.store') }}" id="createSellerForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Emri <span class="text-danger">*</span></label>
                        <input type="text"
                            class="form-control @error('name') is-invalid @enderror"
                            id="name"
                            name="name"
                            value="{{ old('name') }}"
                            placeholder="Shkruani emrin e shitesit"
                            required>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="age" class="form-label">Mosha <span class="text-danger">*</span></label>
                        <input type="text"
                            class="form-control @error('age') is-invalid @enderror"
                            id="age"
                            name="age"
                            value="{{ old('age') }}"
                            placeholder="18"
                            required>
                        @error('age')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Anulo</button>
                    <button type="submit" class="btn btn-success">
                        <i class="ri-save-line align-middle me-1"></i> Ruaj Sellerin
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>