@extends('layouts.app')

@section('title', 'Create Invoice')

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
            <h4 class="mb-sm-0">CREATE INVOICE</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('sales.index') }}">Invoice</a></li>
                    <li class="breadcrumb-item active">Create Invoice</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('sales.store') }}" id="saleForm">
    @csrf
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="invoice_date" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Delivery Date</label>
                            <input type="date" class="form-control" name="delivery_date" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Warehouse <span class="text-danger">*</span></label>
                            <select class="form-select" name="warehouse_id" required>
                                <option value="">Depot</option>
                                @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Payment Status <span class="text-danger">*</span></label>
                            <select class="form-select" name="payment_status" required>
                                <option value="Unpaid">Unpaid</option>
                                <option value="Paid">Paid</option>
                                <option value="Partial">Partial</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Sale Status <span class="text-danger">*</span></label>
                            <select class="form-select" name="sale_status" required>
                                <option value="Confirmed">Confirmed</option>
                                <option value="Draft">Draft</option>
                                <option value="PrePaid">PrePaid</option>
                                <option value="Rejected">Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3 mt-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" value="Cash" checked>
                                    <label class="form-check-label">Cash</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" value="Bank">
                                    <label class="form-check-label">Bank</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Client <span class="text-danger">*</span></label>
                            <select class="form-select" name="partner_id" required>
                                <option value="">Choose...</option>
                                @foreach($partners as $partner)
                                <option value="{{ $partner->id }}">{{ $partner->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Currency <span class="text-danger">*</span></label>
                            <select class="form-select" name="currency_id" required>
                                <option value="">ALL</option>
                                @foreach($currencies as $currency)
                                <option value="{{ $currency->id }}">{{ $currency->code }} ({{ $currency->symbol }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <hr class="my-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Products</h5>
                    </div>

                    <select id="searchProduct" style="width: 100%" placeholder="Search Product"></select>

                    <div id="productsContainer" class="mt-3"></div>

                    <div class="row mt-4">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Payment Term</label>
                            <select class="form-select" name="payment_term">
                                <option value="">Select...</option>
                                <option value="Net 15">Net 15</option>
                                <option value="Net 30">Net 30</option>
                                <option value="Net 45">Net 45</option>
                                <option value="Net 60">Net 60</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Due Date</label>
                            <input type="date" class="form-control" name="due_date">
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label">Note</label>
                        <textarea class="form-control" name="notes" rows="3"></textarea>
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
                            <span id="subtotalDisplay">L 0.00</span>
                        </div>
                        <div class="summary-row">
                            <span>Estimated Tax:</span>
                            <span id="taxDisplay">L 0.00</span>
                        </div>
                        <div class="summary-row">
                            <span>Discount:</span>
                            <span id="discountDisplay">L 0.00</span>
                        </div>
                        <div class="summary-row">
                            <span>Total Amount:</span>
                            <span id="totalDisplay">L 0.00</span>
                        </div>
                    </div>
                    <div class="mt-4 d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="ri-save-line me-1"></i> Save Invoice
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
                ${details ? `<small class="text-muted">${details}</small><br>` : ''}
                <small class="text-info">Stock: ${product.quantity}</small>
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
                <textarea class="form-control form-control-sm imei-input" name="items[${productIndex}][imei_numbers]" rows="2" placeholder="Enter IMEI separated by commas (15 digits each)..." required></textarea>
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
        $('#subtotalDisplay').text('L ' + subtotal.toFixed(2));
        $('#taxDisplay').text('L ' + totalTax.toFixed(2));
        $('#discountDisplay').text('L ' + totalDiscount.toFixed(2));
        $('#totalDisplay').text('L ' + totalAmount.toFixed(2));
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
                    text: response.message || 'Invoice created successfully'
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