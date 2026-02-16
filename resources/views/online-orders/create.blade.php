@extends('layouts.app')

@section('title', 'Shto Porosi Online')

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

    .sale-info-box {
        background: #f8f9fa;
        border: 2px solid #dee2e6;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">🌐 Shto Porosi Online</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('online-orders.index') }}">Porositë Online</a></li>
                    <li class="breadcrumb-item active">Shto Porosi</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('online-orders.store') }}" id="orderForm">
    @csrf
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
                    @if($sales->isEmpty())
                    <div class="alert alert-warning">
                        <i class="ri-alert-line me-2"></i>
                        <strong>Nuk ka shitje online të disponueshme!</strong>
                        <p class="mb-0">Të gjitha shitjet online kanë porosi tashmë, ose nuk ka asnjë shitje online të regjistruar.</p>
                        <a href="{{ route('sales.create') }}" class="btn btn-sm btn-primary mt-2">
                            <i class="ri-add-line me-1"></i> Krijo Shitje Online
                        </a>
                    </div>
                    @else
                    <div class="row">
                        <!-- Sale Selection -->
                        <div class="col-md-12 mb-3">
                            <label class="form-label required-field">Shitja (Invoice)</label>
                            <div class="d-flex align-items-center mb-2 gap-2">
                                <input type="checkbox" id="select_all_sales" />
                                <label for="select_all_sales" class="mb-0">Zgjidh të gjitha</label>
                            </div>
                            <select class="form-select select2-sale" name="sale_ids[]" id="sale_id" multiple required>
                                @foreach($sales as $sale)
                                <option value="{{ $sale->id }}"
                                    data-partner="{{ $sale->partner_id }}"
                                    data-partner-name="{{ $sale->partner->name }}"
                                    data-warehouse="{{ $sale->warehouse_id }}"
                                    data-warehouse-name="{{ $sale->warehouse->name }}"
                                    data-currency="{{ $sale->currency_id }}"
                                    data-currency-symbol="{{ $sale->currency->symbol }}"
                                    data-amount="{{ $sale->total_amount }}"
                                    data-date="{{ $sale->invoice_date->format('Y-m-d') }}"
                                    {{ (is_array(old('sale_ids')) && in_array($sale->id, old('sale_ids'))) ? 'selected' : '' }}>
                                    {{ $sale->invoice_number }} - {{ $sale->partner->name }} ({{ number_format($sale->total_amount, 2) }} {{ $sale->currency->symbol }})
                                </option>
                                @endforeach
                            </select>
                            @error('sale_ids')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Sale Info Display -->
                        <div class="col-md-12 mb-3" id="sale-info-container" style="display: none;">
                            <div class="sale-info-box">
                                <h6 class="mb-3">
                                    <i class="ri-information-line me-2"></i>
                                    Informacioni i Shitjes
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-2"><strong>Klienti:</strong> <span id="info-partner">-</span></p>
                                        <p class="mb-2"><strong>Magazina:</strong> <span id="info-warehouse">-</span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-2"><strong>Data e Shitjes:</strong> <span id="info-date">-</span></p>
                                        <p class="mb-2"><strong>Shuma:</strong> <span id="info-amount" class="text-success fw-bold">-</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Hidden fields auto-populated -->
                        <input type="hidden" name="partner_id" id="partner_id" value="{{ old('partner_id') }}">
                        <input type="hidden" name="warehouse_id" id="warehouse_id" value="{{ old('warehouse_id') }}">
                        <input type="hidden" name="currency_id" id="currency_id" value="{{ old('currency_id') }}">

                        <!-- Order Amount -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label required-field">Shuma e Porosisë</label>
                            <div class="input-group">
                                <input type="number"
                                    class="form-control @error('order_amount') is-invalid @enderror"
                                    name="order_amount"
                                    id="order_amount"
                                    value="{{ old('order_amount') }}"
                                    step="0.01"
                                    min="0.01"
                                    placeholder="0.00"
                                    readonly
                                    required>
                                <span class="input-group-text" id="currency-symbol">L</span>
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
                                value="{{ old('order_date', date('Y-m-d')) }}"
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
                                value="{{ old('expected_payment_date') }}">
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
                                placeholder="Shkruani adresën ku do të dërgohet porosia...">{{ old('delivery_address') }}</textarea>
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
                                placeholder="Shënime shtesë...">{{ old('notes') }}</textarea>
                            @error('notes')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    @endif
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
                            <strong id="summary-partner">-</strong>
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
                            <span class="text-muted">Pritje Pagese:</span>
                            <strong id="summary-expected">-</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span class="text-primary fw-bold">Shuma:</span>
                            <h5 class="mb-0 text-primary" id="summary-amount">0.00 L</h5>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="ri-information-line me-2"></i>
                        <small>
                            Kjo porosi është për një shitje online që pret pagesën nga posta.
                        </small>
                    </div>

                    @if(!$sales->isEmpty())
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="ri-save-line me-1"></i> Ruaj Porosinë
                        </button>
                        <a href="{{ route('online-orders.index') }}" class="btn btn-secondary">
                            <i class="ri-close-line me-1"></i> Anulo
                        </a>
                    </div>
                    @endif
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
                        <li class="mb-2">Zgjidhni shitjen online që pret pagesën</li>
                        <li class="mb-2">Vendosni datën kur prisni të merrni pagesën</li>
                        <li class="mb-2">Kur posta të paguajë, shënojeni si "E Paguar"</li>
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
        $('.select2-sale').select2({
            placeholder: 'Zgjidh Shitjen...',
            allowClear: true
        });

        // Update form when sale(s) are selected
        $('#sale_id').on('change', function() {
            const selectedOptions = $(this).find(':selected');

            if (selectedOptions.length > 0) {
                // If single selection, populate partner/warehouse/currency info for preview
                if (selectedOptions.length === 1) {
                    const s = selectedOptions.first();
                    $('#partner_id').val(s.data('partner'));
                    $('#warehouse_id').val(s.data('warehouse'));
                    $('#currency_id').val(s.data('currency'));
                    $('#currency-symbol').text(s.data('currency-symbol'));

                    $('#info-partner').text(s.data('partner-name'));
                    $('#info-warehouse').text(s.data('warehouse-name'));
                    $('#info-date').text(formatDate(s.data('date')));
                    $('#info-amount').text(parseFloat(s.data('amount')).toFixed(2) + ' ' + s.data('currency-symbol'));
                    $('#sale-info-container').slideDown();
                } else {
                    // Multiple selection: clear per-sale hidden fields and show aggregate info
                    $('#partner_id').val('');
                    $('#warehouse_id').val('');
                    $('#currency_id').val('');
                    $('#info-partner').text('—');
                    $('#info-warehouse').text('—');
                    $('#info-date').text('-');
                    $('#info-amount').text('-');
                    $('#sale-info-container').slideDown();
                }

                // Compute total amount across selected sales
                let total = 0;
                let currencySymbol = '';
                selectedOptions.each(function() {
                    const a = parseFloat($(this).data('amount')) || 0;
                    total += a;
                    if (!currencySymbol) currencySymbol = $(this).data('currency-symbol') || '';
                });
                $('#order_amount').val(total.toFixed(2));
                $('#currency-symbol').text(currencySymbol || $('#currency-symbol').text());

                // Update summary
                updateSummary();
            } else {
                $('#sale-info-container').slideUp();
                $('#order_amount').val('');
                $('#partner_id').val('');
                $('#warehouse_id').val('');
                $('#currency_id').val('');
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

        // Select all checkbox behavior
        $('#select_all_sales').on('change', function() {
            if ($(this).is(':checked')) {
                // select all options
                $('#sale_id option').prop('selected', true);
            } else {
                $('#sale_id option').prop('selected', false);
            }
            $('#sale_id').trigger('change');
        });

        // Form validation
        $('#orderForm').on('submit', function(e) {
            const selected = $('#sale_id').val();
            if (!selected || (Array.isArray(selected) && selected.length === 0)) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Gabim!',
                    text: 'Ju lutem zgjidhni të paktën një shitje!'
                });
                return false;
            }

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
</script>
@endpush