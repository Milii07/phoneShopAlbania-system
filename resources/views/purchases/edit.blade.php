@extends('layouts.app')

@section('title', 'Modifiko Blerjen')

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
            <h4 class="mb-sm-0">Modifiko Blerjen</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('purchases.index') }}">Blerjet</a></li>
                    <li class="breadcrumb-item active">Modifiko</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('purchases.update', $purchase->id) }}" enctype="multipart/form-data" id="purchaseForm">
    @csrf
    @method('PUT')

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
                                value="{{ old('purchase_date', $purchase->purchase_date->format('Y-m-d')) }}"
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
                                value="{{ old('due_date', $purchase->due_date ? $purchase->due_date->format('Y-m-d') : '') }}">
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
                                <option value="{{ $warehouse->id }}" {{ old('warehouse_id', $purchase->warehouse_id) == $warehouse->id ? 'selected' : '' }}>
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
                                <option value="Received" {{ old('order_status', $purchase->order_status) == 'Received' ? 'selected' : '' }}>Received</option>
                                <option value="Pending" {{ old('order_status', $purchase->order_status) == 'Pending' ? 'selected' : '' }}>Pending</option>
                                <option value="Cancelled" {{ old('order_status', $purchase->order_status) == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
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
                                <option value="Unpaid" {{ old('payment_status', $purchase->payment_status) == 'Unpaid' ? 'selected' : '' }}>Unpaid</option>
                                <option value="Paid" {{ old('payment_status', $purchase->payment_status) == 'Paid' ? 'selected' : '' }}>Paid</option>
                                <option value="Partial" {{ old('payment_status', $purchase->payment_status) == 'Partial' ? 'selected' : '' }}>Partial</option>
                            </select>
                            @error('payment_status')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="payment_cash" value="Cash" {{ old('payment_method', $purchase->payment_method) == 'Cash' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="payment_cash">
                                        Cash
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="payment_bank" value="Bank" {{ old('payment_method', $purchase->payment_method) == 'Bank' ? 'checked' : '' }}>
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
                                <option value="{{ $partner->id }}" {{ old('partner_id', $purchase->partner_id) == $partner->id ? 'selected' : '' }}>
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
                                <option value="{{ $currency->id }}" {{ old('currency_id', $purchase->currency_id) == $currency->id ? 'selected' : '' }}>
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

                    <div class="mb-3">
                        <input type="text"
                            class="form-control"
                            id="searchProduct"
                            placeholder="Kërko produkt...">
                    </div>

                    <div id="productsContainer">
                        @foreach($purchase->items as $index => $item)
                        <div class="product-item" data-index="{{ $index }}">
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
                                    <input type="number"
                                        class="form-control form-control-sm quantity-input"
                                        name="items[{{ $index }}][quantity]"
                                        value="{{ $item->quantity }}"
                                        min="1"
                                        required>
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
                                    <label class="form-label small">Unit Cost *</label>
                                    <input type="number"
                                        class="form-control form-control-sm unit-cost-input"
                                        name="items[{{ $index }}][unit_cost]"
                                        value="{{ $item->unit_cost }}"
                                        step="0.01"
                                        min="0"
                                        required>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label small">Sales Price *</label>
                                    <input type="number"
                                        class="form-control form-control-sm unit-cost-input"
                                        name="items[{{ $index }}][selling_price]"
                                        value="{{ $item->selling_price }}"
                                        step="0.01"
                                        min="0"
                                        required>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label small">Discount</label>
                                    <input type="number"
                                        class="form-control form-control-sm discount-input"
                                        name="items[{{ $index }}][discount]"
                                        value="{{ $item->discount }}"
                                        step="0.01"
                                        min="0">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label small">Tax</label>
                                    <input type="number"
                                        class="form-control form-control-sm tax-input"
                                        name="items[{{ $index }}][tax]"
                                        value="{{ $item->tax }}"
                                        step="0.01"
                                        min="0">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label small">Line Total</label>
                                    <input type="text"
                                        class="form-control form-control-sm line-total"
                                        value="{{ number_format($item->line_total, 2) }}"
                                        readonly>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Note -->
                    <div class="mt-4">
                        <label for="notes" class="form-label">Shënime</label>
                        <textarea class="form-control"
                            id="notes"
                            name="notes"
                            rows="3"
                            placeholder="Shto shënime...">{{ old('notes', $purchase->notes) }}</textarea>
                    </div>

                    <!-- Attachment -->
                    <div class="mt-3">
                        <label for="attachment" class="form-label">Attachment</label>
                        @if($purchase->attachment)
                        <div class="mb-2">
                            <a href="{{ Storage::url($purchase->attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="ri-file-line"></i> Attachment ekzistues
                            </a>
                        </div>
                        @endif
                        <input type="file"
                            class="form-control @error('attachment') is-invalid @enderror"
                            id="attachment"
                            name="attachment"
                            accept=".pdf,.jpg,.jpeg,.png">
                        <small class="text-muted">PDF, JPG, PNG (Max: 2MB) - Lëre bosh për të mbajtur attachment-in ekzistues</small>
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
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="ri-save-line me-1"></i> Përditëso Blerjen
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

@endsection

@push('scripts')
<script>
    let productIndex = 0;

    $(document).ready(function() {
        calculateTotals();

        let searchTimeout;
        $('#searchProduct').on('input', function() {
            clearTimeout(searchTimeout);
            const search = $(this).val();

            searchTimeout = setTimeout(() => {
                if (search.length >= 2) {
                    searchProducts(search);
                }
            }, 300);
        });

        $('#addProductBtn').on('click', function() {
            $('#searchProduct').focus();
        });

        $(document).on('input', '.quantity-input, .unit-cost-input, .discount-input, .tax-input', function() {
            updateItemTotal($(this).closest('.product-item'));
            calculateTotals();
        });

        $(document).on('click', '.remove-item', function() {
            $(this).closest('.product-item').remove();
            calculateTotals();
        });
    });

    function searchProducts(search) {
        const results = products.filter(p =>
            p.name.toLowerCase().includes(search.toLowerCase()) ||
            (p.storage && p.storage.toLowerCase().includes(search.toLowerCase())) ||
            (p.color && p.color.toLowerCase().includes(search.toLowerCase()))
        );

        if (results.length > 0) {
            showProductResults(results);
        }
    }

    function showProductResults(results) {
        $('.list-group').remove();

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

        const html = `
            <div class="product-item" data-index="${productIndex}">
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
</script>
@endpush