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
        font-size: 14px;
        font-weight: bold;
        border-radius: 4px;
    }

    .btn-print:hover {
        background: #333;
    }

    .modal {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.6);
        z-index: 1000;
        overflow-y: auto;
    }

    .modal-content {
        background: #fff;
        width: 90%;
        max-width: 780px;
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
        z-index: 10;
    }

    /* ══════════════════════════════════════════
       CERTIFIKATË GARANCIE  –  A4 print area
    ══════════════════════════════════════════ */
    .a4-paper {
        width: 210mm;
        min-height: 297mm;
        padding: 14mm 16mm 16mm;
        margin: auto;
        background: #fff;
        color: #000;
        font-family: Arial, sans-serif;
        font-size: 10pt;
        line-height: 1.45;
        box-sizing: border-box;
    }

    /* ── Header ── */
    .cert-shop-name {
        text-align: center;
        font-family: 'Arial Black', Arial, sans-serif;
        font-size: 18pt;
        font-weight: 900;
        letter-spacing: 2px;
        text-transform: uppercase;
        margin: 0 0 2px;
    }
    .cert-title {
        text-align: center;
        font-size: 13pt;
        font-weight: 700;
        letter-spacing: 2px;
        text-transform: uppercase;
        margin: 0 0 2px;
    }
    .cert-number {
        text-align: center;
        font-size: 9.5pt;
        color: #333;
        margin: 0 0 14px;
    }

    /* ── Section header bar ── */
    .cert-section-title {
        font-size: 9.5pt;
        font-weight: 700;
        text-transform: uppercase;
        border: 1px solid #000;
        border-bottom: none;
        padding: 5px 8px;
        background: #fff;
        margin-top: 14px;
        letter-spacing: 0.5px;
    }

    /* ── Data table ── */
    .cert-table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #000;
        margin-bottom: 0;
    }
    .cert-table td {
        border: 1px solid #000;
        padding: 6px 10px;
        font-size: 9.5pt;
        vertical-align: middle;
    }
    .cert-table td:first-child {
        font-weight: 700;
        width: 38%;
        background: #fff;
    }
    .cert-table tr.highlight-row td {
        background: #1a1a1a;
        color: #fff;
        font-weight: 700;
    }

    /* ── Exclusions box ── */
    .cert-box {
        border: 1px solid #000;
        padding: 8px 12px;
        margin-top: 14px;
    }
    .cert-box-title {
        font-size: 9.5pt;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 6px;
    }
    .cert-box ul {
        margin: 0;
        padding: 0;
        list-style: none;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2px 16px;
    }
    .cert-box ul li {
        font-size: 9pt;
        line-height: 1.4;
    }
    .cert-box ul li::before { content: "- "; }

    /* ── Conditions box ── */
    .cert-conditions {
        border: 1px solid #000;
        padding: 8px 12px;
        margin-top: 14px;
    }
    .cert-conditions-title {
        font-size: 9.5pt;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 6px;
    }
    .cert-conditions ul {
        margin: 0;
        padding: 0;
        list-style: none;
    }
    .cert-conditions ul li {
        font-size: 9pt;
        line-height: 1.5;
    }
    .cert-conditions ul li::before { content: "- "; }

    /* ── Signature footer ── */
    .cert-signatures {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        margin-top: 30px;
        gap: 16px;
    }
    .cert-sig-block {
        flex: 1;
        text-align: center;
    }
    .cert-sig-block .sig-label {
        font-size: 9pt;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 28px;
        letter-spacing: 1px;
    }
    .cert-sig-block .sig-line {
        border-top: 1px dashed #555;
        padding-top: 4px;
        font-size: 8pt;
        color: #555;
    }
    .cert-sig-stamp {
        flex: 0 0 120px;
        text-align: center;
    }
    .cert-sig-stamp .stamp-box {
        border: 1px dashed #555;
        height: 70px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 9pt;
        color: #555;
        margin-bottom: 4px;
    }
    .cert-sig-stamp .stamp-label {
        font-size: 8.5pt;
        font-weight: 700;
        text-transform: uppercase;
    }

    /* ── Product items ── */
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

    /* ── Summary box ── */
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

    /* ── IMEI search box ── */
    .imei-search-box {
        background: #e8f4fd;
        border: 2px solid #3498db;
        border-radius: 8px;
        padding: 12px 16px;
        margin-bottom: 10px;
    }

    .imei-search-box .input-group input {
        border-right: 0;
    }

    .imei-search-box .input-group-text {
        background: #3498db;
        color: #fff;
        border-color: #3498db;
        font-weight: 600;
    }

    .imei-search-box .btn-imei-search {
        background: #3498db;
        color: #fff;
        border-color: #3498db;
        font-weight: 600;
    }

    .imei-search-box .btn-imei-search:hover {
        background: #2176ae;
        border-color: #2176ae;
    }

    #imeiSearchStatus {
        font-size: 13px;
        margin-top: 6px;
        min-height: 20px;
    }

    /* ── Search tabs ── */
    .search-tabs {
        margin-bottom: 10px;
    }

    .search-tabs .nav-link {
        color: #495057;
        border-radius: 6px 6px 0 0;
        font-weight: 500;
    }

    .search-tabs .nav-link.active {
        color: #fff;
        background-color: #3498db;
        border-color: #3498db;
    }

    /* ── Warranty modal ── */
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

    .modal {
        z-index: 1060 !important;
    }

    .modal-backdrop {
        z-index: 1050 !important;
    }

    .modal-backdrop.show {
        opacity: 0.5;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Krijo Shitje</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('sales.index') }}">Shitjet</a></li>
                    <li class="breadcrumb-item active">Krijo Shitje</li>
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
                            <label class="form-label">Data e Blerjes</label>
                            <input type="date" class="form-control" name="delivery_date" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Dyqani <span class="text-danger">*</span></label>
                            <select class="form-select" name="warehouse_id" id="warehouse_id" required>
                                <option value="">Depot</option>
                                @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}"
                                    data-address="{{ $warehouse->address ?? '' }}"
                                    data-instagram="{{ $warehouse->instagram ?? '' }}">
                                    {{ $warehouse->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Statusi i Pagesës <span class="text-danger">*</span></label>
                            <select class="form-select" name="payment_status" required>
                                <option value="Unpaid">Pa Pagesë</option>
                                <option value="Paid" selected>Me Pagesë</option>
                                <option value="Partial">Pjesërisht i Paguar</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Statusi i Shitjes <span class="text-danger">*</span></label>
                            <select class="form-select" name="sale_status" required>
                                <option value="Confirmed">Konfirmuar</option>
                                <option value="Draft">Draft</option>
                                <option value="PrePaid">Parapaguar</option>
                                <option value="Rejected">Refuzuar</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Metoda e Pagesës <span class="text-danger">*</span></label>
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
                                <div class="col-md-6">
                                    <label class="form-label">Vendi i Blerjes <span class="text-danger">*</span></label>
                                    <div class="d-flex gap-3 mt-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="purchase_location" id="shop" value="shop" checked>
                                            <label class="form-check-label" for="shop">Dyqan</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="purchase_location" id="online" value="online">
                                            <label class="form-check-label" for="online">Online</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-5">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <label class="form-label mb-0">Klient <span class="text-danger">*</span></label>
                                <button class="btn btn-sm btn-outline-primary rounded-pill px-3" type="button" data-bs-toggle="modal" data-bs-target="#createClientModal">
                                    <i class="ri-user-add-line me-1"></i> Klient i Ri
                                </button>
                            </div>
                            <select class="form-select select2-client" name="partner_id" id="partner_id" required>
                                <option value="">Zgjidh Klientin...</option>
                                @foreach($partners as $partner)
                                <option value="{{ $partner->id }}"
                                    data-name="{{ $partner->name }}"
                                    data-address="{{ $partner->address ?? '' }}"
                                    data-phone="{{ $partner->phone ?? '' }}">
                                    {{ $partner->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Shitesi <span class="text-danger">*</span></label>
                            <select class="form-select select2-seller" name="seller_id" required>
                                <option value="">Zgjidh Shitesin...</option>
                                @foreach($sellers as $seller)
                                <option value="{{ $seller->id }}">{{ $seller->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Monedha <span class="text-danger">*</span></label>
                            <select class="form-select" name="currency_id" id="currency_id" required>
                                <option value="">Zgjidh Monedhën</option>
                                @foreach($currencies as $currency)
                                <option value="{{ $currency->id }}" data-symbol="{{ $currency->symbol }}">
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

                    {{-- ════════════════════════════════════════════════════
                         SEARCH TABS  –  by Name  OR  by IMEI
                    ════════════════════════════════════════════════════ --}}
                    <ul class="nav nav-tabs search-tabs" id="searchTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab-name" data-bs-toggle="tab"
                                data-bs-target="#pane-name" type="button" role="tab">
                                <i class="ri-search-line me-1"></i> Kërko me Emër
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-imei" data-bs-toggle="tab"
                                data-bs-target="#pane-imei" type="button" role="tab">
                                <i class="ri-barcode-line me-1"></i> Kërko me IMEI
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content border border-top-0 rounded-bottom p-3 mb-3">
                        {{-- ── Tab 1: Search by product name (original) ── --}}
                        <div class="tab-pane fade show active" id="pane-name" role="tabpanel">
                            <select id="searchProduct" style="width:100%" placeholder="Kerko Prduktin..."></select>
                        </div>

                        {{-- ── Tab 2: Search by IMEI ── --}}
                        <div class="tab-pane fade" id="pane-imei" role="tabpanel">
                            <div class="imei-search-box">
                                <label class="form-label fw-semibold mb-2">
                                    <i class="ri-barcode-line me-1"></i> Shkruani numrin IMEI (15 shifra)
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="ri-cpu-line"></i></span>
                                    <input type="text"
                                        id="imeiSearchInput"
                                        class="form-control"
                                        placeholder="p.sh. 123456789012345"
                                        maxlength="15"
                                        inputmode="numeric"
                                        pattern="\d{15}">
                                    <button type="button" class="btn btn-imei-search" id="btnImeiSearch">
                                        <i class="ri-search-line me-1"></i> Kërko
                                    </button>
                                </div>
                                <div id="imeiSearchStatus"></div>
                            </div>
                        </div>
                    </div>
                    {{-- ════════════════════════════════════════════════════ --}}

                    <div id="productsContainer" class="mt-3"></div>

                    <div class="row mt-4">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Payment Term</label>
                            <select class="form-select" name="payment_term">
                                <option value="Due on Receipt" selected>NE momentin e pranimit</option>
                                <option value="Net 15">pas 15</option>
                                <option value="Net 30">pas 30</option>
                                <option value="Net 45">pas 45</option>
                                <option value="Net 60">pas 60</option>
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
                            <span id="subtotalDisplay"><span class="currency-symbol">L</span> 0.00</span>
                        </div>
                        <div class="summary-row">
                            <span>Estimated Tax:</span>
                            <span id="taxDisplay"><span class="currency-symbol">L</span> 0.00</span>
                        </div>
                        <div class="summary-row">
                            <span>Discount:</span>
                            <span id="discountDisplay"><span class="currency-symbol">L</span> 0.00</span>
                        </div>
                        <div class="summary-row">
                            <span>Total Amount:</span>
                            <span id="totalDisplay"><span class="currency-symbol">L</span> 0.00</span>
                        </div>
                    </div>
                    <div class="mt-4 d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="ri-save-line me-1"></i> Rregjistro Shitjen
                        </button>
                        <a href="{{ route('sales.index') }}" class="btn btn-secondary">
                            <i class="ri-close-line me-1"></i> Anullo
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
                        <!-- Create Client Modal -->
                        <div class="modal fade" id="createClientModal" tabindex="-1" aria-labelledby="createClientModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header bg-primary">
                                        <h5 class="modal-title text-white" id="createClientModalLabel">
                                            <i class="ri-add-line align-middle me-1"></i> Shto Klient të Ri
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form method="POST" action="{{ route('partners.store') }}" id="createClientForm">
                                        @csrf
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label for="client_name" class="form-label">Emri <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="client_name" name="name" placeholder="Shkruani emrin e klientit">
                                            </div>
                                            <div class="mb-3">
                                                <label for="client_phone" class="form-label">Nr. Telefoni <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="client_phone" name="phone" placeholder="+355 69 123 4567">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Anulo</button>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="ri-save-line align-middle me-1"></i> Ruaj Klientin
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

<!-- WARRANTY FORM MODAL -->
<div id="warrantyModal" class="warranty-modal">
    <div class="warranty-modal-content">
        <div class="warranty-modal-header">
            <h3>🛡️ Garancia e Produktit</h3>
            <button class="warranty-close" onclick="closeWarrantyModal()">&times;</button>
        </div>
        <div class="warranty-modal-body">
            <input type="hidden" id="current_product_index">
            <div class="warranty-section">
                <h5>Informacioni i Produktit</h5>
                <div class="alert alert-info mb-0">
                    <strong>Produkti:</strong> <span id="warranty_product_name"></span><br>
                    <strong>Klient:</strong> <span id="warranty_client_name"></span><br>
                    <strong>Data e Blerjes:</strong> <span id="warranty_purchase_date"></span><br>
                    <strong>IMEI:</strong> <span id="warranty_imei"></span>
                </div>
            </div>
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
                                <option value="36">36 Muaj</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Data e Skadimit</label>
                            <input type="date" class="form-control" id="warranty_expiry" readonly style="background-color:#f0f0f0;">
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
                                <option value="I RI NË KUTI">I RI NË KUTI</option>
                                <option value="10/10">10/10 - Perfekt</option>
                                <option value="9/10">9/10 - Shumë i Mirë</option>
                                <option value="8/10">8/10 - I Mirë</option>
                                <option value="7/10">7/10 - I Përdorur</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Kushte Shtesë të Garancisë</label>
                            <textarea class="form-control" id="warranty_notes" rows="3" placeholder="Kushte shtesë (opsionale)"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="warranty-section">
                <button type="button" class="warranty-btn" onclick="printWarrantyNow()">
                    <i class="ri-printer-line me-2"></i> Print Garancia
                </button>
            </div>
        </div>
    </div>
</div>

<div id="warrantyPrintModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeWarrantyPrintModal()">&times;</span>

        <div id="printArea" class="a4-paper">

            {{-- ═══════ HEADER ═══════ --}}
            <p class="cert-shop-name">PHONE SHOP ALBANIA</p>
            <p class="cert-title">Certifikatë Garancie</p>
            <p class="cert-number">Nr. <span id="print_cert_number"></span> / <span id="print_purchase_date"></span></p>

            {{-- ═══════ DEVICE & BUYER INFO TABLE ═══════ --}}
            <div class="cert-section-title">TË DHËNAT E PAJISJES DHE BLERËSIT</div>
            <table class="cert-table">
                <tr>
                    <td>Emri i Blerësit</td>
                    <td id="print_client_name">—</td>
                </tr>
                <tr>
                    <td>Modeli i Pajisjes</td>
                    <td id="print_model">—</td>
                </tr>
                <tr>
                    <td>IMEI / Nr. Serial</td>
                    <td id="print_imei">—</td>
                </tr>
                <tr>
                    <td>Data e Blerjes</td>
                    <td id="print_purchase_date_table">—</td>
                </tr>
                <tr>
                    <td>Çmimi i Blerjes</td>
                    <td><span id="print_price">—</span> <span id="print_currency">LEK</span></td>
                </tr>
                <tr class="highlight-row">
                    <td>Afati i Garancisë</td>
                    <td><span id="print_warranty_period">—</span> MUAJ</td>
                </tr>
            </table>

            {{-- ═══════ EXCLUSIONS ═══════ --}}
            <div class="cert-box" style="margin-top:14px;">
                <div class="cert-box-title">Përjashtimet nga Garancia</div>
                <ul>
                    <li>Dëmtime fizike (rënie, goditje, thyerje)</li>
                    <li>Aksesorë jo origjinalë</li>
                    <li>Ekspozim ndaj lëngjeve ose lagështisë</li>
                    <li>Probleme softuerike / instalime jozyrtare</li>
                    <li>Konsum normal (bateri, aksesorë)</li>
                    <li>Dëmtime nga rrymë, zjarr, fatkeqësi natyrore</li>
                    <li>Përdorim i gabuar ose neglizhencë</li>
                    <li>Heqje/dëmtim i numrit serial</li>
                    <li>Riparime nga servise të paautorizuara</li>
                </ul>
            </div>

            {{-- ═══════ CONDITIONS ═══════ --}}
            <div class="cert-conditions">
                <div class="cert-conditions-title">Kushtet dhe Informacione të Rëndësishme</div>
                <ul>
                    <li>Dokumenti është i vlefshëm vetëm me vulë dhe nënshkrim të dyqanit.</li>
                    <li>Garancioni lidhet vetëm me IMEI/Serial të pajisjes.</li>
                    <li>Afati mesatar i servisimit: 7–21 ditë pune.</li>
                    <li>Ruajeni këtë dokument për çdo pretendim garancie.</li>
                </ul>
            </div>

            {{-- ═══════ SIGNATURES ═══════ --}}
            <div class="cert-signatures">
                <div class="cert-sig-block">
                    <div class="sig-label">Shitësi</div>
                    <div class="sig-line">Emri dhe Nënshkrimi</div>
                </div>
                <div class="cert-sig-stamp">
                    <div class="stamp-label" style="margin-bottom:6px;">Vula</div>
                    <div class="stamp-box">Vula e<br>Dyqanit</div>
                </div>
                <div class="cert-sig-block">
                    <div class="sig-label">Blerësi</div>
                    <div class="sig-line">Emri dhe Nënshkrimi</div>
                </div>
            </div>

        </div><!-- /printArea -->

        <div style="text-align:center; margin-top:15px; padding-bottom:15px; display:flex; gap:12px; justify-content:center;">
            <button onclick="printWarrantyCertificate()" class="btn-print"><i class="ri-printer-line me-1"></i> Print</button>
            <button onclick="generatePDF()" class="btn-print">Download PDF</button>
        </div>
    </div>
</div>
{{-- ════════════════════════════════════════════════════════════
     TRANSFER TO WAREHOUSE MODAL
════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="transferWarehouseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title fw-bold">
                    <i class="ri-arrow-left-right-line me-2"></i> Transfer Product to Warehouse
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">
                    <i class="ri-information-line me-1"></i>
                    Duke transferuar: <strong id="transferProductName">—</strong>
                </p>
                {{-- Warehouse List --}}
                <div>
                    <label class="form-label fw-semibold">Zgjidh Dyqanin e Destinacionit <span class="text-danger">*</span></label>
                    <div id="transferWarehouseList" class="row g-2">
                        @foreach($warehouses as $wh)
                        <div class="col-md-6">
                            <label class="d-flex align-items-center gap-2 border rounded p-3 transfer-wh-label"
                                   style="cursor:pointer; transition: background .15s;">
                                <input type="checkbox"
                                       class="transfer-wh-checkbox form-check-input flex-shrink-0"
                                       value="{{ $wh->id }}"
                                       data-name="{{ $wh->name }}"
                                       style="width:20px;height:20px;">
                                <span class="fw-medium">{{ $wh->name }}</span>
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Anulo</button>
                <button type="button" class="btn btn-warning px-4" id="confirmTransferBtn" onclick="confirmTransfer()">
                    <i class="ri-check-line me-1"></i> Konfirmo Transferin
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let productIndex = 0;
    let warrantyData = {};
    let currentCurrencySymbol = 'L';

    // ── Currency symbol update ──────────────────────────────────────────────
    $('#currency_id').on('change', function() {
        const symbol = $(this).find('option:selected').data('symbol') || 'L';
        currentCurrencySymbol = symbol;
        $('.currency-symbol').text(symbol);
    });

    // ═══════════════════════════════════════════════════════════════════════
    //  IMEI SEARCH  –  Tab 2
    // ═══════════════════════════════════════════════════════════════════════
    function setImeiStatus(msg, type) {
        // type: 'info' | 'success' | 'danger' | 'warning'
        const colors = {
            info: '#0d6efd',
            success: '#198754',
            danger: '#dc3545',
            warning: '#fd7e14'
        };
        $('#imeiSearchStatus').html(
            `<span style="color:${colors[type]||'#495057'}">` +
            (type === 'danger' ? '<i class="ri-error-warning-line me-1"></i>' : '<i class="ri-information-line me-1"></i>') +
            msg + `</span>`
        );
    }

    function doImeiSearch() {
        const imei = $('#imeiSearchInput').val().trim();
        const warehouseId = $('#warehouse_id').val();

        if (!imei) {
            setImeiStatus('Ju lutem shkruani numrin IMEI.', 'warning');
            return;
        }
        if (!/^\d{15}$/.test(imei)) {
            setImeiStatus('IMEI duhet të jetë saktësisht 15 shifra numerike.', 'danger');
            return;
        }

        setImeiStatus('Duke kërkuar...', 'info');
        $('#btnImeiSearch').prop('disabled', true);

        $.ajax({
            url: '/sales-api/search-by-imei',
            method: 'GET',
            data: {
                imei: imei,
                warehouse_id: warehouseId || ''
            },
            success: function(product) {
                setImeiStatus(
                    `✓ U gjet: <strong>${product.name}</strong>` +
                    (product.storage ? ` | ${product.storage}` : '') +
                    (product.ram ? ` | ${product.ram}` : '') +
                    (product.color ? ` | ${product.color}` : '') +
                    ` — Stok: ${product.quantity}`,
                    'success'
                );

                // Add product row and pre-fill IMEI
                addProductItem(product, product.found_imei);

                // Clear input and switch focus
                $('#imeiSearchInput').val('');
            },
            error: function(xhr) {
                const msg = xhr.responseJSON && xhr.responseJSON.error ?
                    xhr.responseJSON.error :
                    'Gabim gjatë kërkimit.';
                setImeiStatus(msg, 'danger');
            },
            complete: function() {
                $('#btnImeiSearch').prop('disabled', false);
            }
        });
    }

    // Button click
    $('#btnImeiSearch').on('click', doImeiSearch);

    // Enter key inside input
    $('#imeiSearchInput').on('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            doImeiSearch();
        }
    });

    // Auto-search when exactly 15 digits are typed
    $('#imeiSearchInput').on('input', function() {
        const val = $(this).val().replace(/\D/g, '');
        $(this).val(val); // strip non-digits
        if (val.length === 15) {
            doImeiSearch();
        }
    });

    // ═══════════════════════════════════════════════════════════════════════
    //  WARRANTY MODAL
    // ═══════════════════════════════════════════════════════════════════════
    function openWarrantyModal(index) {
        $('#current_product_index').val(index);

        const productItem = $(`.product-item[data-index="${index}"]`);
        const productName = productItem.data('product-name');
        const productDetails = productItem.data('product-details');
        const selectedClient = $('#partner_id option:selected');
        const clientName = selectedClient.data('name') || selectedClient.text();
        const purchaseDate = $('#invoice_date').val();
        const imeiValue = $(`#imei_${index}`).val() || 'N/A';

        $('#warranty_product_name').text(productName + (productDetails ? ' - ' + productDetails : ''));
        $('#warranty_client_name').text(clientName);
        $('#warranty_purchase_date').text(formatDateDisplay(purchaseDate));
        $('#warranty_imei').text(imeiValue);

        if (warrantyData[index]) {
            $('#has_warranty').prop('checked', warrantyData[index].has_warranty);
            $('#warranty_period').val(warrantyData[index].warranty_period || '12');
            $('#warranty_notes').val(warrantyData[index].warranty_notes || '');
            $('#product_new_status').val(warrantyData[index].product_status || 'i_ri');
            $('#product_condition_warranty').val(warrantyData[index].product_condition || 'I RI NË KUTI');
            if (warrantyData[index].has_warranty) {
                $('#warranty_checkbox_group').addClass('active');
                $('#warranty_details').addClass('show');
            }
        } else {
            $('#has_warranty').prop('checked', false);
            $('#warranty_period').val('12');
            $('#warranty_notes').val('');
            $('#product_new_status').val('i_ri');
            $('#product_condition_warranty').val('I RI NË KUTI');
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

    function printWarrantyNow() {
        const index = $('#current_product_index').val();
        const hasWarranty = $('#has_warranty').is(':checked');

        if (!hasWarranty) {
            Swal.fire({
                icon: 'warning',
                title: 'Kërkohet garanci',
                text: 'Ju lutem zgjidhni "Produkti ka garanci" para se të printoni.'
            });
            return;
        }

        warrantyData[index] = {
            has_warranty: hasWarranty,
            warranty_period: $('#warranty_period').val(),
            warranty_expiry: $('#warranty_expiry').val(),
            warranty_notes: $('#warranty_notes').val(),
            product_status: $('#product_new_status').val(),
            product_condition: $('#product_condition_warranty').val()
        };

        $(`#warranty_has_${index}`).val(hasWarranty ? '1' : '0');
        $(`#warranty_period_${index}`).val(warrantyData[index].warranty_period);
        $(`#warranty_expiry_${index}`).val(warrantyData[index].warranty_expiry);
        $(`#warranty_notes_${index}`).val(warrantyData[index].warranty_notes);
        $(`#product_status_${index}`).val(warrantyData[index].product_status);
        $(`#product_condition_${index}`).val(warrantyData[index].product_condition);

        updateWarrantyBadge(index, hasWarranty);

        const productItem = $(`.product-item[data-index="${index}"]`);
        productItem.addClass('has-warranty');
        $(`#warranty_info_${index}`).html(`
            <div class="warranty-info-box">
                <strong>🛡️ Garanci:</strong> ${warrantyData[index].warranty_period} muaj 
                (deri më ${formatDateDisplay(warrantyData[index].warranty_expiry)}) | 
                <strong>Statusi:</strong> ${warrantyData[index].product_status === 'i_ri' ? 'I Ri' : 'I Përdorur'} | 
                <strong>Gjendja:</strong> ${warrantyData[index].product_condition}
            </div>`);

        closeWarrantyModal();
        openWarrantyPrintModal(index);
    }

    function updateWarrantyBadge(index, hasWarranty) {
        $(`#warranty_badge_${index}`).html(hasWarranty ?
            '<span class="warranty-badge has-warranty">✓ Ka Garanci</span>' :
            '<span class="warranty-badge no-warranty">Nuk ka Garanci</span>');
    }

    function openWarrantyPrintModal(index) {
        const productItem = $(`.product-item[data-index="${index}"]`);
        const productName = productItem.data('product-name');
        const productDetails = productItem.data('product-details');
        const fullModel = productName + (productDetails ? ' ' + productDetails : '');
        const selectedClient = $('#partner_id option:selected');
        const clientName = selectedClient.data('name') || selectedClient.text() || 'Klient i panjohur';
        const selectedCurrency = $('#currency_id option:selected');
        const currencySymbol = selectedCurrency.data('symbol') || 'LEK';
        const purchaseDate = $('#invoice_date').val();
        const unitPrice = productItem.find('.unit-price-input').val() || '0';
        const imeiRaw = $(`#imei_${index}`).val() || '';
        const imeiFirst = imeiRaw.split(',').map(s => s.trim()).filter(s => s.length > 0)[0] || 'N/A';
        const wd = warrantyData[index] || {};
        const warrantyPeriod = wd.warranty_period || '12';

        // Auto-generate certificate number: random 8-digit + date digits
        const certNum = Math.floor(10000000 + Math.random() * 90000000);

        // Format purchase date for display (e.g. "May 12, 2026")
        const displayDate = formatDateDisplay(purchaseDate);

        // Populate all certificate fields
        $('#print_cert_number').text(certNum);
        $('#print_purchase_date').text(displayDate);
        $('#print_purchase_date_table').text(displayDate);
        $('#print_client_name').text(clientName || 'Klient i panjohur');
        $('#print_model').text(fullModel);
        $('#print_imei').text(imeiFirst);
        $('#print_price').text(Number(unitPrice).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        $('#print_currency').text(currencySymbol.toUpperCase());
        $('#print_warranty_period').text(warrantyPeriod);

        $('#warrantyPrintModal').show();
        $('body').css('overflow', 'hidden');
    }

    function closeWarrantyPrintModal() {
        $('#warrantyPrintModal').hide();
        $('body').css('overflow', 'auto');
    }

    function printWarrantyCertificate() {
        const content = document.getElementById('printArea').innerHTML;
        const win = window.open('', '_blank', 'width=900,height=1200');
        win.document.write(`<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Certifikatë Garancie</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Arial,sans-serif;font-size:10pt;line-height:1.45;color:#000;background:#fff}
.a4-paper{width:210mm;min-height:297mm;padding:14mm 16mm 16mm;margin:auto;background:#fff;color:#000;font-family:Arial,sans-serif;font-size:10pt;line-height:1.45}
.cert-shop-name{text-align:center;font-family:'Arial Black',Arial,sans-serif;font-size:18pt;font-weight:900;letter-spacing:2px;text-transform:uppercase;margin:0 0 2px}
.cert-title{text-align:center;font-size:13pt;font-weight:700;letter-spacing:2px;text-transform:uppercase;margin:0 0 2px}
.cert-number{text-align:center;font-size:9.5pt;color:#333;margin:0 0 14px}
.cert-section-title{font-size:9.5pt;font-weight:700;text-transform:uppercase;border:1px solid #000;border-bottom:none;padding:5px 8px;background:#fff;margin-top:14px;letter-spacing:.5px}
.cert-table{width:100%;border-collapse:collapse;border:1px solid #000;margin-bottom:0}
.cert-table td{border:1px solid #000;padding:6px 10px;font-size:9.5pt;vertical-align:middle}
.cert-table td:first-child{font-weight:700;width:38%;background:#fff}
.cert-table tr.highlight-row td{background:#1a1a1a;color:#fff;font-weight:700}
.cert-box{border:1px solid #000;padding:8px 12px;margin-top:14px}
.cert-box-title{font-size:9.5pt;font-weight:700;text-transform:uppercase;margin-bottom:6px}
.cert-box ul{margin:0;padding:0;list-style:none;display:grid;grid-template-columns:1fr 1fr;gap:2px 16px}
.cert-box ul li{font-size:9pt;line-height:1.4}
.cert-box ul li::before{content:"- "}
.cert-conditions{border:1px solid #000;padding:8px 12px;margin-top:14px}
.cert-conditions-title{font-size:9.5pt;font-weight:700;text-transform:uppercase;margin-bottom:6px}
.cert-conditions ul{margin:0;padding:0;list-style:none}
.cert-conditions ul li{font-size:9pt;line-height:1.5}
.cert-conditions ul li::before{content:"- "}
.cert-signatures{display:flex;justify-content:space-between;align-items:flex-end;margin-top:30px;gap:16px}
.cert-sig-block{flex:1;text-align:center}
.cert-sig-block .sig-label{font-size:9pt;font-weight:700;text-transform:uppercase;margin-bottom:28px;letter-spacing:1px}
.cert-sig-block .sig-line{border-top:1px dashed #555;padding-top:4px;font-size:8pt;color:#555}
.cert-sig-stamp{flex:0 0 120px;text-align:center}
.cert-sig-stamp .stamp-box{border:1px dashed #555;height:70px;display:flex;align-items:center;justify-content:center;font-size:9pt;color:#555;margin-bottom:4px}
.cert-sig-stamp .stamp-label{font-size:8.5pt;font-weight:700;text-transform:uppercase}
@media print{body{margin:0}@page{margin:0;size:A4 portrait}}
</style>
</head>
<body>${content}</body>
</html>`);
        win.document.close();
        win.focus();
        setTimeout(() => { win.print(); win.close(); }, 300);
    }

    async function generatePDF() {
        const printArea = document.getElementById('printArea');
        try {
            const canvas = await html2canvas(printArea, {
                scale: 2,
                useCORS: true,
                logging: false,
                backgroundColor: '#ffffff'
            });
            const imgData = canvas.toDataURL('image/png');
            const {
                jsPDF
            } = window.jspdf;
            const pdf = new jsPDF('p', 'mm', 'a4');
            pdf.addImage(imgData, 'PNG', 0, 0, pdf.internal.pageSize.getWidth(), pdf.internal.pageSize.getHeight());
            const clientName = $('#print_client_name').text() || 'Client';
            const date = new Date().toISOString().split('T')[0];
            pdf.save(`Garancia_${clientName}_${date}.pdf`);
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Gabim',
                text: 'Ka ndodhur një gabim gjatë gjenerimit të PDF.'
            });
        }
    }

    function formatDateDisplay(dateString) {
        if (!dateString) return 'N/A';
        const parts = dateString.split('-');
        return parts.length === 3 ? parts[2] + '/' + parts[1] + '/' + parts[0] : dateString;
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  DOCUMENT READY
    // ═══════════════════════════════════════════════════════════════════════
    $(document).ready(function() {
        // Select2 inits
        if (typeof $.fn.select2 !== 'undefined') {
            $('.select2-client').select2({
                placeholder: 'Zgjidh Klientin...',
                allowClear: true
            });
            $('.select2-seller').select2({
                placeholder: 'Zgjidh Shitesin...',
                allowClear: true
            });

            // Name-based product search
            $('#searchProduct').select2({
                placeholder: 'Kërko produktin...',
                minimumInputLength: 2,
                allowClear: true,
                ajax: {
                    url: '/sales-api/search-products',
                    dataType: 'json',
                    delay: 300,
                    data: function(params) {
                        return {
                            q: params.term,
                            warehouse_id: $('#warehouse_id').val() || ''
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
                addProductItem(e.params.data.product, null);
                $(this).val(null).trigger('change');
            });
        }

        // Live input handlers
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
            if (item.data('needs-imei')) {
                item.find('.required-count').text(quantity);
                validateImeiForItem(item);
            }
        });

        $(document).on('input', '.imei-input', function() {
            validateImeiForItem($(this).closest('.product-item'));
        });
    });

    // ═══════════════════════════════════════════════════════════════════════
    //  ADD PRODUCT ITEM  –  accepts optional prefilledImei
    // ═══════════════════════════════════════════════════════════════════════
    function addProductItem(product, prefilledImei) {
        productIndex++;
        let details = '';
        if (product.storage) details += product.storage;
        if (product.ram) details += (details ? ' | ' : '') + product.ram;
        if (product.color) details += (details ? ' | ' : '') + product.color;
        const needsImei = product.storage || product.ram || product.color;

        // Pre-filled IMEI value (from IMEI search or empty string)
        const imeiValue = prefilledImei || '';

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
            <button type="button" class="btn btn-sm btn-warning ms-2" onclick="openTransferModal(${product.id}, '${product.name}')">
                <i class="ri-arrow-left-right-line"></i> Transfer
            </button>
            <button type="button" class="btn btn-sm btn-danger remove-item ms-2">
                <i class="ri-delete-bin-line"></i>
            </button>
        </div>
    </div>
    <input type="hidden" name="items[${productIndex}][product_id]"    value="${product.id}">
    <input type="hidden" name="items[${productIndex}][has_warranty]"   id="warranty_has_${productIndex}"       value="0">
    <input type="hidden" name="items[${productIndex}][warranty_period]" id="warranty_period_${productIndex}">
    <input type="hidden" name="items[${productIndex}][warranty_expiry]" id="warranty_expiry_${productIndex}">
    <input type="hidden" name="items[${productIndex}][warranty_notes]"  id="warranty_notes_${productIndex}">
    <input type="hidden" name="items[${productIndex}][product_status]"  id="product_status_${productIndex}">
    <input type="hidden" name="items[${productIndex}][product_condition]" id="product_condition_${productIndex}">

    <div class="row g-2">
        <div class="col-md-3">
            <label class="form-label small">Qty *</label>
            <input type="number" class="form-control form-control-sm quantity-input"
                   name="items[${productIndex}][quantity]" id="items_${product.id}_quantity" value="1" min="1" max="${product.quantity}" required>
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
            <input type="number" class="form-control form-control-sm unit-price-input"
                   name="items[${productIndex}][unit_price]" value="${product.selling_price}" step="0.01" min="0" placeholder="0.00" required>
        </div>
        <div class="col-md-3">
            <label class="form-label small">Discount</label>
            <input type="number" class="form-control form-control-sm discount-input"
                   name="items[${productIndex}][discount]" value="0" step="0.01" min="0">
        </div>
        <div class="col-md-3">
            <label class="form-label small">Tax</label>
            <input type="number" class="form-control form-control-sm tax-input"
                   name="items[${productIndex}][tax]" value="0" step="0.01" min="0">
        </div>
        <div class="col-md-3">
            <label class="form-label small">Line Total</label>
            <input type="text" class="form-control form-control-sm line-total" value="0.00" readonly>
        </div>
        ${needsImei ? `
        <div class="col-md-12 imei-container mt-2">
            <label class="form-label small">
                IMEI <span class="text-danger">*</span>
                <small class="text-muted">(15 shifra, ndaj me presje nëse ka shumë)</small>
                ${prefilledImei ? '<span class="badge bg-success ms-2">✓ IMEI u ngarkua automatikisht</span>' : ''}
            </label>
            <textarea class="form-control form-control-sm imei-input"
                      name="items[${productIndex}][imei_numbers]"
                      id="imei_${productIndex}" rows="2"
                      placeholder="Vendos IMEI..." required>${imeiValue}</textarea>
            <div class="d-flex justify-content-between mt-1">
                <small class="imei-count text-info">IMEI: <span class="current-count">0</span> / <span class="required-count">1</span></small>
                <small class="imei-validation text-muted"></small>
            </div>
        </div>
        ` : ''}
    </div>
    <div class="warranty-info-container" id="warranty_info_${productIndex}"></div>
</div>`;

        $('#productsContainer').append(html);

        const newItem = $(`[data-index="${productIndex}"]`);

        // If IMEI was pre-filled, validate immediately
        if (prefilledImei && needsImei) {
            validateImeiForItem(newItem);
        }

        updateItemTotal(newItem);
        calculateTotals();
    }

    // ─── IMEI Validation ───────────────────────────────────────────────────
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
            validationMessage = `Duhen ${quantity} IMEI`;
            imeiInput.addClass('is-invalid');
            isValid = false;
        } else {
            const uniqueImei = [...new Set(imeiArray)];
            if (uniqueImei.length !== imeiArray.length) {
                validationMessage = 'IMEI të dubluar!';
                imeiInput.addClass('is-invalid');
                isValid = false;
            } else {
                let formatErrors = [];
                imeiArray.forEach((imei, i) => {
                    if (!/^\d{15}$/.test(imei)) formatErrors.push(`#${i + 1}`);
                });
                if (formatErrors.length > 0) {
                    validationMessage = 'IMEI jo-valid: ' + formatErrors.join(', ');
                    imeiInput.addClass('is-invalid');
                    isValid = false;
                } else {
                    validationMessage = '✓ Valide';
                    imeiInput.addClass('is-valid');
                }
            }
        }

        item.find('.imei-validation')
            .html(validationMessage)
            .toggleClass('text-danger', !isValid)
            .toggleClass('text-success', isValid);
    }

    // ─── Line total & summary ─────────────────────────────────────────────
    function updateItemTotal(item) {
        const qty = parseFloat(item.find('.quantity-input').val()) || 0;
        const price = parseFloat(item.find('.unit-price-input').val()) || 0;
        const discount = parseFloat(item.find('.discount-input').val()) || 0;
        const tax = parseFloat(item.find('.tax-input').val()) || 0;
        item.find('.line-total').val(((qty * price) - discount + tax).toFixed(2));
    }

    function calculateTotals() {
        let subtotal = 0,
            totalTax = 0,
            totalDiscount = 0;
        $('.product-item').each(function() {
            subtotal += (parseFloat($(this).find('.quantity-input').val()) || 0) * (parseFloat($(this).find('.unit-price-input').val()) || 0);
            totalTax += parseFloat($(this).find('.tax-input').val()) || 0;
            totalDiscount += parseFloat($(this).find('.discount-input').val()) || 0;
        });
        const totalAmount = subtotal - totalDiscount + totalTax;
        $('#subtotalDisplay').html(`<span class="currency-symbol">${currentCurrencySymbol}</span> ${subtotal.toFixed(2)}`);
        $('#taxDisplay').html(`<span class="currency-symbol">${currentCurrencySymbol}</span> ${totalTax.toFixed(2)}`);
        $('#discountDisplay').html(`<span class="currency-symbol">${currentCurrencySymbol}</span> ${totalDiscount.toFixed(2)}`);
        $('#totalDisplay').html(`<span class="currency-symbol">${currentCurrencySymbol}</span> ${totalAmount.toFixed(2)}`);
    }

    // ─── Close modals on backdrop click ───────────────────────────────────
    $(window).on('click', function(event) {
        if (event.target.id === 'warrantyModal') closeWarrantyModal();
        if (event.target.id === 'warrantyPrintModal') closeWarrantyPrintModal();
    });

    // ═══════════════════════════════════════════════════════════════════════
    //  TRANSFER TO WAREHOUSE
    // ═══════════════════════════════════════════════════════════════════════
    let selectedTransferProductId = null;
    let selectedTransferWarehouseId = null;

    function openTransferModal(productId, productName) {
        selectedTransferProductId = productId;
        selectedTransferWarehouseId = null;
        $('#transferProductName').text(productName || '—');
        $('.transfer-wh-checkbox').prop('checked', false);
        $('.transfer-wh-label').css('background', '');
        const modal = new bootstrap.Modal(document.getElementById('transferWarehouseModal'));
        modal.show();
    }

    // Enforce single-select on warehouse checkboxes
    $(document).on('change', '.transfer-wh-checkbox', function () {
        const checked = $(this).is(':checked');
        $('.transfer-wh-checkbox').prop('checked', false);
        $('.transfer-wh-label').css('background', '');
        if (checked) {
            $(this).prop('checked', true);
            $(this).closest('.transfer-wh-label').css('background', '#fff3cd');
            selectedTransferWarehouseId = $(this).val();
        } else {
            selectedTransferWarehouseId = null;
        }
    });

    function confirmTransfer() {
        if (!selectedTransferWarehouseId) {
            Swal.fire({ icon: 'warning', title: 'Dyqan mungon', text: 'Ju lutem zgjidhni dyqanin e destinacionit.' });
            return;
        }

        Swal.fire({
            title: 'Duke transferuar...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: '{{ route("sales.transfer-product-warehouse") }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: {
                product_id:   selectedTransferProductId,
                warehouse_id: selectedTransferWarehouseId,
                from_warehouse_id: $('#warehouse_id').val(),
                custom_qty: $('#items_' + selectedTransferProductId + '_quantity').val() || 1
            },
            success: function (res) {
                bootstrap.Modal.getInstance(document.getElementById('transferWarehouseModal')).hide();
                Swal.fire({ icon: 'success', title: 'Sukses!', text: res.message, timer: 2500, showConfirmButton: false });
            },
            error: function (xhr) {
                const msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gabim gjatë transferimit.';
                Swal.fire({ icon: 'error', title: 'Gabim!', text: msg });
            }
        });
    }

    // ─── Form submit ───────────────────────────────────────────────────────
    $('#saleForm').on('submit', function(e) {
        e.preventDefault();

        // Validim i fushave kryesore
        const warehouse = $('#warehouse_id').val();
        const partner = $('#partner_id').val();
        const seller = $('select[name="seller_id"]').val();
        const currency = $('#currency_id').val();
        const invoiceDate = $('#invoice_date').val();

        if (!warehouse) {
            Swal.fire({icon: 'error', title: 'Gabim', text: 'Zgjidhni dyqanin!'});
            return false;
        }
        if (!partner) {
            Swal.fire({icon: 'error', title: 'Gabim', text: 'Zgjidhni klientin!'});
            return false;
        }
        if (!seller) {
            Swal.fire({icon: 'error', title: 'Gabim', text: 'Zgjidhni shitesin!'});
            return false;
        }
        if (!currency) {
            Swal.fire({icon: 'error', title: 'Gabim', text: 'Zgjidhni monedhën!'});
            return false;
        }
        if (!invoiceDate) {
            Swal.fire({icon: 'error', title: 'Gabim', text: 'Vendosni datën!'});
            return false;
        }

        if ($('.product-item').length === 0) {
            Swal.fire({icon: 'error', title: 'Gabim', text: 'Duhet të shtoni të paktën një produkt!'});
            return false;
        }

        let hasError = false,
            errorMessages = [];

        $('.product-item').each(function() {
            const item = $(this);
            const needsImei = item.data('needs-imei');
            const productName = item.find('h6').text().trim();
            const quantity = parseInt(item.find('.quantity-input').val()) || 0;
            const price = parseFloat(item.find('.unit-price-input').val()) || 0;

            if (quantity <= 0) {
                hasError = true;
                errorMessages.push(`${productName}: Sasia duhet të jetë më e madhe se 0`);
                return;
            }

            if (price <= 0) {
                hasError = true;
                errorMessages.push(`${productName}: Çmimi duhet të jetë më i madh se 0`);
                return;
            }

            if (needsImei) {
                const imeiInput = item.find('.imei-input');
                const imeiText = imeiInput.val().trim();

                if (!imeiText) {
                    hasError = true;
                    errorMessages.push(`${productName}: IMEI mungon`);
                    return;
                }

                const imeiArray = imeiText.split(',').map(s => s.trim()).filter(s => s.length > 0);
                if (imeiArray.length !== quantity) {
                    hasError = true;
                    errorMessages.push(`${productName}: Kërkohen ${quantity} IMEI, por keni ${imeiArray.length}`);
                    return;
                }

                if ([...new Set(imeiArray)].length !== imeiArray.length) {
                    hasError = true;
                    errorMessages.push(`${productName}: Ka IMEI të dubluar!`);
                    return;
                }

                for (let i = 0; i < imeiArray.length; i++) {
                    if (!/^\d{15}$/.test(imeiArray[i])) {
                        hasError = true;
                        errorMessages.push(`${productName}: IMEI #${i + 1} (${imeiArray[i]}) jo-valid - duhet 15 shifra`);
                        return;
                    }
                }
            }
        });

        if (hasError) {
            Swal.fire({
                icon: 'error',
                title: 'Gabim në Validim',
                html: errorMessages.map(msg => `<p style="text-align:left;margin:5px 0">${msg}</p>`).join(''),
                confirmButtonText: 'Rregulloni Gabimin'
            });
            return false;
        }

        // Show loading
        Swal.fire({
            title: 'Duke ruajtur...',
            html: 'Ju lutem prisni, fatura po ruhet',
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
                    title: response.message || 'Fatura u krijua me sukses!',
                    text: 'Fatura po hapet...',
                    timer: 2000,
                    showConfirmButton: false
                }).then(function() {
                    if (response.url) {
                        window.location.href = response.url;
                    } else {
                        window.location.href = '{{ route("sales.index") }}';
                    }
                });
            },
            error: function(xhr) {
                let msg = 'Gabim gjatë ruajtes të faturës';
                let details = [];

                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    if (xhr.responseJSON.errors) {
                        details = Object.entries(xhr.responseJSON.errors).map(([key, errors]) => {
                            return `<strong>${key}:</strong> ${Array.isArray(errors) ? errors.join(', ') : errors}`;
                        });
                    }
                }

                let htmlContent = `<p>${msg}</p>`;
                if (details.length > 0) {
                    htmlContent += `<div style="text-align:left;margin-top:10px">${details.join('<br>')}</div>`;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Gabim!',
                    html: htmlContent,
                    confirmButtonText: 'Kthehu prapa'
                });
            }
        });
    });
</script>

<script>

    $(document).ready(function() {
        let activeBackdrops = 0;
        // Fix modal z-index
        $('#createClientModal').on('show.bs.modal', function() {
            activeBackdrops++;
            setTimeout(function() {
                const baseZIndex = 1070 + (activeBackdrops * 10);
                $('#createClientModal').css('z-index', baseZIndex);
                $('body').find('.modal-backdrop').last().css('z-index', baseZIndex - 10);
            }, 10);
        });

        $('#createClientModal').on('hidden.bs.modal', function() {
            activeBackdrops = Math.max(0, activeBackdrops - 1);
            // Cleanup backdrops
            const count = $('body').find('.modal-backdrop').length;
            if (count > 0) {
                $('body').find('.modal-backdrop').last().remove();
            }
        });

        // ═══════════════════════════════════════════════════════════════════
        // CLIENT CREATION
        // ═══════════════════════════════════════════════════════════════════
        let saveTimeout;
        let isCreatingClient = false;

        function saveClient() {
            if (isCreatingClient) return;

            const name = $('#client_name').val().trim();
            const phone = $('#client_phone').val().trim();

            if (!name || !phone) return;
            if (name.length < 2) return;
            if (phone.length < 6) return;

            isCreatingClient = true;
            const saveBtn = $('#createClientForm').find('button[type="submit"]');
            
            if (saveBtn.length === 0) {
                console.error('Save button nuk u gjet');
                isCreatingClient = false;
                return;
            }

            const originalText = saveBtn.html();
            saveBtn.html('<i class="ri-loader-4-line me-1" style="animation:spin 1s linear infinite"></i> Duke ruajtur...').prop('disabled', true);

            $.ajax({
                url: '{{ route("partners.store") }}',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    name: name,
                    phone: phone
                },
                timeout: 15000,
                success: function(response) {
                    if (response.success && response.partner) {
                        // Krijo option të ri
                        const newOption = new Option(response.partner.name, response.partner.id, true, true);
                        $(newOption).attr('data-name', response.partner.name);
                        $(newOption).attr('data-phone', response.partner.phone || '');
                        $(newOption).attr('data-address', '');

                        // Shto në select
                        const partnerSelect = $('#partner_id');
                        if (partnerSelect.length > 0) {
                            partnerSelect.append(newOption);
                            partnerSelect.val(response.partner.id).trigger('change');
                        }

                        // Rregjo forman
                        const form = $('#createClientForm');
                        if (form.length > 0 && form[0]) {
                            form[0].reset();
                        }

                        // Mbyll modalin
                        setTimeout(() => {
                            const modal = bootstrap.Modal.getInstance(document.getElementById('createClientModal'));
                            if (modal) {
                                modal.hide();
                            }
                            Swal.fire({
                                icon: 'success',
                                title: 'Sukses!',
                                text: `Klienti "${name}" u ruajt dhe u zgjodh automatikisht!`,
                                timer: 1500,
                                showConfirmButton: false
                            });
                        }, 100);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gabim!',
                            text: 'Përgjigja e serverit nuk ishte e vlefshme'
                        });
                    }
                },
                error: function(xhr) {
                    let errorMsg = 'Gabim gjatë ruajtes të klientit';

                    if (xhr.status === 0) {
                        errorMsg = 'Problemi i lidhjes me serverin';
                    } else if (xhr.statusText === 'timeout') {
                        errorMsg = 'Timeout - serveri nuk përgjigjet';
                    } else if (xhr.responseJSON) {
                        if (xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        if (xhr.responseJSON.errors) {
                            const errors = xhr.responseJSON.errors;
                            if (errors.name) errorMsg = 'Emri: ' + errors.name[0];
                            if (errors.phone) errorMsg = 'Telefoni: ' + errors.phone[0];
                        }
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Gabim!',
                        text: errorMsg
                    });
                },
                complete: function() {
                    isCreatingClient = false;
                    saveBtn.html(originalText).prop('disabled', false);
                }
            });
        }

        // Auto-save me delay
        $(document).on('input', '#client_name, #client_phone', function() {
            clearTimeout(saveTimeout);
            // Nuk e save automatikisht - vetëm kur submit
        });

        // Submit kur shtypet Enter
        $(document).on('keypress', '#client_name, #client_phone', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                saveClient();
            }
        });

        // Submit button click
        $('#createClientForm').on('submit', function(e) {
            e.preventDefault();
            saveClient();
        });

        // Verifikoni që forma ekziston para se të bindni events
        if ($('#createClientForm').length === 0) {
            console.warn('createClientForm nuk u gjet në DOM');
        }

        // Riset forman kur hapet modali
        $('#createClientModal').on('show.bs.modal', function() {
            const form = $('#createClientForm');
            if (form.length > 0 && form[0]) {
                form[0].reset();
            }
            setTimeout(() => {
                $('#client_name').focus();
            }, 100);
        });

        // purchase_location → payment_status
        $('input[name="purchase_location"]').on('change', function() {
            $('select[name="payment_status"]').val($(this).val() === 'online' ? 'Unpaid' : 'Paid');
        });

        // Animation CSS
        if (!document.querySelector('style[data-spin]')) {
            const style = document.createElement('style');
            style.setAttribute('data-spin', 'true');
            style.innerHTML = `
                @keyframes spin {
                    from { transform: rotate(0deg); }
                    to { transform: rotate(360deg); }
                }
            `;
            document.head.appendChild(style);
        }
    });
</script>
<script>
    document.addEventListener("DOMContentLoaded", function() {

        const warehouseSelect = document.getElementById("warehouse_id");

        // 1️⃣ Vendos zgjedhjen e fundit kur hapet faqja
        let savedWarehouse = localStorage.getItem("selected_warehouse_id");
        if (savedWarehouse) {
            warehouseSelect.value = savedWarehouse;
        }

        // 2️⃣ Ruaj zgjedhjen sa herë ndryshohet
        warehouseSelect.addEventListener("change", function() {
            localStorage.setItem("selected_warehouse_id", this.value);
        });

    });
</script>

@endpush