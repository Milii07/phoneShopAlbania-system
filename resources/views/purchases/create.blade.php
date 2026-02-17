@extends('layouts.app')

@section('title', 'Krijo Blerje')

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

    .remove-item {
        cursor: pointer;
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
            <h4 class="mb-sm-0">Krijo Blerje</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('purchases.index') }}">Blerjet</a></li>
                    <li class="breadcrumb-item active">Krijo Blerje</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('purchases.store') }}" enctype="multipart/form-data" id="purchaseForm">
    @csrf

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="purchase_date" class="form-label">Data <span class="text-danger">*</span></label>
                            <input type="date"
                                class="form-control @error('purchase_date') is-invalid @enderror"
                                id="purchase_date"
                                name="purchase_date"
                                value="{{ old('purchase_date', date('Y-m-d')) }}"
                                required>
                            @error('purchase_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="due_date" class="form-label">Due Date</label>
                            <input type="date"
                                class="form-control @error('due_date') is-invalid @enderror"
                                id="due_date"
                                name="due_date"
                                value="{{ old('due_date') }}">
                            @error('due_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="warehouse_id" class="form-label">Warehouse <span class="text-danger">*</span></label>
                            <select class="form-select @error('warehouse_id') is-invalid @enderror"
                                id="warehouse_id"
                                name="warehouse_id"
                                required>
                                <option value="">Zgjidh Warehouse</option>
                                @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                    {{ $warehouse->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('warehouse_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="order_status" class="form-label">Order Status <span class="text-danger">*</span></label>
                            <select class="form-select @error('order_status') is-invalid @enderror"
                                id="order_status"
                                name="order_status"
                                required>
                                <option value="Received" {{ old('order_status') == 'Received' ? 'selected' : '' }}>Received</option>
                                <option value="Pending" {{ old('order_status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                                <option value="Cancelled" {{ old('order_status') == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                            @error('order_status')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="payment_status" class="form-label">Payment Status <span class="text-danger">*</span></label>
                            <select class="form-select @error('payment_status') is-invalid @enderror"
                                id="payment_status"
                                name="payment_status"
                                required>
                                <option value="Paid" {{ old('payment_status') == 'Paid' ? 'selected' : '' }}>Paid</option>
                                <option value="Unpaid" {{ old('payment_status') == 'Unpaid' ? 'selected' : '' }}>Unpaid</option>
                                <option value="Partial" {{ old('payment_status') == 'Partial' ? 'selected' : '' }}>Partial</option>
                            </select>
                            @error('payment_status')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="payment_cash" value="Cash" {{ old('payment_method', 'Cash') == 'Cash' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="payment_cash">
                                        Cash
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="payment_bank" value="Bank" {{ old('payment_method') == 'Bank' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="payment_bank">
                                        Bank
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="partner_id" class="form-label">Supplier <span class="text-danger">*</span></label>
                            <select class="form-select @error('partner_id') is-invalid @enderror"
                                id="partner_id"
                                name="partner_id"
                                required>
                                <option value="">Zgjidh Supplier</option>
                                @foreach($partners as $partner)
                                <option value="{{ $partner->id }}" {{ old('partner_id') == $partner->id ? 'selected' : '' }}>
                                    {{ $partner->name }}
                                </option>
                                @endforeach
                            </select>
                            @error('partner_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="currency_id" class="form-label">Currency <span class="text-danger">*</span></label>
                            <select class="form-select @error('currency_id') is-invalid @enderror"
                                id="currency_id"
                                name="currency_id"
                                required>
                                <option value="">Zgjidh Currency</option>
                                @foreach($currencies as $currency)
                                <option value="{{ $currency->id }}" {{ old('currency_id') == $currency->id ? 'selected' : '' }}>
                                    {{ $currency->code }} ({{ $currency->symbol }})
                                </option>
                                @endforeach
                            </select>
                            @error('currency_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Products Section -->
                    <hr class="my-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Produktet</h5>
                        <button type="button" class="btn btn-primary btn-sm" id="addProductBtn">
                            <i class="ri-add-line me-1"></i> Shto Produkt
                        </button>
                    </div>

                    <select id="searchProduct" style="width: 100%"></select>


                    <div id="productsContainer">
                        <!-- Products will be added here dynamically -->
                    </div>

                    <!-- Note -->
                    <div class="mt-4">
                        <label for="notes" class="form-label">Shënime</label>
                        <textarea class="form-control"
                            id="notes"
                            name="notes"
                            rows="3"
                            placeholder="Shto shënime...">{{ old('notes') }}</textarea>
                    </div>

                    <!-- Attachment -->
                    <div class="mt-3">
                        <label for="attachment" class="form-label">Attachment</label>
                        <input type="file"
                            class="form-control @error('attachment') is-invalid @enderror"
                            id="attachment"
                            name="attachment"
                            accept=".pdf,.jpg,.jpeg,.png">
                        <small class="text-muted">PDF, JPG, PNG (Max: 2MB)</small>
                        @error('attachment')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">Përmbledhje</h5>

                    <div class="summary-box">
                        <div class="summary-row">
                            <span>Sub Total:</span>
                            <span id="subtotalDisplay">0.00</span>
                        </div>
                        <div class="summary-row">
                            <span>Tax:</span>
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
                            <i class="ri-save-line me-1"></i> Ruaj Blerjen
                        </button>
                        <a href="{{ route('purchases.index') }}" class="btn btn-secondary">
                            <i class="ri-close-line me-1"></i> Anulo
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@include('purchases.partials.pdf-import')

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    let productIndex = 0;

    $(document).ready(function() {

        $('#searchProduct').select2({
            placeholder: 'Search product...',
            minimumInputLength: 3,
            ajax: {
                url: '/purchases-api/search-products',
                dataType: 'json',
                delay: 300,
                data: function(params) {
                    return {
                        q: params.term,
                    };
                },
                processResults: function(data) {
                    showProductResults(data);
                    return {
                        results: data
                    };
                },
                cache: true
            }
        });

        $(document).on('input', '.unit-cost-input, .discount-input, .tax-input', function() {
            updateItemTotal($(this).closest('.product-item'));
            calculateTotals();
        });

        $(document).on('input', '.quantity-input', function() {

            const item = $(this).closest('.product-item');

            updateItemTotal(item);
            calculateTotals();

            const quantity = parseInt($(this).val()) || 0;
            const needsImei = item.data('needs-imei');

            if (needsImei) {
                item.find('.required-count').text(quantity);
                validateImeiForItem(item);
            }

        });

        $(document).on('input', '.imei-input', function() {
            const item = $(this).closest('.product-item');
            validateImeiForItem(item);
        });

        $(document).on('click', '.remove-item', function() {
            $(this).closest('.product-item').remove();
            calculateTotals();
        });
    });

    function showProductResults(results) {

        $('#searchProduct').next('.list-group').remove();
        let html = '<div class="list-group mb-3">';
        results.forEach(product => {
            let details = product.name;
            if (product.storage) details += ` - ${product.storage}`;
            if (product.ram) details += ` | ${product.ram}`;
            if (product.color) details += ` | ${product.color}`;

            html += `
                <a href="#" class="list-group-item list-group-item-action select-product" 
                   data-product='${JSON.stringify(product)}'>
                    <div class="d-flex justify-content-between">
                        <span>${details}</span>
                        <span class="badge bg-primary">${product.price} ${product.currency ? product.currency.symbol : ''}</span>
                    </div>
                </a>
            `;
        });
        html += '</div>';

        $('#searchProduct').after(html);

        $('.select-product').on('click', function(e) {
            e.preventDefault();
            const product = JSON.parse($(this).attr('data-product'));
            addProductItem(product);
            $(this).closest('.list-group').remove();
            $('#searchProduct').val('');
        });
    }

    function addProductItem(product) {
        productIndex++;

        let details = '';
        if (product.storage) details += product.storage;
        if (product.ram) details += (details ? ' | ' : '') + product.ram;
        if (product.color) details += (details ? ' | ' : '') + product.color;

        const needsImei = product.storage || product.ram || product.color;

        console.log(needsImei, product)

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
                    <input type="number" 
                        class="form-control form-control-sm quantity-input" 
                        name="items[${productIndex}][quantity]" 
                        value="1" 
                        min="1" 
                        required>
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
                    <label class="form-label small">Unit Cost *</label>
                    <input type="number" 
                        class="form-control form-control-sm unit-cost-input" 
                        name="items[${productIndex}][unit_cost]" 
                        value="${product.price}" 
                        step="0.01" 
                        min="0" 
                        required>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label small">Discount</label>
                    <input type="number" 
                        class="form-control form-control-sm discount-input" 
                        name="items[${productIndex}][discount]" 
                        value="0" 
                        step="0.01" 
                        min="0">
                </div>
                
                <div class="col-md-3">
                    <label class="form-label small">Tax</label>
                    <input type="number" 
                        class="form-control form-control-sm tax-input" 
                        name="items[${productIndex}][tax]" 
                        value="0" 
                        step="0.01" 
                        min="0">
                </div>
                
                <div class="col-md-3">
                    <label class="form-label small">Line Total</label>
                    <input type="text" 
                        class="form-control form-control-sm line-total" 
                        value="0.00" 
                        readonly>
                </div>
                
                ${needsImei ? `
                <div class="col-md-12 imei-container mt-2">
                    <label class="form-label small">
                        IMEI <span class="text-danger">*</span> 
                        <small class="text-muted">(Vendos ${product.storage || 'telefon'} - Ndaj me presje, p.sh: 123456789012345, 987654321098765)</small>
                    </label>
                    <textarea class="form-control form-control-sm imei-input" 
                        name="items[${productIndex}][imei_numbers]" 
                        rows="2"
                        placeholder="Vendos IMEI të ndara me presje (15 shifra secili)..."
                        required></textarea>
                    <div class="d-flex justify-content-between mt-1">
                        <small class="imei-count text-info">
                            IMEI të vendosur: <span class="current-count">0</span> / Kërkohen: <span class="required-count">1</span>
                        </small>
                        <small class="imei-validation text-muted"></small>
                    </div>
                </div>
                ` : ''}
            </div>
        </div>
    `;

        $('#productsContainer').append(html);
        updateItemTotal($(`[data-index="${productIndex}"]`));
        calculateTotals();
    }

    function updateItemTotal(item) {
        const qty = parseFloat(item.find('.quantity-input').val()) || 0;
        const cost = parseFloat(item.find('.unit-cost-input').val()) || 0;
        const discount = parseFloat(item.find('.discount-input').val()) || 0;
        const tax = parseFloat(item.find('.tax-input').val()) || 0;

        const total = (qty * cost) - discount + tax;
        item.find('.line-total').val(total.toFixed(2));
    }


    function validateImeiForItem(item) {
        const imeiInput = item.find('.imei-input');
        const imeiText = imeiInput.val();
        const quantity = parseInt(item.find('.quantity-input').val()) || 0;

        // Split and clean IMEI
        const imeiArray = imeiText.split(',').map(s => s.trim()).filter(s => s.length > 0);
        const imeiCount = imeiArray.length;

        item.find('.current-count').text(imeiCount);

        imeiInput.removeClass('is-invalid is-valid');
        let validationMessage = '';
        let isValid = true;

        if (imeiCount === 0) {
            validationMessage = '';
            isValid = false;
        } else if (imeiCount !== quantity) {
            validationMessage = `Duhen ${quantity} IMEI, keni ${imeiCount}`;
            imeiInput.addClass('is-invalid');
            isValid = false;
        } else {
            const uniqueImei = [...new Set(imeiArray)];
            if (uniqueImei.length !== imeiArray.length) {
                validationMessage = 'Ka IMEI të dubluar!';
                imeiInput.addClass('is-invalid');
                isValid = false;
            } else {
                let formatErrors = [];
                imeiArray.forEach((imei, index) => {
                    if (!/^\d{15}$/.test(imei)) {
                        formatErrors.push(`IMEI #${index + 1}: "${imei}"`);
                    }
                });

                if (formatErrors.length > 0) {
                    validationMessage = 'IMEI jo-valid (duhet 15 shifra): ' + formatErrors.join(', ');
                    imeiInput.addClass('is-invalid');
                    isValid = false;
                } else {
                    validationMessage = '✓ Të gjitha IMEI janë valide';
                    imeiInput.addClass('is-valid');
                }
            }
        }

        item.find('.imei-validation').html(validationMessage).toggleClass('text-danger', !isValid).toggleClass('text-success', isValid);
    }

    function calculateTotals() {
        let subtotal = 0;
        let totalTax = 0;
        let totalDiscount = 0;

        $('.product-item').each(function() {
            const qty = parseFloat($(this).find('.quantity-input').val()) || 0;
            const cost = parseFloat($(this).find('.unit-cost-input').val()) || 0;
            const discount = parseFloat($(this).find('.discount-input').val()) || 0;
            const tax = parseFloat($(this).find('.tax-input').val()) || 0;

            subtotal += (qty * cost);
            totalTax += tax;
            totalDiscount += discount;
        });

        const totalAmount = subtotal - totalDiscount + totalTax;

        $('#subtotalDisplay').text(subtotal.toFixed(2));
        $('#taxDisplay').text(totalTax.toFixed(2));
        $('#discountDisplay').text(totalDiscount.toFixed(2));
        $('#totalDisplay').text(totalAmount.toFixed(2));
    }

    $('#purchaseForm').on('submit', function(e) {
        e.preventDefault();

        var form = $(this);

        // Show loading Swal immediately
        Swal.fire({
            title: 'Processing...',
            text: 'Please wait',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: form.attr('action'),
            type: form.attr('method') || 'POST',
            data: form.serialize(),

            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: response.message || 'Operation completed successfully',
                    confirmButtonText: 'OK'
                }).then(() => {
                    if (response.url) {
                        window.location.href = response.url;
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
                    errorsHtml = 'An unexpected error occurred.';
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Gabim në validim!',
                    html: errorsHtml,
                    width: '600px'
                });
            }
        });
    });
</script>
@include('purchases.partials.pdf-import-scripts')
@endpush