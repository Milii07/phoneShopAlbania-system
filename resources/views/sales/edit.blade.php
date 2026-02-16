@extends('layouts.app')

@section('title', 'Edit Invoice')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .product-item {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
    }

    .product-item:hover {
        background: #e9ecef;
    }

    .summary-box {
        background: #f8f9fa;
        border: 2px solid #dee2e6;
        border-radius: 8px;
        padding: 20px;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #dee2e6;
    }

    .summary-row:last-child {
        border-bottom: none;
        font-weight: bold;
        font-size: 1.2rem;
        color: #198754;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">EDIT INVOICE</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('sales.index') }}">Invoice</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('sales.update', $sale->id) }}" id="saleForm">
    @csrf
    @method('PUT')
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="invoice_date" value="{{ $sale->invoice_date->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Data e Blerjes</label>
                            <input type="date" class="form-control" name="delivery_date" value="{{ $sale->delivery_date ? $sale->delivery_date->format('Y-m-d') : '' }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Dyqani <span class="text-danger">*</span></label>
                            <select class="form-select" name="warehouse_id" required>
                                <option value="">Depot</option>
                                @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" {{ $sale->warehouse_id == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Statusi i Pagesës <span class="text-danger">*</span></label>
                            <select class="form-select" name="payment_status" required>
                                <option value="Unpaid" {{ $sale->payment_status == 'Unpaid' ? 'selected' : '' }}>Pa Pagesë</option>
                                <option value="Paid" {{ $sale->payment_status == 'Paid' ? 'selected' : '' }}>Me Pagesë</option>
                                <option value="Partial" {{ $sale->payment_status == 'Partial' ? 'selected' : '' }}>Pjesërisht i Paguar</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Statusi i Shitjes <span class="text-danger">*</span></label>
                            <select class="form-select" name="sale_status" required>
                                <option value="Confirmed" {{ $sale->sale_status == 'Confirmed' ? 'selected' : '' }}>Konfirmuar</option>
                                <option value="Draft" {{ $sale->sale_status == 'Draft' ? 'selected' : '' }}>Draft</option>
                                <option value="PrePaid" {{ $sale->sale_status == 'PrePaid' ? 'selected' : '' }}>Parapaguar</option>
                                <option value="Rejected" {{ $sale->sale_status == 'Rejected' ? 'selected' : '' }}>Refuzuar</option>
                            </select>
                        </div>

                        <!-- Payment Method & Purchase Location në një rresht -->
                        <div class="col-md-8 mb-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Metoda e Pagesës <span class="text-danger">*</span></label>
                                    <div class="d-flex gap-3 mt-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="payment_method" id="payment_cash" value="Cash" {{ $sale->payment_method == 'Cash' ? 'checked' : '' }} required>
                                            <label class="form-check-label" for="payment_cash">Cash</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="payment_method" id="payment_bank" value="Bank" {{ $sale->payment_method == 'Bank' ? 'checked' : '' }} required>
                                            <label class="form-check-label" for="payment_bank">Bank</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Vendi i Blerjes <span class="text-danger">*</span></label>
                                    <div class="d-flex gap-3 mt-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="purchase_location" id="shop" value="shop" {{ $sale->purchase_location == 'shop' ? 'checked' : '' }} required>
                                            <label class="form-check-label" for="shop">Dyqan</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="purchase_location" id="online" value="online" {{ $sale->purchase_location == 'online' ? 'checked' : '' }} required>
                                            <label class="form-check-label" for="online">Online</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Client <span class="text-danger">*</span></label>
                            <select class="form-select select2-client" name="partner_id" required>
                                <option value="">Choose...</option>
                                @foreach($partners as $partner)
                                <option value="{{ $partner->id }}" {{ $sale->partner_id == $partner->id ? 'selected' : '' }}>{{ $partner->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Seller <span class="text-danger">*</span></label>
                            <select class="form-select select2-seller" name="seller_id" required>
                                <option value="">Choose...</option>
                                @foreach($sellers as $seller)
                                <option value="{{ $seller->id }}" {{ $sale->seller_id == $seller->id ? 'selected' : '' }}>{{ $seller->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Currency <span class="text-danger">*</span></label>
                            <select class="form-select" name="currency_id" id="currency_id" required>
                                <option value="">Select Currency</option>
                                @foreach($currencies as $currency)
                                <option value="{{ $currency->id }}"
                                    data-symbol="{{ $currency->symbol }}"
                                    {{ $sale->currency_id == $currency->id ? 'selected' : '' }}>
                                    {{ $currency->code }} ({{ $currency->symbol }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <hr class="my-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Products</h5>
                    </div>

                    <select id="searchProduct" style="width: 100%" placeholder="Search Product"></select>

                    <div id="productsContainer" class="mt-3">
                        @foreach($sale->items as $index => $item)
                        @php
                        $needsImei = $item->storage || $item->ram || $item->color;
                        $imeiNumbers = '';
                        if ($item->imei_numbers && is_array($item->imei_numbers)) {
                        $imeiNumbers = implode(', ', $item->imei_numbers);
                        }
                        @endphp
                        <div class="product-item" data-index="{{ $index }}" data-needs-imei="{{ $needsImei ? 'true' : 'false' }}">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h6 class="mb-0">{{ $item->product_name }}</h6>
                                    @if($item->storage || $item->ram || $item->color)
                                    <small class="text-muted">
                                        @if($item->storage){{ $item->storage }}@endif
                                        @if($item->ram) | {{ $item->ram }}@endif
                                        @if($item->color) | {{ $item->color }}@endif
                                    </small>
                                    @endif
                                </div>
                                <button type="button" class="btn btn-sm btn-danger remove-item">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </div>
                            <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item->product_id }}">
                            <div class="row g-2">
                                <div class="col-md-3">
                                    <label class="form-label small">Qty *</label>
                                    <input type="number" class="form-control form-control-sm quantity-input" name="items[{{ $index }}][quantity]" value="{{ $item->quantity }}" min="1" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small">Unit Type</label>
                                    <select class="form-select form-select-sm" name="items[{{ $index }}][unit_type]">
                                        <option value="Pcs" {{ $item->unit_type == 'Pcs' ? 'selected' : '' }}>Pcs</option>
                                        <option value="Box" {{ $item->unit_type == 'Box' ? 'selected' : '' }}>Box</option>
                                        <option value="Kg" {{ $item->unit_type == 'Kg' ? 'selected' : '' }}>Kg</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small">Unit Price *</label>
                                    <input type="number" class="form-control form-control-sm unit-price-input" name="items[{{ $index }}][unit_price]" value="{{ $item->unit_price }}" step="0.01" min="0" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small">Discount</label>
                                    <input type="number" class="form-control form-control-sm discount-input" name="items[{{ $index }}][discount]" value="{{ $item->discount }}" step="0.01" min="0">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small">Tax</label>
                                    <input type="number" class="form-control form-control-sm tax-input" name="items[{{ $index }}][tax]" value="{{ $item->tax }}" step="0.01" min="0">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small">Line Total</label>
                                    <input type="text" class="form-control form-control-sm line-total" value="{{ number_format($item->line_total, 2) }}" readonly>
                                </div>
                                @if($needsImei)
                                <div class="col-md-12 imei-container mt-2">
                                    <label class="form-label small">IMEI <span class="text-danger">*</span></label>
                                    <textarea class="form-control form-control-sm imei-input" name="items[{{ $index }}][imei_numbers]" rows="2" required>{{ $imeiNumbers }}</textarea>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Payment Term</label>
                            <select class="form-select" name="payment_term">
                                <option value="Due on Receipt" {{ $sale->payment_term == 'Due on Receipt' ? 'selected' : '' }}>NE momentin e pranimit</option>
                                <option value="Net 15" {{ $sale->payment_term == 'Net 15' ? 'selected' : '' }}>pas 15</option>
                                <option value="Net 30" {{ $sale->payment_term == 'Net 30' ? 'selected' : '' }}>pas 30</option>
                                <option value="Net 45" {{ $sale->payment_term == 'Net 45' ? 'selected' : '' }}>pas 45</option>
                                <option value="Net 60" {{ $sale->payment_term == 'Net 60' ? 'selected' : '' }}>pas 60</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3">{{ $sale->description }}</textarea>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Due Date</label>
                            <input type="date" class="form-control" name="due_date" value="{{ $sale->due_date ? $sale->due_date->format('Y-m-d') : '' }}">
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label">Note</label>
                        <textarea class="form-control" name="notes" rows="3">{{ $sale->notes }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">Summary</h5>
                    <div class="summary-box">
                        <div class="summary-row">
                            <span>Sub Total:</span>
                            <span id="subtotalDisplay"><span class="currency-symbol">{{ $sale->currency->symbol }}</span> 0.00</span>
                        </div>
                        <div class="summary-row">
                            <span>Estimated Tax:</span>
                            <span id="taxDisplay"><span class="currency-symbol">{{ $sale->currency->symbol }}</span> 0.00</span>
                        </div>
                        <div class="summary-row">
                            <span>Discount:</span>
                            <span id="discountDisplay"><span class="currency-symbol">{{ $sale->currency->symbol }}</span> 0.00</span>
                        </div>
                        <div class="summary-row">
                            <span>Total Amount:</span>
                            <span id="totalDisplay"><span class="currency-symbol">{{ $sale->currency->symbol }}</span> 0.00</span>
                        </div>
                    </div>
                    <div class="mt-4 d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="ri-save-line me-1"></i> Update Invoice
                        </button>
                        <a href="{{ route('sales.index') }}" class="btn btn-secondary">
                            <i class="ri-close-line me-1"></i> Cancel
                        </a>
                    </div>
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
    let productIndex = 0;
    let currentCurrencySymbol = '{{ $sale->currency->symbol }}';

    $(document).ready(function() {
        // Initialize Select2
        $('.select2-client, .select2-seller').select2({
            placeholder: 'Choose...',
            allowClear: true
        });

        // Currency symbol update
        $('#currency_id').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            const symbol = selectedOption.data('symbol') || 'L';
            currentCurrencySymbol = symbol;
            $('.currency-symbol').text(symbol);
        });

        // Auto-set payment status to Unpaid when Online is selected
        $('input[name="purchase_location"]').on('change', function() {
            if ($(this).val() === 'online') {
                $('select[name="payment_status"]').val('Unpaid');
            } else {
                $('select[name="payment_status"]').val('Paid');
            }
        });

        // Search Product
        $('#searchProduct').select2({
            placeholder: 'Search product...',
            minimumInputLength: 2,
            allowClear: true,
            ajax: {
                url: '/sales-api/search-products',
                dataType: 'json',
                delay: 300,
                data: function(params) {
                    return {
                        q: params.term
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.map(function(product) {
                            let text = product.name;
                            if (product.storage) text += ' - ' + product.storage;
                            if (product.ram) text += ' | ' + product.ram;
                            if (product.color) text += ' | ' + product.color;
                            text += ' (Stock: ' + product.quantity + ')';
                            return {
                                id: product.id,
                                text: text,
                                product: product
                            };
                        })
                    };
                },
                cache: true
            }
        }).on('select2:select', function(e) {
            addProductItem(e.params.data.product);
            $(this).val(null).trigger('change');
        });

        calculateTotals();

        $(document).on('input', '.unit-price-input, .discount-input, .tax-input, .quantity-input', function() {
            updateItemTotal($(this).closest('.product-item'));
            calculateTotals();
        });

        $(document).on('click', '.remove-item', function() {
            $(this).closest('.product-item').remove();
            calculateTotals();
        });
    });

    function addProductItem(product) {
        productIndex++;
        let details = '';
        if (product.storage) details += product.storage;
        if (product.ram) details += (details ? ' | ' : '') + product.ram;
        if (product.color) details += (details ? ' | ' : '') + product.color;
        const needsImei = product.storage || product.ram || product.color;

        const html = `
    <div class="product-item" data-index="${productIndex}" data-needs-imei="${needsImei}">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h6 class="mb-0">${product.name}</h6>
                ${details ? `<small class="text-muted">${details}</small>` : ''}
            </div>
            <button type="button" class="btn btn-sm btn-danger remove-item">
                <i class="ri-delete-bin-line"></i>
            </button>
        </div>
        <input type="hidden" name="items[${productIndex}][product_id]" value="${product.id}">
        <div class="row g-2">
            <div class="col-md-3">
                <label class="form-label small">Qty *</label>
                <input type="number" class="form-control form-control-sm quantity-input" name="items[${productIndex}][quantity]" value="1" min="1" max="${product.quantity}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Unit Type</label>
                <select class="form-select form-select-sm" name="items[${productIndex}][unit_type]">
                    <option value="Pcs">Pcs</option>
                    <option value="Box">Box</option>
                    <option value="Kg">Kg</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Unit Price *</label>
                <input type="number" class="form-control form-control-sm unit-price-input" name="items[${productIndex}][unit_price]" value="${product.price || 0}" step="0.01" min="0" required>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Discount</label>
                <input type="number" class="form-control form-control-sm discount-input" name="items[${productIndex}][discount]" value="0" step="0.01" min="0">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Tax</label>
                <input type="number" class="form-control form-control-sm tax-input" name="items[${productIndex}][tax]" value="0" step="0.01" min="0">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Line Total</label>
                <input type="text" class="form-control form-control-sm line-total" value="0.00" readonly>
            </div>
            ${needsImei ? `
            <div class="col-md-12 imei-container mt-2">
                <label class="form-label small">IMEI <span class="text-danger">*</span></label>
                <textarea class="form-control form-control-sm imei-input" name="items[${productIndex}][imei_numbers]" rows="2" required></textarea>
            </div>
            ` : ''}
        </div>
    </div>`;

        $('#productsContainer').append(html);
        updateItemTotal($(`[data-index="${productIndex}"]`));
        calculateTotals();
    }

    function updateItemTotal(item) {
        const qty = parseFloat(item.find('.quantity-input').val()) || 0;
        const price = parseFloat(item.find('.unit-price-input').val()) || 0;
        const discount = parseFloat(item.find('.discount-input').val()) || 0;
        const tax = parseFloat(item.find('.tax-input').val()) || 0;
        const total = (qty * price) - discount + tax;
        item.find('.line-total').val(total.toFixed(2));
    }

    function calculateTotals() {
        let subtotal = 0,
            totalTax = 0,
            totalDiscount = 0;
        $('.product-item').each(function() {
            const qty = parseFloat($(this).find('.quantity-input').val()) || 0;
            const price = parseFloat($(this).find('.unit-price-input').val()) || 0;
            const discount = parseFloat($(this).find('.discount-input').val()) || 0;
            const tax = parseFloat($(this).find('.tax-input').val()) || 0;
            subtotal += (qty * price);
            totalTax += tax;
            totalDiscount += discount;
        });
        const totalAmount = subtotal - totalDiscount + totalTax;

        $('#subtotalDisplay').html(`<span class="currency-symbol">${currentCurrencySymbol}</span> ${subtotal.toFixed(2)}`);
        $('#taxDisplay').html(`<span class="currency-symbol">${currentCurrencySymbol}</span> ${totalTax.toFixed(2)}`);
        $('#discountDisplay').html(`<span class="currency-symbol">${currentCurrencySymbol}</span> ${totalDiscount.toFixed(2)}`);
        $('#totalDisplay').html(`<span class="currency-symbol">${currentCurrencySymbol}</span> ${totalAmount.toFixed(2)}`);
    }

    $('#saleForm').on('submit', function(e) {
        e.preventDefault();

        Swal.fire({
            title: 'Duke Përditësuar...',
            text: 'Ju lutem prisni',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Sukses!',
                    text: response.message || 'Fatura u përditësua me sukses'
                }).then(() => {
                    if (response.url) {
                        window.location.href = response.url;
                    } else {
                        window.location.href = '{{ route("sales.index") }}';
                    }
                });
            },
            error: function(xhr) {
                let errorsHtml = '';
                if (xhr.responseJSON && Array.isArray(xhr.responseJSON.message)) {
                    errorsHtml = xhr.responseJSON.message.join('<br>');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorsHtml = xhr.responseJSON.message;
                } else {
                    errorsHtml = 'Ka ndodhur një gabim i papritur.';
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Gabim në Validim!',
                    html: errorsHtml,
                    width: '600px'
                });
            }
        });
    });
</script>
@endpush