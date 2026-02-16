@extends('layouts.app')

@section('title', 'Modifiko Porosinë')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .form-card {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .section-header {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
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
            <h4 class="mb-sm-0">✏️ Modifiko Porosinë</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('online-orders.index') }}">Porositë Online</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('online-orders.show', $onlineOrder->id) }}">{{ $onlineOrder->order_number }}</a></li>
                    <li class="breadcrumb-item active">Modifiko</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('online-orders.update', $onlineOrder->id) }}" id="orderForm">
    @csrf
    @method('PUT')
    <div class="row">
        <div class="col-lg-8">
            <div class="card form-card">
                <div class="section-header">
                    <h5 class="mb-0">
                        <i class="ri-shopping-cart-line me-2"></i>
                        Informacioni i Porosisë
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Alert if paid -->
                    @if($onlineOrder->is_paid)
                    <div class="alert-warning-custom mb-4">
                        <div class="d-flex align-items-start">
                            <i class="ri-alert-line fs-4 me-2 text-warning"></i>
                            <div>
                                <strong>Vëmendje!</strong>
                                <p class="mb-0">
                                    Kjo porosi është shënuar si e paguar më {{ $onlineOrder->payment_received_date->format('d/m/Y') }}.
                                    Ndryshimet mund të ndikojnë në raportet financiare.
                                </p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="row">
                        <!-- Order Number (Read-only) -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Numri i Porosisë</label>
                            <input type="text" class="form-control" value="{{ $onlineOrder->order_number }}" readonly>
                        </div>

                        <!-- Status (Read-only) -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Statusi</label>
                            <input type="text" class="form-control"
                                value="{{ $onlineOrder->is_paid ? 'E Paguar' : 'E Papaguar' }}"
                                readonly>
                        </div>

                        <!-- Sale Selection -->
                        <div class="col-md-12 mb-3">
                            <label class="form-label required-field">Shitja (Invoice)</label>
                            <select class="form-select select2-sale" name="sale_id" id="sale_id" required>
                                <option value="">Zgjidh Shitjen...</option>
                                @foreach($sales as $sale)
                                <option value="{{ $sale->id }}"
                                    data-partner="{{ $sale->partner_id }}"
                                    data-partner-name="{{ $sale->partner->name }}"
                                    data-warehouse="{{ $sale->warehouse_id }}"
                                    data-warehouse-name="{{ $sale->warehouse->name }}"
                                    data-currency="{{ $sale->currency_id }}"
                                    data-currency-symbol="{{ $sale->currency->symbol }}"
                                    data-amount="{{ $sale->total_amount }}"
                                    {{ $onlineOrder->sale_id == $sale->id ? 'selected' : '' }}>
                                    {{ $sale->invoice_number }} - {{ $sale->partner->name }} ({{ number_format($sale->total_amount, 2) }} {{ $sale->currency->symbol }})
                                </option>
                                @endforeach
                            </select>
                            @error('sale_id')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Hidden fields -->
                        <input type="hidden" name="partner_id" id="partner_id" value="{{ $onlineOrder->partner_id }}">
                        <input type="hidden" name="warehouse_id" id="warehouse_id" value="{{ $onlineOrder->warehouse_id }}">
                        <input type="hidden" name="currency_id" id="currency_id" value="{{ $onlineOrder->currency_id }}">

                        <!-- Order Amount -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label required-field">Shuma e Porosisë</label>
                            <div class="input-group">
                                <input type="number"
                                    class="form-control @error('order_amount') is-invalid @enderror"
                                    name="order_amount"
                                    id="order_amount"
                                    value="{{ old('order_amount', $onlineOrder->order_amount) }}"
                                    step="0.01"
                                    min="0.01"
                                    readonly
                                    required>
                                <span class="input-group-text" id="currency-symbol">{{ $onlineOrder->currency->symbol }}</span>
                            </div>
                            @error('order_amount')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Order Date -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label required-field">Data e Porosisë</label>
                            <input type="date"
                                class="form-control @error('order_date') is-invalid @enderror"
                                name="order_date"
                                id="order_date"
                                value="{{ old('order_date', $onlineOrder->order_date->format('Y-m-d')) }}"
                                required>
                            @error('order_date')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Expected Payment Date -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Data e Pritshme e Pagesës
                                <small class="text-muted">(Opsionale)</small>
                            </label>
                            <input type="date"
                                class="form-control @error('expected_payment_date') is-invalid @enderror"
                                name="expected_payment_date"
                                id="expected_payment_date"
                                value="{{ old('expected_payment_date', $onlineOrder->expected_payment_date ? $onlineOrder->expected_payment_date->format('Y-m-d') : '') }}">
                            @error('expected_payment_date')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Delivery Address -->
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Adresa e Dërgesës</label>
                            <textarea class="form-control @error('delivery_address') is-invalid @enderror"
                                name="delivery_address"
                                rows="2"
                                placeholder="Shkruani adresën ku do të dërgohet porosia...">{{ old('delivery_address', $onlineOrder->delivery_address) }}</textarea>
                            @error('delivery_address')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Notes -->
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Shënime</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror"
                                name="notes"
                                rows="2"
                                placeholder="Shënime shtesë...">{{ old('notes', $onlineOrder->notes) }}</textarea>
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
                            <span class="text-muted">Klienti:</span>
                            <strong id="summary-partner">{{ $onlineOrder->partner->name }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Magazina:</span>
                            <strong id="summary-warehouse">{{ $onlineOrder->warehouse->name }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Data:</span>
                            <strong id="summary-date">{{ $onlineOrder->order_date->format('d/m/Y') }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Pritje Pagese:</span>
                            <strong id="summary-expected">{{ $onlineOrder->expected_payment_date ? $onlineOrder->expected_payment_date->format('d/m/Y') : '-' }}</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span class="text-primary fw-bold">Shuma:</span>
                            <h5 class="mb-0 text-primary" id="summary-amount">{{ number_format($onlineOrder->order_amount, 2) }} {{ $onlineOrder->currency->symbol }}</h5>
                        </div>
                    </div>

                    @if($onlineOrder->is_paid)
                    <div class="alert alert-success">
                        <i class="ri-check-line me-2"></i>
                        <small>
                            Kjo porosi është paguar më <strong>{{ $onlineOrder->payment_received_date->format('d/m/Y') }}</strong>
                        </small>
                    </div>
                    @endif

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="ri-save-line me-1"></i> Përditëso Porosinë
                        </button>
                        <a href="{{ route('online-orders.show', $onlineOrder->id) }}" class="btn btn-secondary">
                            <i class="ri-close-line me-1"></i> Anulo
                        </a>
                        <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                            <i class="ri-delete-bin-line me-1"></i> Fshi Porosinë
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Delete Form -->
<form id="deleteForm" action="{{ route('online-orders.destroy', $onlineOrder->id) }}" method="POST" style="display: none;">
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
        $('.select2-sale').select2({
            placeholder: 'Zgjidh Shitjen...',
            allowClear: true
        });

        // Update form when sale is selected
        $('#sale_id').on('change', function() {
            const selected = $(this).find(':selected');

            if (selected.val()) {
                $('#partner_id').val(selected.data('partner'));
                $('#warehouse_id').val(selected.data('warehouse'));
                $('#currency_id').val(selected.data('currency'));
                $('#order_amount').val(parseFloat(selected.data('amount')).toFixed(2));
                $('#currency-symbol').text(selected.data('currency-symbol'));

                updateSummary();
            }
        });

        // Update summary on date change
        $('#order_date, #expected_payment_date').on('change', updateSummary);

        function updateSummary() {
            const selected = $('#sale_id option:selected');
            const orderDate = $('#order_date').val();
            const expectedDate = $('#expected_payment_date').val();

            if (selected.val()) {
                $('#summary-partner').text(selected.data('partner-name'));
                $('#summary-warehouse').text(selected.data('warehouse-name'));
                $('#summary-date').text(orderDate ? formatDate(orderDate) : '-');
                $('#summary-expected').text(expectedDate ? formatDate(expectedDate) : '-');
                $('#summary-amount').text(
                    parseFloat(selected.data('amount')).toFixed(2) + ' ' + selected.data('currency-symbol')
                );
            }
        }

        function formatDate(dateStr) {
            if (!dateStr) return '-';
            const parts = dateStr.split('-');
            return parts[2] + '/' + parts[1] + '/' + parts[0];
        }

        // Form validation
        $('#orderForm').on('submit', function(e) {
            const orderDate = new Date($('#order_date').val());
            const expectedDate = $('#expected_payment_date').val() ? new Date($('#expected_payment_date').val()) : null;

            if (expectedDate && expectedDate < orderDate) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Gabim!',
                    text: 'Data e pritshme e pagesës nuk mund të jetë para datës së porosisë!'
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