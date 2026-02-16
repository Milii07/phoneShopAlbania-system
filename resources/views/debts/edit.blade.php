@extends('layouts.app')

@section('title', 'Modifiko Borxhin')

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

    .alert-warning-custom {
        background: #fff3cd;
        border-left: 4px solid #ffc107;
        padding: 15px;
        border-radius: 5px;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">✏️ Modifiko Borxhin</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('debts.index') }}">Borxhet</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('debts.show', $debt->id) }}">{{ $debt->debt_number }}</a></li>
                    <li class="breadcrumb-item active">Modifiko</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('debts.update', $debt->id) }}" id="debtForm">
    @csrf
    @method('PUT')
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
                    <!-- Alert if has payments -->
                    @if($debt->payments->count() > 0)
                    <div class="alert-warning-custom mb-4">
                        <div class="d-flex align-items-start">
                            <i class="ri-alert-line fs-4 me-2 text-warning"></i>
                            <div>
                                <strong>Vëmendje!</strong>
                                <p class="mb-0">
                                    Ky borxh ka {{ $debt->payments->count() }} pagesa të regjistruara.
                                    Ndryshimi i shumës totale do të rillogarisë automatikisht shumën e mbetur.
                                </p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="row">
                        <!-- Debt Number (Read-only) -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Numri i Borxhit</label>
                            <input type="text" class="form-control" value="{{ $debt->debt_number }}" readonly>
                        </div>

                        <!-- Status (Read-only) -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Statusi</label>
                            <input type="text" class="form-control"
                                value="@if($debt->status == 'paid')E Paguar@elseif($debt->status == 'partial')Pjesërisht@else E Papaguar@endif"
                                readonly>
                        </div>

                        <!-- Supplier -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label required-field">Furnizuesi</label>
                            <select class="form-select select2-supplier" name="supplier_id" id="supplier_id" required>
                                <option value="">Zgjidh Furnizuesin...</option>
                                @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ $debt->supplier_id == $supplier->id ? 'selected' : '' }}>
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
                                <option value="{{ $warehouse->id }}" {{ $debt->warehouse_id == $warehouse->id ? 'selected' : '' }}>
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
                                    value="{{ old('total_amount', $debt->total_amount) }}"
                                    step="0.01"
                                    min="{{ $debt->paid_amount }}"
                                    placeholder="0.00"
                                    required>
                                <span class="input-group-text" id="currency-symbol">{{ $debt->currency->symbol }}</span>
                            </div>
                            @if($debt->paid_amount > 0)
                            <small class="text-muted">
                                Minimumi: {{ number_format($debt->paid_amount, 2) }} {{ $debt->currency->symbol }} (shuma e paguar)
                            </small>
                            @endif
                            @error('total_amount')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Currency -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label required-field">Monedha</label>
                            <select class="form-select" name="currency_id" id="currency_id" required>
                                @foreach($currencies as $currency)
                                <option value="{{ $currency->id }}"
                                    data-symbol="{{ $currency->symbol }}"
                                    {{ $debt->currency_id == $currency->id ? 'selected' : '' }}>
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
                                value="{{ old('debt_date', $debt->debt_date->format('Y-m-d')) }}"
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
                                value="{{ old('due_date', $debt->due_date ? $debt->due_date->format('Y-m-d') : '') }}">
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
                                placeholder="Shkruani përshkrimin e borxhit...">{{ old('description', $debt->description) }}</textarea>
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
                                placeholder="Shënime shtesë...">{{ old('notes', $debt->notes) }}</textarea>
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
                            <span class="text-muted">Shuma Totale:</span>
                            <strong id="summary-total">{{ number_format($debt->total_amount, 2) }} {{ $debt->currency->symbol }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-success">E Paguar:</span>
                            <strong class="text-success">{{ number_format($debt->paid_amount, 2) }} {{ $debt->currency->symbol }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-danger">Mbetet:</span>
                            <strong class="text-danger" id="summary-remaining">{{ number_format($debt->remaining_amount, 2) }} {{ $debt->currency->symbol }}</strong>
                        </div>
                    </div>

                    @if($debt->payments->count() > 0)
                    <div class="alert alert-info">
                        <i class="ri-information-line me-2"></i>
                        <small>
                            Ky borxh ka <strong>{{ $debt->payments->count() }}</strong> pagesa të regjistruara.
                        </small>
                    </div>
                    @endif

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="ri-save-line me-1"></i> Përditëso Borxhin
                        </button>
                        <a href="{{ route('debts.show', $debt->id) }}" class="btn btn-secondary">
                            <i class="ri-close-line me-1"></i> Anulo
                        </a>
                        <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                            <i class="ri-delete-bin-line me-1"></i> Fshi Borxhin
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Delete Form -->
<form id="deleteForm" action="{{ route('debts.destroy', $debt->id) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
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

        const paidAmount = {
            {
                $debt - > paid_amount
            }
        };

        // Update currency symbol
        $('#currency_id').on('change', function() {
            const symbol = $(this).find(':selected').data('symbol') || 'L';
            $('#currency-symbol').text(symbol);
            updateSummary();
        });

        // Update summary on total amount change
        $('#total_amount').on('input', function() {
            updateSummary();
        });

        function updateSummary() {
            const totalAmount = parseFloat($('#total_amount').val()) || 0;
            const symbol = $('#currency_id option:selected').data('symbol') || 'L';
            const remaining = totalAmount - paidAmount;

            $('#summary-total').text(totalAmount.toFixed(2) + ' ' + symbol);
            $('#summary-remaining').text(remaining.toFixed(2) + ' ' + symbol);

            // Validation
            if (totalAmount < paidAmount) {
                $('#total_amount').addClass('is-invalid');
                if (!$('#total_amount').next('.invalid-feedback').length) {
                    $('#total_amount').after(
                        `<div class="invalid-feedback">Shuma totale nuk mund të jetë më e vogël se shuma e paguar (${paidAmount.toFixed(2)} ${symbol})</div>`
                    );
                }
            } else {
                $('#total_amount').removeClass('is-invalid');
                $('#total_amount').next('.invalid-feedback').remove();
            }
        }

        // Form validation
        $('#debtForm').on('submit', function(e) {
            const totalAmount = parseFloat($('#total_amount').val());

            if (totalAmount < paidAmount) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Gabim!',
                    text: `Shuma totale nuk mund të jetë më e vogël se shuma e paguar (${paidAmount.toFixed(2)})`
                });
                return false;
            }

            if (totalAmount <= 0) {
                e.preventDefault();
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
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Gabim!',
                    text: 'Afati i pagesës nuk mund të jetë para datës së borxhit'
                });
                return false;
            }
        });
    });

    function confirmDelete() {
        Swal.fire({
            title: 'Jeni i sigurt?',
            text: "Ky veprim nuk mund të kthehet mbrapsht!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Po, Fshije!',
            cancelButtonText: 'Anulo'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('deleteForm').submit();
            }
        });
    }
</script>
@endpush