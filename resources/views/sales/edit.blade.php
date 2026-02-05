@extends('layouts.app')

@section('title', 'Edit Invoice')

@push('styles')
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
                            <label class="form-label">Delivery Date</label>
                            <input type="date" class="form-control" name="delivery_date" value="{{ $sale->delivery_date ? $sale->delivery_date->format('Y-m-d') : '' }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Warehouse <span class="text-danger">*</span></label>
                            <select class="form-select" name="warehouse_id" required>
                                <option value="">Depot</option>
                                @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" {{ $sale->warehouse_id == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Payment Status <span class="text-danger">*</span></label>
                            <select class="form-select" name="payment_status" required>
                                <option value="Unpaid" {{ $sale->payment_status == 'Unpaid' ? 'selected' : '' }}>Unpaid</option>
                                <option value="Paid" {{ $sale->payment_status == 'Paid' ? 'selected' : '' }}>Paid</option>
                                <option value="Partial" {{ $sale->payment_status == 'Partial' ? 'selected' : '' }}>Partial</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Sale Status <span class="text-danger">*</span></label>
                            <select class="form-select" name="sale_status" required>
                                <option value="Confirmed" {{ $sale->sale_status == 'Confirmed' ? 'selected' : '' }}>Confirmed</option>
                                <option value="Draft" {{ $sale->sale_status == 'Draft' ? 'selected' : '' }}>Draft</option>
                                <option value="PrePaid" {{ $sale->sale_status == 'PrePaid' ? 'selected' : '' }}>PrePaid</option>
                                <option value="Rejected" {{ $sale->sale_status == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3 mt-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" value="Cash" {{ $sale->payment_method == 'Cash' ? 'checked' : '' }}>
                                    <label class="form-check-label">Cash</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" value="Bank" {{ $sale->payment_method == 'Bank' ? 'checked' : '' }}>
                                    <label class="form-check-label">Bank</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Client <span class="text-danger">*</span></label>
                            <select class="form-select" name="partner_id" required>
                                <option value="">Choose...</option>
                                @foreach($partners as $partner)
                                <option value="{{ $partner->id }}" {{ $sale->partner_id == $partner->id ? 'selected' : '' }}>{{ $partner->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Currency <span class="text-danger">*</span></label>
                            <select class="form-select" name="currency_id" required>
                                <option value="">ALL</option>
                                @foreach($currencies as $currency)
                                <option value="{{ $currency->id }}" {{ $sale->currency_id == $currency->id ? 'selected' : '' }}>{{ $currency->code }} ({{ $currency->symbol }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <hr class="my-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Products</h5>
                    </div>

                    <select id="searchProduct" style="width: 100%"></select>

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
                                <option value="">Select...</option>
                                <option value="Net 15" {{ $sale->payment_term == 'Net 15' ? 'selected' : '' }}>Net 15</option>
                                <option value="Net 30" {{ $sale->payment_term == 'Net 30' ? 'selected' : '' }}>Net 30</option>
                                <option value="Net 45" {{ $sale->payment_term == 'Net 45' ? 'selected' : '' }}>Net 45</option>
                                <option value="Net 60" {{ $sale->payment_term == 'Net 60' ? 'selected' : '' }}>Net 60</option>
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
                            <span id="subtotalDisplay">0.00</span>
                        </div>
                        <div class="summary-row">
                            <span>Estimated Tax:</span>
                            <span id="taxDisplay">0.00</span>
                        </div>
                        <div class="summary-row">
                            <span>Discount:</span>
                            <span id="discountDisplay">0.00</span>
                        </div>
                        <div class="summary-row">
                            <span>Total Amount:</span>
                            <span id="totalDisplay">0.00</span>
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
<script>
    let productIndex = 0;

    $(document).ready(function() {
        $('#searchProduct').select2({
            placeholder: 'Search product...',
            minimumInputLength: 2,
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
                            return {
                                id: product.id,
                                text: product.name + (product.storage ? ' - ' + product.storage : '') + ' (Stock: ' + product.quantity + ')',
                                product: product
                            };
                        })
                    };
                }
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
                <input type="number" class="form-control form-control-sm unit-price-input" name="items[${productIndex}][unit_price]" value="${product.price}" step="0.01" min="0" required>
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
        $('#subtotalDisplay').text(subtotal.toFixed(2));
        $('#taxDisplay').text(totalTax.toFixed(2));
        $('#discountDisplay').text(totalDiscount.toFixed(2));
        $('#totalDisplay').text(totalAmount.toFixed(2));
    }

    $('#saleForm').on('submit', function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Processing...',
            text: 'Please wait',
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
                    title: 'Success!',
                    text: response.message || 'Invoice updated successfully'
                }).then(() => {
                    if (response.url) window.location.href = response.url;
                });
            },
            error: function(xhr) {
                let errorsHtml = '';
                if (xhr.responseJSON && Array.isArray(xhr.responseJSON.message)) {
                    errorsHtml = xhr.responseJSON.message.join('<br>');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorsHtml = xhr.responseJSON.message;
                } else {
                    errorsHtml = 'An unexpected error occurred.';
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error!',
                    html: errorsHtml,
                    width: '600px'
                });
            }
        });
    });
</script>
@endpush