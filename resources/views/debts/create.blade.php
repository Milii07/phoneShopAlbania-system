@extends('layouts.app')

@section('title', 'Shto Pagesë të Re')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .form-card {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .section-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px 20px;
        border-radius: 10px 10px 0 0;
        margin-bottom: 20px;
    }

    .required-field::after {
        content: "*";
        color: red;
        margin-left: 3px;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">💰 Shto Borxh të Ri</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('debts.index') }}">Borxhet</a></li>
                    <li class="breadcrumb-item active">Shto Borxh</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('debts.store') }}" id="debtForm">
    @csrf
    <div class="row">
        <div class="col-lg-8">
            <div class="card form-card">
                <div class="section-header">
                    <h5 class="mb-0">
                        <i class="ri-file-list-line me-2"></i>
                        Informacioni i Borxhit
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Supplier -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label required-field">Furnizuesi</label>
                            <select class="form-select select2-supplier" name="supplier_id" id="supplier_id" required>
                                <option value="">Zgjidh Furnizuesin...</option>
                                @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('supplier_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Warehouse -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label required-field">Magazina</label>
                            <select class="form-select select2-warehouse" name="warehouse_id" id="warehouse_id" required>
                                <option value="">Zgjidh Magazinën...</option>
                                @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                    {{ $warehouse->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('warehouse_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Total Amount -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label required-field">Shuma Totale</label>
                            <div class="input-group">
                                <input type="number"
                                    class="form-control @error('total_amount') is-invalid @enderror"
                                    name="total_amount"
                                    id="total_amount"
                                    value="{{ old('total_amount') }}"
                                    step="0.01"
                                    min="0.01"
                                    placeholder="0.00"
                                    required>
                                <span class="input-group-text" id="currency-symbol">L</span>
                            </div>
                            @error('total_amount')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Currency -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label required-field">Monedha</label>
                            <select class="form-select" name="currency_id" id="currency_id" required>
                                <option value="">Zgjidh Monedhën...</option>
                                @foreach($currencies as $currency)
                                <option value="{{ $currency->id }}"
                                    data-symbol="{{ $currency->symbol }}"
                                    {{ old('currency_id', $currencies->first()->id ?? '') == $currency->id ? 'selected' : '' }}>
                                    {{ $currency->code }} ({{ $currency->symbol }})
                                </option>
                                @endforeach
                            </select>
                            @error('currency_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Debt Date -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label required-field">Data e Borxhit</label>
                            <input type="date"
                                class="form-control @error('debt_date') is-invalid @enderror"
                                name="debt_date"
                                id="debt_date"
                                value="{{ old('debt_date', date('Y-m-d')) }}"
                                required>
                            @error('debt_date')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Due Date -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Afati i Pagesës
                                <small class="text-muted">(Opsionale)</small>
                            </label>
                            <input type="date"
                                class="form-control @error('due_date') is-invalid @enderror"
                                name="due_date"
                                id="due_date"
                                value="{{ old('due_date') }}">
                            @error('due_date')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Përshkrimi</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                name="description"
                                rows="3"
                                placeholder="Shkruani përshkrimin e borxhit...">{{ old('description') }}</textarea>
                            @error('description')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Notes -->
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Shënime</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror"
                                name="notes"
                                rows="2"
                                placeholder="Shënime shtesë...">{{ old('notes') }}</textarea>
                            @error('notes')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Sidebar -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <i class="ri-file-list-2-line me-2"></i>
                        Përmbledhje
                    </h5>

                    <div class="border rounded p-3 mb-3" style="background-color: #f8f9fa;">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Furnizuesi:</span>
                            <strong id="summary-supplier">-</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Magazina:</span>
                            <strong id="summary-warehouse">-</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Data:</span>
                            <strong id="summary-date">-</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Afati:</span>
                            <strong id="summary-due">-</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span class="text-danger fw-bold">Shuma Totale:</span>
                            <h5 class="mb-0 text-danger" id="summary-amount">0.00 L</h5>
                        </div>
                    </div>

                    <div class="alert alert-warning">
                        <i class="ri-information-line me-2"></i>
                        <small>
                            Ky borxh do të shtohet në sistemin tuaj. Mund të shtoni pagesa më vonë.
                        </small>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="ri-save-line me-1"></i> Ruaj Borxhin
                        </button>
                        <a href="{{ route('debts.index') }}" class="btn btn-secondary">
                            <i class="ri-close-line me-1"></i> Anulo
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quick Tips -->
            <div class="card mt-3">
                <div class="card-body">
                    <h6 class="card-title">
                        <i class="ri-lightbulb-line me-2 text-warning"></i>
                        Këshilla
                    </h6>
                    <ul class="small mb-0 ps-3">
                        <li class="mb-2">Vendosni afatin e pagesës për të marrë alarme automatike</li>
                        <li class="mb-2">Mund të shtoni pagesa pjesore më vonë</li>
                        <li class="mb-2">Sistemi do të llogarisë automatikisht shumën e mbetur</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2
        $('.select2-supplier, .select2-warehouse').select2({
            placeholder: 'Zgjidh...',
            allowClear: true
        });

        // Update currency symbol
        $('#currency_id').on('change', function() {
            const symbol = $(this).find(':selected').data('symbol') || 'L';
            $('#currency-symbol').text(symbol);
            updateSummary();
        });

        // Update summary on input change
        $('#supplier_id, #warehouse_id, #total_amount, #debt_date, #due_date').on('change', updateSummary);

        function updateSummary() {
            const supplier = $('#supplier_id option:selected').text();
            const warehouse = $('#warehouse_id option:selected').text();
            const amount = $('#total_amount').val() || '0.00';
            const symbol = $('#currency_id option:selected').data('symbol') || 'L';
            const debtDate = $('#debt_date').val();
            const dueDate = $('#due_date').val();

            $('#summary-supplier').text(supplier !== 'Zgjidh Furnizuesin...' ? supplier : '-');
            $('#summary-warehouse').text(warehouse !== 'Zgjidh Magazinën...' ? warehouse : '-');
            $('#summary-amount').text(parseFloat(amount).toFixed(2) + ' ' + symbol);
            $('#summary-date').text(debtDate ? formatDate(debtDate) : '-');
            $('#summary-due').text(dueDate ? formatDate(dueDate) : '-');
        }

        function formatDate(dateStr) {
            if (!dateStr) return '-';
            const parts = dateStr.split('-');
            return parts[2] + '/' + parts[1] + '/' + parts[0];
        }

        // Form validation
        $('#debtForm').on('submit', function(e) {
            e.preventDefault();

            const totalAmount = parseFloat($('#total_amount').val());
            if (totalAmount <= 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gabim!',
                    text: 'Shuma totale duhet të jetë më e madhe se 0'
                });
                return false;
            }

            const debtDate = new Date($('#debt_date').val());
            const dueDate = $('#due_date').val() ? new Date($('#due_date').val()) : null;

            if (dueDate && dueDate < debtDate) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gabim!',
                    text: 'Afati i pagesës nuk mund të jetë para datës së borxhit'
                });
                return false;
            }

            this.submit();
        });

        // Initialize summary
        updateSummary();
    });
</script>
@endpush