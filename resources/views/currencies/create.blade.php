<!-- Create Currency Modal -->
<div class="modal fade" id="createCurrencyModal" tabindex="-1" aria-labelledby="createCurrencyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="createCurrencyModalLabel">
                    <i class="ri-add-line align-middle me-1"></i> Shto Currency të Re
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('currencies.store') }}" id="createCurrencyForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="code" class="form-label">Kodi (3 shkronja) <span class="text-danger">*</span></label>
                        <input type="text"
                            class="form-control @error('code') is-invalid @enderror"
                            id="code"
                            name="code"
                            value="{{ old('code') }}"
                            maxlength="3"
                            placeholder="p.sh. USD, EUR"
                            required>
                        @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="symbol" class="form-label">Simboli <span class="text-danger">*</span></label>
                        <input type="text"
                            class="form-control @error('symbol') is-invalid @enderror"
                            id="symbol"
                            name="symbol"
                            value="{{ old('symbol') }}"
                            maxlength="5"
                            placeholder="p.sh. $, €"
                            required>
                        @error('symbol')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="exchange_rate" class="form-label">Exchange Rate <span class="text-danger">*</span></label>
                        <input type="number"
                            step="0.0001"
                            class="form-control @error('exchange_rate') is-invalid @enderror"
                            id="exchange_rate"
                            name="exchange_rate"
                            value="{{ old('exchange_rate', 1) }}"
                            min="0"
                            placeholder="1.0000"
                            required>
                        @error('exchange_rate')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Sa është 1 njësi e kësaj valute në LEK</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Anulo</button>
                    <button type="submit" class="btn btn-success">
                        <i class="ri-save-line align-middle me-1"></i> Ruaj Currency
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>