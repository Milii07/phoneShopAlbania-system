@extends('layouts.app')

@section('title', 'Create Invoice')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    body {
        font-family: Arial, sans-serif;
    }

    .btn-open,
    .btn-print {
        padding: 10px 20px;
        cursor: pointer;
        background: #000;
        color: #fff;
        border: none;
    }

    .modal {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.6);
        z-index: 1000;
    }

    .modal-content {
        background: #fff;
        width: 90%;
        max-width: 900px;
        margin: 30px auto;
        padding: 20px;
        position: relative;
    }

    .close {
        position: absolute;
        top: 10px;
        right: 15px;
        font-size: 26px;
        cursor: pointer;
    }

    .a4-paper {
        width: 210mm;
        min-height: 297mm;
        padding: 25mm;
        margin: auto;
        background: white;
        color: #000;
    }

    .title {
        text-align: center;
        letter-spacing: 2px;
    }

    .section p {
        margin: 4px 0;
    }

    .note {
        margin-top: 20px;
    }

    .thanks {
        text-align: center;
        margin-top: 30px;
        font-weight: bold;
    }

    /* PRINT */
    @media print {
        body * {
            visibility: hidden;
        }

        #printArea,
        #printArea * {
            visibility: visible;
        }

        #printArea {
            position: absolute;
            left: 0;
            top: 0;
        }
    }
