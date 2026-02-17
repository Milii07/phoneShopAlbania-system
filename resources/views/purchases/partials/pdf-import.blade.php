{{-- resources/views/purchases/partials/pdf-import.blade.php --}}
<style>
    .import-btn-zone {
        border: 2px dashed #dee2e6;
        border-radius: 12px;
        padding: 22px 16px;
        text-align: center;
        cursor: pointer;
        transition: all .25s ease;
        background: #fafafa;
    }

    .import-btn-zone:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0, 0, 0, .1);
    }

    .import-btn-zone.pdf-zone:hover {
        border-color: #e74c3c;
        background: #fff5f5;
    }

    .import-btn-zone.img-zone:hover {
        border-color: #3498db;
        background: #f0f8ff;
    }

    .import-btn-zone.dragover {
        transform: translateY(-2px);
    }

    .import-btn-zone.pdf-zone.dragover {
        border-color: #e74c3c;
        background: #fff5f5;
    }

    .import-btn-zone.img-zone.dragover {
        border-color: #3498db;
        background: #f0f8ff;
    }

    .import-btn-zone .zone-icon {
        font-size: 2rem;
        display: block;
        margin-bottom: 8px;
    }

    .import-btn-zone.pdf-zone .zone-icon {
        color: #e74c3c;
    }

    .import-btn-zone.img-zone .zone-icon {
        color: #3498db;
    }

    .pdf-processing-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, .5);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(3px);
    }

    .pdf-processing-overlay.active {
        display: flex;
    }

    .pdf-processing-card {
        background: white;
        border-radius: 16px;
        padding: 32px 44px;
        text-align: center;
        box-shadow: 0 20px 50px rgba(0, 0, 0, .2);
        max-width: 320px;
        width: 90%;
    }

    .pdf-spinner {
        width: 52px;
        height: 52px;
        border: 5px solid #e9ecef;
        border-top-color: #667eea;
        border-radius: 50%;
        animation: pdfSpin .8s linear infinite;
        margin: 0 auto 14px;
    }

    @keyframes pdfSpin {
        to {
            transform: rotate(360deg);
        }
    }

    .import-preview-card {
        background: #f8f9ff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 14px 18px;
        margin-bottom: 10px;
    }

    .import-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: white;
        padding: 3px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 10px;
    }

    .import-badge.blue {
        background: #667eea;
    }

    .import-badge.green {
        background: #11998e;
    }

    .import-badge.red {
        background: #f5576c;
    }

    .product-preview-row {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 10px 14px;
        margin-bottom: 6px;
    }

    .product-preview-row.found {
        border-left: 3px solid #38a169;
        background: #f0fff4;
    }

    .product-preview-row.not-found {
        border-left: 3px solid #e2882a;
        background: #fffaf0;
    }

    .imei-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        margin-top: 6px;
    }

    .imei-tag {
        background: #edf2f7;
        color: #2d3748;
        padding: 2px 8px;
        border-radius: 8px;
        font-size: 11px;
        font-family: monospace;
        letter-spacing: .3px;
    }
</style>

<!-- Processing Overlay -->
<div class="pdf-processing-overlay" id="pdfProcessingOverlay">
    <div class="pdf-processing-card">
        <div class="pdf-spinner"></div>
        <h6 class="fw-bold mb-1" id="overlayText">Duke lexuar dokumentin...</h6>
        <p class="text-muted mb-0" style="font-size:13px">Ju luteni prisni</p>
    </div>
</div>

<!-- Import Card -->
<div class="card mb-4" id="pdfImportSection">
    <div class="card-header d-flex align-items-center justify-content-between"
        style="background:linear-gradient(135deg,#667eea,#764ba2);border-radius:8px 8px 0 0">
        <div class="d-flex align-items-center gap-2">
            <i class="ri-import-line text-white fs-5"></i>
            <h6 class="text-white mb-0 fw-bold">Import nga Fatura</h6>
        </div>
        <button type="button" class="btn btn-sm btn-outline-light" onclick="togglePdfSection()">
            <i id="pdfToggleIcon" class="ri-arrow-up-s-line"></i>
        </button>
    </div>

    <div class="card-body" id="pdfImportBody">

        <!-- STATE: upload — dy butona të ndarë -->
        <div id="stateUpload">
            <div class="row g-3">

                <!-- Butoni 1: PDF -->
                <div class="col-md-6">
                    <div class="import-btn-zone pdf-zone"
                        onclick="triggerInput('pdfFileInput')"
                        ondragover="onDragOver(event,'pdf')"
                        ondragleave="onDragLeave(event,'pdf')"
                        ondrop="onDrop(event,'pdf')">
                        <span class="zone-icon"><i class="ri-file-pdf-line"></i></span>
                        <h6 class="mb-1 fw-semibold">Lexo PDF</h6>
                        <p class="text-muted mb-0" style="font-size:12px">
                            Faturat PDF (p.sh. WEST TELECOM)<br>
                            <small>Kliko ose zvarrit këtu</small>
                        </p>
                    </div>
                    <input type="file" id="pdfFileInput" accept=".pdf" style="display:none"
                        onchange="onFileSelected(this,'pdf')">
                </div>

                <!-- Butoni 2: Imazh -->
                <div class="col-md-6">
                    <div class="import-btn-zone img-zone"
                        onclick="triggerInput('imgFileInput')"
                        ondragover="onDragOver(event,'img')"
                        ondragleave="onDragLeave(event,'img')"
                        ondrop="onDrop(event,'img')">
                        <span class="zone-icon"><i class="ri-image-line"></i></span>
                        <h6 class="mb-1 fw-semibold">Lexo Imazh</h6>
                        <p class="text-muted mb-0" style="font-size:12px">
                            Foto faturash JPG/PNG<br>
                            <small>Kliko ose zvarrit këtu</small>
                        </p>
                    </div>
                    <input type="file" id="imgFileInput" accept=".jpg,.jpeg,.png" style="display:none"
                        onchange="onFileSelected(this,'img')">
                </div>

            </div>
        </div>

        <!-- STATE: preview -->
        <div id="statePreview" style="display:none">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="mb-0 fw-bold text-success">
                    <i class="ri-check-double-line me-1"></i>Të dhënat u lexuan me sukses
                </h6>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-success btn-sm" onclick="applyImportedData()">
                        <i class="ri-check-line me-1"></i>Apliko në Formë
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetToUpload()">
                        <i class="ri-arrow-left-line me-1"></i>Kthehu
                    </button>
                </div>
            </div>
            <div id="previewContent"></div>
        </div>

        <!-- STATE: error -->
        <div id="stateError" style="display:none">
            <div class="alert alert-danger d-flex align-items-start gap-3 mb-0">
                <i class="ri-error-warning-line fs-5 mt-1 flex-shrink-0"></i>
                <div class="flex-grow-1">
                    <strong>Gabim gjatë leximit:</strong>
                    <p id="errorText" class="mb-2 mt-1 small"></p>
                    <button type="button" class="btn btn-sm btn-danger" onclick="resetToUpload()">
                        <i class="ri-arrow-left-line me-1"></i>Provo Sërish
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>