</style>

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

    /* Warranty Modal Styles */
    .warranty-modal {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.6);
        overflow-y: auto;
    }

    .warranty-modal-content {
        background-color: #fefefe;
        margin: 3% auto;
        padding: 0;
        border-radius: 15px;
        width: 90%;
        max-width: 700px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        animation: slideDown 0.3s ease;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-50px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .warranty-modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 25px 30px;
        border-radius: 15px 15px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .warranty-modal-header h3 {
        margin: 0;
        font-size: 22px;
        font-weight: 600;
    }

    .warranty-close {
        color: white;
        font-size: 32px;
        font-weight: bold;
        cursor: pointer;
        background: none;
        border: none;
        padding: 0;
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: background 0.3s;
    }

    .warranty-close:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    .warranty-modal-body {
        padding: 30px;
    }

    .warranty-section {
        margin-bottom: 25px;
    }

    .warranty-section h5 {
        color: #667eea;
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #f0f0f0;
    }

    .warranty-checkbox-group {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        border: 2px solid #dee2e6;
        transition: all 0.3s;
    }

    .warranty-checkbox-group.active {
        background: #e8f5e9;
        border-color: #4caf50;
    }

    .warranty-checkbox-group label {
        display: flex;
        align-items: center;
        cursor: pointer;
        margin: 0;
        font-weight: 500;
    }

    .warranty-checkbox-group input[type="checkbox"] {
        width: 22px;
        height: 22px;
        margin-right: 12px;
        cursor: pointer;
    }

    .warranty-details {
        display: none;
        margin-top: 20px;
        padding: 20px;
        background: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #dee2e6;
    }

    .warranty-details.show {
        display: block;
    }

    .warranty-btn {
        background: #667eea;
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        width: 100%;
        font-size: 16px;
    }

    .warranty-btn:hover {
        background: #5568d3;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }

    .warranty-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        margin-left: 10px;
    }

    .warranty-badge.has-warranty {
        background: #e8f5e9;
        color: #2e7d32;
    }

    .warranty-badge.no-warranty {
        background: #fff3e0;
        color: #e65100;
    }

    .add-warranty-btn {
        background: #4caf50;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        margin-left: 10px;
    }

    .add-warranty-btn:hover {
        background: #45a049;
        transform: scale(1.05);
    }

    .product-item.has-warranty {
        border-left: 4px solid #4caf50;
    }

    .warranty-info-box {
        background: #e8f5e9;
        border: 1px solid #4caf50;
        border-radius: 6px;
        padding: 10px 15px;
        margin-top: 10px;
        font-size: 13px;
    }

    .warranty-info-box strong {
        color: #2e7d32;
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
                            <input type="date" class="form-control" name="invoice_date" id="invoice_date" value="{{ date('Y-m-d') }}" required>
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
                            <select class="form-select select2-client" name="partner_id" id="partner_id" required>
                                <option value="">Choose...</option>
                                @foreach($partners as $partner)
                                <option value="{{ $partner->id }}" data-name="{{ $partner->name }}" data-address="{{ $partner->address ?? '' }}" data-phone="{{ $partner->phone ?? '' }}">{{ $partner->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Seller <span class="text-danger">*</span></label>
                            <select class="form-select select2-seller" name="seller_id" required>
                                <option value="">Choose...</option>
                                @foreach($sellers as $seller)
                                <option value="{{ $seller->id }}">{{ $seller->name }}</option>
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
                                <option value="Due on Receipt">Due on Receipt</option>
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

<!-- Warranty Modal -->
<div id="warrantyModal" class="warranty-modal">
    <div class="warranty-modal-content">
        <div class="warranty-modal-header">
            <h3>🛡️ Garancia e Produktit</h3>
            <button class="warranty-close" onclick="closeWarrantyModal()">&times;</button>
        </div>
        <div class="warranty-modal-body">
            <input type="hidden" id="current_product_index">

            <!-- Product Info Display -->
            <div class="warranty-section">
                <h5>Informacioni i Produktit</h5>
                <div class="alert alert-info mb-0">
                    <strong>Produkti:</strong> <span id="warranty_product_name"></span><br>
                    <strong>Klient:</strong> <span id="warranty_client_name"></span><br>
                    <strong>Data e Blerjes:</strong> <span id="warranty_purchase_date"></span><br>
                    <strong>IMEI:</strong> <span id="warranty_imei"></span>
                </div>
            </div>

            <!-- Warranty Status -->
            <div class="warranty-section">
                <h5>Statusi i Garancisë</h5>
                <div class="warranty-checkbox-group" id="warranty_checkbox_group">
                    <label>
                        <input type="checkbox" id="has_warranty" onchange="toggleWarrantyDetails()">
                        Produkti ka garanci
                    </label>
                </div>

                <div class="warranty-details" id="warranty_details">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Afati i Garancisë <span class="text-danger">*</span></label>
                            <select class="form-select" id="warranty_period" onchange="calculateWarrantyExpiry()">
                                <option value="12" selected>12 Muaj (Standard)</option>
                                <option value="6">6 Muaj</option>
                                <option value="3">3 Muaj</option>
                                <option value="24">24 Muaj</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Data e Skadimit</label>
                            <input type="date" class="form-control" id="warranty_expiry" readonly style="background-color: #f0f0f0;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Statusi i Produktit <span class="text-danger">*</span></label>
                            <select class="form-select" id="product_new_status">
                                <option value="i_ri">I Ri</option>
                                <option value="i_perdorur">I Përdorur</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Gjendja</label>
                            <select class="form-select" id="product_condition_warranty">
                                <option value="10/10">10/10 - Perfekt</option>
                                <option value="9/10">9/10 - Shumë i Mirë</option>
                                <option value="8/10">8/10 - I Mirë</option>
                                <option value="7/10">7/10 - I Përdorur</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Kushte Shtesë të Garancisë</label>
                            <textarea class="form-control" id="warranty_notes" rows="3" placeholder="Shkruani kushte shtesë nëse ka (opsionale)"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="warranty-section">
                <button type="button" class="warranty-btn" onclick="openWarrantyModalPDF()">
                    <i class="ri-save-line me-2"></i> Ruaj Garancisë
                </button>
            </div>
        </div>
    </div>
</div>
<div id="warrantyModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeWarrantyModal()">&times;</span>

        <!-- A4 PAPER -->
        <div id="printArea" class="a4-paper">

            <h1 class="title">PHONE SHOP ALBANIA</h1>

            <div class="section">
                <p><strong>KLIENTI:</strong> MANJOLA MEMA</p>
                <p><strong>ADRESA:</strong> ZOGU ZI, TIRANË, SHQIPËRI</p>
                <p><strong>NR I DYQANIT:</strong> 0696403876</p>
                <p><strong>INSTAGRAM:</strong> phone_shop.albania</p>
            </div>

            <h3>TË DHËNAT PËR PRODUKTIT</h3>
            <div class="section">
                <p><strong>GARANCIA:</strong> 36 MUAJ</p>
                <p><strong>DATA E BLERJES:</strong> 27/01/2026</p>
                <p><strong>ÇMIMI:</strong> 128,000 LEKË</p>
                <p><strong>MODELI:</strong> iPhone 17 Pro Max White 256GB</p>
                <p><strong>IMEI:</strong> 354856650669218</p>
                <p><strong>GJENDJA:</strong> I RI NË KUTI</p>
            </div>

            <h3>KUSHTET E GARANCISË</h3>
            <p>
                Phone Shop Albania garanton që produkti është pa defekte fabrikimi
                në momentin e blerjes. Garancia mbulon vetëm defektet e brendshme
                që nuk janë shkak i përdorimit nga klienti.
            </p>

            <ul>
                <li>Nëse pajisja hapet ose riparohet nga servis jo i autorizuar, garancia anulohet.</li>
                <li>Defektet e fabrikimit duhet të raportohen brenda 7 ditëve.</li>
                <li>Garancia nuk mbulon dëmtime nga uji, goditjet, pluhuri ose temperaturat ekstreme.</li>
            </ul>

            <h3>PJESËT DHE DEFEKTET QË NUK MBULOHEN NGA GARANCIA</h3>
            <p>
                Ekrani, bateria, porta e karikimit, kamera, dëmtime fizike, riparime të paautorizuara,
                softuer i modifikuar, përdorim i gabuar ose mbingarkesë e baterisë.
            </p>

            <h3>PËRFUNDIMI</h3>
            <p>
                Phone Shop Albania angazhohet të ofrojë shërbim cilësor.
                Për asistencë teknike na kontaktoni në telefon ose Instagram.
            </p>

            <p class="note"><strong>Nuk bëhet kthim pagese mbrapsht.</strong></p>

            <p class="thanks">
                Ju falenderojmë që keni zgjedhur Phone Shop Albania!
            </p>

        </div>

        <button onclick="printDocument()" class="btn-print">
            Download / Print A4
        </button>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="{{ asset('assets/js/pages/select2.init.js') }}"></script>
<script>
    let productIndex = 0;
    let warrantyData = {};

    function openWarrantyModalPDF() {
        document.getElementById('warrantyModal').style.display = 'block';
    }

    function closeWarrantyModal() {
        document.getElementById('warrantyModal').style.display = 'none';
    }

    function printDocument() {
        window.print();
    }

    $(document).ready(function() {
        $('.select2-client').select2();
        $('.select2-seller').select2();

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
            const index = $(this).closest('.product-item').data('index');
            delete warrantyData[index];
            $(this).closest('.product-item').remove();
            calculateTotals();
        });

        $(document).on('input', '.quantity-input', function() {
            const item = $(this).closest('.product-item');
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
    });

    function addProductItem(product) {
        productIndex++;
        let details = '';
        if (product.storage) details += product.storage;
        if (product.ram) details += (details ? ' | ' : '') + product.ram;
        if (product.color) details += (details ? ' | ' : '') + product.color;
        const needsImei = product.storage || product.ram || product.color;

        const html = `
    <div class="product-item" data-index="${productIndex}" data-needs-imei="${needsImei}" data-product-name="${product.name}" data-product-details="${details}">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h6 class="mb-0">
                    ${product.name}
                    <span class="warranty-badge-container" id="warranty_badge_${productIndex}"></span>
                </h6>
                ${details ? `<small class="text-muted">${details}</small><br>` : ''}
                <small class="text-info">Stock: ${product.quantity}</small>
            </div>
            <div>
                <button type="button" class="add-warranty-btn" onclick="openWarrantyModal(${productIndex})">
                    <i class="ri-shield-check-line"></i> Garanci
                </button>
                <button type="button" class="btn btn-sm btn-danger remove-item ms-2">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </div>
        </div>
        <input type="hidden" name="items[${productIndex}][product_id]" value="${product.id}">
        
        <!-- Hidden warranty fields -->
        <input type="hidden" name="items[${productIndex}][has_warranty]" id="warranty_has_${productIndex}" value="0">
        <input type="hidden" name="items[${productIndex}][warranty_period]" id="warranty_period_${productIndex}">
        <input type="hidden" name="items[${productIndex}][warranty_expiry]" id="warranty_expiry_${productIndex}">
        <input type="hidden" name="items[${productIndex}][warranty_notes]" id="warranty_notes_${productIndex}">
        <input type="hidden" name="items[${productIndex}][product_status]" id="product_status_${productIndex}">
        <input type="hidden" name="items[${productIndex}][product_condition]" id="product_condition_${productIndex}">
        
        <div class="row g-2">
            <div class="col-md-3">
                <label class="form-label small">Qty *</label>
                <input type="number" class="form-control form-control-sm quantity-input" 
                    name="items[${productIndex}][quantity]" 
                    value="1" 
                    min="1" 
                    max="${product.quantity}" 
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
                <label class="form-label small">Unit Price *</label>
                <input type="number" 
                    class="form-control form-control-sm unit-price-input" 
                    name="items[${productIndex}][unit_price]" 
                    value="" 
                    step="0.01" 
                    min="0" 
                    placeholder="0.00"
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
                    <small class="text-muted">(Vendos ${details || 'telefon'} - Ndaj me presje, 15 shifra secili)</small>
                </label>
                <textarea class="form-control form-control-sm imei-input" 
                    name="items[${productIndex}][imei_numbers]" 
                    id="imei_${productIndex}"
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
        <div class="warranty-info-container" id="warranty_info_${productIndex}"></div>
    </div>`;

        $('#productsContainer').append(html);
        updateItemTotal($(`[data-index="${productIndex}"]`));
        calculateTotals();
    }

    function openWarrantyModal(index) {
        $('#current_product_index').val(index);

        // Get product info
        const productItem = $(`.product-item[data-index="${index}"]`);
        const productName = productItem.data('product-name');
        const productDetails = productItem.data('product-details');

        // Get client info
        const selectedClient = $('#partner_id option:selected');
        const clientName = selectedClient.data('name') || selectedClient.text();

        // Get purchase date
        const purchaseDate = $('#invoice_date').val();

        // Get IMEI
        const imeiValue = $(`#imei_${index}`).val() || 'N/A';

        // Populate modal
        $('#warranty_product_name').text(productName + (productDetails ? ' - ' + productDetails : ''));
        $('#warranty_client_name').text(clientName);
        $('#warranty_purchase_date').text(formatDateDisplay(purchaseDate));
        $('#warranty_imei').text(imeiValue);

        // Load existing warranty data if any
        if (warrantyData[index]) {
            $('#has_warranty').prop('checked', warrantyData[index].has_warranty);
            $('#warranty_period').val(warrantyData[index].warranty_period || '12');
            $('#warranty_notes').val(warrantyData[index].warranty_notes || '');
            $('#product_new_status').val(warrantyData[index].product_status || 'i_ri');
            $('#product_condition_warranty').val(warrantyData[index].product_condition || '10/10');

            if (warrantyData[index].has_warranty) {
                $('#warranty_checkbox_group').addClass('active');
                $('#warranty_details').addClass('show');
            }
        } else {
            $('#has_warranty').prop('checked', false);
            $('#warranty_period').val('12');
            $('#warranty_notes').val('');
            $('#product_new_status').val('i_ri');
            $('#product_condition_warranty').val('10/10');
            $('#warranty_checkbox_group').removeClass('active');
            $('#warranty_details').removeClass('show');
        }

        calculateWarrantyExpiry();
        $('#warrantyModal').show();
        $('body').css('overflow', 'hidden');
    }

    function closeWarrantyModal() {
        $('#warrantyModal').hide();
        $('body').css('overflow', 'auto');
    }

    function toggleWarrantyDetails() {
        const hasWarranty = $('#has_warranty').is(':checked');
        if (hasWarranty) {
            $('#warranty_checkbox_group').addClass('active');
            $('#warranty_details').addClass('show');
            calculateWarrantyExpiry();
        } else {
            $('#warranty_checkbox_group').removeClass('active');
            $('#warranty_details').removeClass('show');
        }
    }

    function calculateWarrantyExpiry() {
        const purchaseDate = $('#invoice_date').val();
        const warrantyPeriod = parseInt($('#warranty_period').val());

        if (purchaseDate && warrantyPeriod) {
            const expiryDate = new Date(purchaseDate);
            expiryDate.setMonth(expiryDate.getMonth() + warrantyPeriod);
            $('#warranty_expiry').val(expiryDate.toISOString().split('T')[0]);
        }
    }

    function saveWarranty() {
        const index = $('#current_product_index').val();
        const hasWarranty = $('#has_warranty').is(':checked');

        warrantyData[index] = {
            has_warranty: hasWarranty,
            warranty_period: $('#warranty_period').val(),
            warranty_expiry: $('#warranty_expiry').val(),
            warranty_notes: $('#warranty_notes').val(),
            product_status: $('#product_new_status').val(),
            product_condition: $('#product_condition_warranty').val()
        };

        // Update hidden fields
        $(`#warranty_has_${index}`).val(hasWarranty ? '1' : '0');
        $(`#warranty_period_${index}`).val(hasWarranty ? warrantyData[index].warranty_period : '');
        $(`#warranty_expiry_${index}`).val(hasWarranty ? warrantyData[index].warranty_expiry : '');
        $(`#warranty_notes_${index}`).val(hasWarranty ? warrantyData[index].warranty_notes : '');
        $(`#product_status_${index}`).val(warrantyData[index].product_status);
        $(`#product_condition_${index}`).val(warrantyData[index].product_condition);

        // Update badge
        updateWarrantyBadge(index, hasWarranty);

        // Update product item styling
        const productItem = $(`.product-item[data-index="${index}"]`);
        if (hasWarranty) {
            productItem.addClass('has-warranty');

            // Show warranty info box
            const warrantyInfo = `
                <div class="warranty-info-box">
                    <strong>🛡️ Garanci:</strong> ${warrantyData[index].warranty_period} muaj 
                    (deri më ${formatDateDisplay(warrantyData[index].warranty_expiry)}) | 
                    <strong>Statusi:</strong> ${warrantyData[index].product_status === 'i_ri' ? 'I Ri' : 'I Përdorur'} | 
                    <strong>Gjendja:</strong> ${warrantyData[index].product_condition}
                </div>
            `;
            $(`#warranty_info_${index}`).html(warrantyInfo);
        } else {
            productItem.removeClass('has-warranty');
            $(`#warranty_info_${index}`).html('');
        }

        closeWarrantyModal();

        Swal.fire({
            icon: 'success',
            title: 'Sukses!',
            text: 'Garancia u ruajt me sukses',
            timer: 1500,
            showConfirmButton: false
        });
    }

    function updateWarrantyBadge(index, hasWarranty) {
        const badgeHtml = hasWarranty ?
            '<span class="warranty-badge has-warranty">✓ Ka Garanci</span>' :
            '<span class="warranty-badge no-warranty">Nuk ka Garanci</span>';
        $(`#warranty_badge_${index}`).html(badgeHtml);
    }

    function formatDateDisplay(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('sq-AL', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    }

    function validateImeiForItem(item) {
        const imeiInput = item.find('.imei-input');
        const imeiText = imeiInput.val();
        const quantity = parseInt(item.find('.quantity-input').val()) || 0;

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

        item.find('.imei-validation').html(validationMessage)
            .toggleClass('text-danger', !isValid)
            .toggleClass('text-success', isValid);
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

    // Close modal when clicking outside
    $(window).on('click', function(event) {
        if (event.target.id === 'warrantyModal') {
            closeWarrantyModal();
        }
    });

    $('#saleForm').on('submit', function(e) {
        e.preventDefault();

        let hasError = false;
        let errorMessages = [];

        if ($('.product-item').length === 0) {
            Swal.fire({
                icon: 'error',
                title: 'Gabim!',
                text: 'Duhet të shtoni të paktën një produkt!'
            });
            return false;
        }

        $('.product-item').each(function(index) {
            const item = $(this);
            const needsImei = item.data('needs-imei');
            const productName = item.find('h6').text();

            if (needsImei) {
                const imeiInput = item.find('.imei-input');
                const imeiText = imeiInput.val().trim();
                const quantity = parseInt(item.find('.quantity-input').val()) || 0;

                if (!imeiText) {
                    hasError = true;
                    errorMessages.push(`${productName}: IMEI është i detyrueshëm`);
                    imeiInput.addClass('is-invalid');
                    return;
                }

                const imeiArray = imeiText.split(',').map(s => s.trim()).filter(s => s.length > 0);
                const imeiCount = imeiArray.length;

                if (imeiCount !== quantity) {
                    hasError = true;
                    errorMessages.push(`${productName}: Kërkohen ${quantity} IMEI, por keni vendosur ${imeiCount}`);
                    imeiInput.addClass('is-invalid');
                    return;
                }

                const uniqueImei = [...new Set(imeiArray)];
                if (uniqueImei.length !== imeiArray.length) {
                    hasError = true;
                    errorMessages.push(`${productName}: Ka IMEI të dubluar`);
                    imeiInput.addClass('is-invalid');
                    return;
                }

                for (let i = 0; i < imeiArray.length; i++) {
                    const imei = imeiArray[i];
                    if (!/^\d{15}$/.test(imei)) {
                        hasError = true;
                        errorMessages.push(`${productName}: IMEI "${imei}" nuk është valid (duhet 15 shifra)`);
                        imeiInput.addClass('is-invalid');
                        return;
                    }
                }
            }
        });

        if (hasError) {
            Swal.fire({
                icon: 'error',
                title: 'Gabim në validim!',
                html: errorMessages.join('<br>'),
                width: '600px'
            });
            return false;
        }

        Swal.fire({
            title: 'Duke procesuar...',
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
                    text: response.message || 'Fatura u krijua me sukses'
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
                    errorsHtml = 'Ndodhi një gabim i papritur.';
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