{{-- resources/views/purchases/partials/pdf-import-scripts.blade.php --}}
<script>
    let _importedData = null;

    // ─────────────────────────────────────────────
    // TOGGLE
    // ─────────────────────────────────────────────
    function toggleImportSection() {
        const body = document.getElementById('importBody');
        const icon = document.getElementById('importToggleIcon');
        const hidden = body.style.display === 'none';
        body.style.display = hidden ? 'block' : 'none';
        icon.className = hidden ? 'ri-arrow-up-s-line' : 'ri-arrow-down-s-line';
    }

    // ─────────────────────────────────────────────
    // FILE INPUT TRIGGERS
    // ─────────────────────────────────────────────
    function triggerInput(inputId) {
        const inp = document.getElementById(inputId);
        inp.value = '';
        inp.click();
    }

    // ─────────────────────────────────────────────
    // DRAG & DROP — mode = 'pdf' | 'excel'
    // ─────────────────────────────────────────────
    function onDragOver(e, mode) {
        e.preventDefault();
        const zone = mode === 'pdf' ?
            document.querySelector('.pdf-zone') :
            document.querySelector('.excel-zone');
        zone.classList.add('dragover');
    }

    function onDragLeave(e, mode) {
        const zone = mode === 'pdf' ?
            document.querySelector('.pdf-zone') :
            document.querySelector('.excel-zone');
        zone.classList.remove('dragover');
    }

    function onDrop(e, mode) {
        e.preventDefault();
        const zone = mode === 'pdf' ?
            document.querySelector('.pdf-zone') :
            document.querySelector('.excel-zone');
        zone.classList.remove('dragover');
        const file = e.dataTransfer.files[0];
        if (file) processFile(file, mode);
    }

    function onFileSelected(input, mode) {
        if (input.files && input.files[0]) processFile(input.files[0], mode);
    }

    // ─────────────────────────────────────────────
    // PROCESS FILE
    // ─────────────────────────────────────────────
    function processFile(file, mode) {

        // Validim
        if (mode === 'pdf') {
            if (file.type !== 'application/pdf') {
                showError('Butoni PDF pranon vetëm skedarë .pdf');
                return;
            }
        } else {
            const excelTypes = [
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'text/csv'
            ];
            if (!excelTypes.includes(file.type) && !file.name.match(/\.(xlsx?|csv)$/i)) {
                showError('Butoni Excel pranon vetëm XLS, XLSX dhe CSV.');
                return;
            }
        }

        if (file.size > 10 * 1024 * 1024) {
            showError('Skedari është shumë i madh (max 10MB).');
            return;
        }

        // Spinner
        document.getElementById('overlayText').textContent = mode === 'pdf' ?
            'Duke lexuar PDF-in...' :
            'Duke lexuar Excel-in...';
        document.getElementById('processingOverlay').classList.add('active');

        const route = mode === 'pdf' ?
            '{{ route("purchases.extract-pdf") }}' :
            '{{ route("purchases.extract-excel") }}';

        const fd = new FormData();
        fd.append('document', file);
        fd.append('_token', document.querySelector('meta[name="csrf-token"]').content);

        fetch(route, {
                method: 'POST',
                body: fd
            })
            .then(r => r.json())
            .then(resp => {
                document.getElementById('processingOverlay').classList.remove('active');
                if (!resp.success) {
                    showError(resp.message || 'Gabim i panjohur.');
                    return;
                }

                _importedData = resp.data;
                _importedData._partner = resp.partner;

                console.log('[Import] Data:', JSON.stringify(_importedData, null, 2));

                renderPreview(_importedData);
                setState('preview');
            })
            .catch(err => {
                document.getElementById('processingOverlay').classList.remove('active');
                showError('Lidhja dështoi: ' + (err.message || err));
            });
    }

    // ─────────────────────────────────────────────
    // STATES
    // ─────────────────────────────────────────────
    function setState(state) {
        document.getElementById('stateUpload').style.display = state === 'upload' ? 'block' : 'none';
        document.getElementById('statePreview').style.display = state === 'preview' ? 'block' : 'none';
        document.getElementById('stateError').style.display = state === 'error' ? 'block' : 'none';
    }

    function showError(msg) {
        document.getElementById('errorText').textContent = msg;
        setState('error');
    }

    function resetToUpload() {
        _importedData = null;
        document.getElementById('pdfFileInput').value = '';
        document.getElementById('excelFileInput').value = '';
        setState('upload');
    }

    // ─────────────────────────────────────────────
    // RENDER PREVIEW (same as before)
    // ─────────────────────────────────────────────
    function renderPreview(data) {
        const partner = data._partner;
        let html = '';

        html += `
    <div class="import-preview-card">
        <div class="import-badge blue"><i class="ri-building-line"></i> Furnitori</div>
        <div class="row g-2">
            <div class="col-md-4">
                <small class="text-muted d-block">Emri</small>
                <strong>${x(data.supplier?.name)}</strong>
                ${partner
                    ? `<span class="badge bg-success ms-2">✓ Gjetur</span>`
                    : `<span class="badge bg-warning text-dark ms-2">Do krijohet i ri</span>`}
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block">NIPT</small>
                <strong>${x(data.supplier?.nipt || '—')}</strong>
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block">Nr. Faturës</small>
                <strong>${x(data.invoice?.number || '—')}</strong>
            </div>
            <div class="col-md-2">
                <small class="text-muted d-block">Data</small>
                <strong>${x(data.invoice?.date || '—')}</strong>
            </div>
        </div>
    </div>`;

        html += `<div class="import-preview-card">
        <div class="import-badge green"><i class="ri-shopping-bag-line"></i> Produktet (${(data.items||[]).length})</div>`;

        (data.items || []).forEach(item => {
            const imei = Array.isArray(item.imei_numbers) ? item.imei_numbers : [];
            const imeiMatch = imei.length === parseInt(item.quantity);
            const metaBadges = [
                item.brand ? `<span class="badge bg-secondary" style="font-size:10px">${x(item.brand)}</span>` : '',
                item.category ? `<span class="badge bg-info text-dark" style="font-size:10px">${x(item.category)}</span>` : '',
                item.storage ? `<span class="badge bg-light text-dark border" style="font-size:10px">${x(item.storage)}</span>` : '',
                item.ram ? `<span class="badge bg-light text-dark border" style="font-size:10px">RAM ${x(item.ram)}</span>` : '',
                item.color ? `<span class="badge bg-light text-dark border" style="font-size:10px">${x(item.color)}</span>` : '',
            ].filter(Boolean).join(' ');

            html += `
        <div class="product-preview-row ${item.product_found ? 'found' : 'not-found'}">
            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                <i class="${item.product_found ? 'ri-checkbox-circle-fill text-success' : 'ri-add-circle-fill text-warning'}"></i>
                <strong>${x(item.product_name)}</strong>
                ${item.product_found
                    ? `<span class="badge bg-success" style="font-size:10px">✓ Gjetur</span>`
                    : `<span class="badge bg-warning text-dark" style="font-size:10px">Do krijohet i ri</span>`}
                ${metaBadges}
            </div>
            <div class="d-flex flex-wrap gap-3 mb-1">
                <small class="text-muted">Sasia: <strong>${item.quantity}</strong></small>
                <small class="text-muted">Çmimi: <strong>${fmt(item.unit_cost)}</strong></small>
                <small class="text-muted">Total: <strong>${fmt(item.line_total)}</strong></small>
                <small class="${imei.length > 0 ? (imeiMatch ? 'text-success' : 'text-danger') : 'text-muted'}">
                    <i class="ri-fingerprint-line"></i>
                    IMEI: <strong>${imei.length}</strong>
                    ${imei.length > 0 && !imeiMatch ? `/ kërkohen ${item.quantity}` : ''}
                </small>
            </div>
            ${imei.length > 0 ? `<div class="imei-tags">${imei.map(n => `<span class="imei-tag">${n}</span>`).join('')}</div>` : ''}
        </div>`;
        });
        html += `</div>`;

        html += `
    <div class="import-preview-card">
        <div class="import-badge red"><i class="ri-calculator-line"></i> Totalet</div>
        <div class="row text-center">
            <div class="col-4"><small class="text-muted d-block">Pa TVSH</small><strong>${fmt(data.totals?.subtotal)} L</strong></div>
            <div class="col-4"><small class="text-muted d-block">TVSH</small><strong>${fmt(data.totals?.tax)} L</strong></div>
            <div class="col-4"><small class="text-muted d-block">Total</small><strong class="text-success">${fmt(data.totals?.total)} L</strong></div>
        </div>
    </div>`;

        document.getElementById('previewContent').innerHTML = html;
    }

    // ─────────────────────────────────────────────
    // APLIKO NË FORMË (same as before)
    // ─────────────────────────────────────────────
    function applyImportedData() {
        if (!_importedData) return;
        const data = _importedData;
        const partner = data._partner;

        if (partner) {
            const sel = document.getElementById('partner_id');
            if (sel) {
                sel.value = partner.id;
                if (typeof $ !== 'undefined') $(sel).trigger('change');
            }
        }
        if (!partner && data.supplier?.name) {
            setHidden('new_supplier_name', data.supplier.name);
            setHidden('new_supplier_nipt', data.supplier.nipt || '');
            setHidden('new_supplier_address', data.supplier.address || '');
        }

        const dEl = document.getElementById('purchase_date');
        if (dEl && data.invoice?.date) dEl.value = data.invoice.date;

        const method = data.invoice?.payment_method === 'Bank' ? 'bank' : 'cash';
        const radio = document.getElementById('payment_' + method);
        if (radio) radio.checked = true;

        document.getElementById('productsContainer').innerHTML = '';
        (data.items || []).forEach(item => addImportedProduct(item));

        if (typeof calculateTotals === 'function') calculateTotals();

        document.getElementById('importSection').style.display = 'none';

        Swal.fire({
            icon: 'success',
            title: 'U aplikua!',
            text: 'Të dhënat u importuan me sukses.',
            timer: 2500,
            timerProgressBar: true,
            showConfirmButton: false,
        });
    }

    // ─────────────────────────────────────────────
    // SHTO PRODUKT (same as before)
    // ─────────────────────────────────────────────
    function addImportedProduct(item) {
        if (typeof productIndex === 'undefined') window.productIndex = 0;
        productIndex++;
        const idx = productIndex;
        const imei = Array.isArray(item.imei_numbers) ? item.imei_numbers : [];
        const imeiStr = imei.join(', ');
        const hasImei = imei.length > 0;
        const border = item.product_found ? '#38a169' : '#e2882a';

        const html = `
    <div class="product-item" data-index="${idx}" style="border-left:4px solid ${border}">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                ${item.product_found
                    ? `<span class="badge bg-success"><i class="ri-check-line"></i> Gjetur</span>`
                    : `<span class="badge bg-warning text-dark"><i class="ri-add-line"></i> I ri</span>`}
                <strong>${x(item.product_name)}</strong>
                ${item.brand    ? `<span class="badge bg-secondary"    style="font-size:10px">${x(item.brand)}</span>`    : ''}
                ${item.category ? `<span class="badge bg-info text-dark" style="font-size:10px">${x(item.category)}</span>` : ''}
                ${item.storage  ? `<span class="badge bg-light text-dark border" style="font-size:10px">${x(item.storage)}</span>` : ''}
                ${item.ram      ? `<span class="badge bg-light text-dark border" style="font-size:10px">RAM ${x(item.ram)}</span>` : ''}
                ${item.color    ? `<span class="badge bg-light text-dark border" style="font-size:10px">${x(item.color)}</span>`   : ''}
            </div>
            <button type="button" class="btn btn-sm btn-danger remove-item">
                <i class="ri-delete-bin-line"></i>
            </button>
        </div>

        <input type="hidden" name="items[${idx}][product_id]" value="${item.product_id || ''}">
        ${!item.product_found ? `
        <input type="hidden" name="items[${idx}][new_product_name]" value="${x(item.product_name)}">
        <input type="hidden" name="items[${idx}][new_clean_name]"   value="${x(item.clean_name   || item.product_name)}">
        <input type="hidden" name="items[${idx}][new_brand]"        value="${x(item.brand    || '')}">
        <input type="hidden" name="items[${idx}][new_category]"     value="${x(item.category || '')}">
        <input type="hidden" name="items[${idx}][new_storage]"      value="${x(item.storage  || '')}">
        <input type="hidden" name="items[${idx}][new_ram]"          value="${x(item.ram      || '')}">
        <input type="hidden" name="items[${idx}][new_color]"        value="${x(item.color    || '')}">
        ` : ''}

        <div class="row g-2">
            <div class="col-md-2">
                <label class="form-label small">Qty *</label>
                <input type="number" class="form-control form-control-sm quantity-input"
                    name="items[${idx}][quantity]" value="${item.quantity}" min="1" required>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Njësia</label>
                <select class="form-select form-select-sm" name="items[${idx}][unit_type]">
                    <option value="Pcs" selected>Pcs</option>
                    <option value="Box">Box</option>
                    <option value="Kg">Kg</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Çmimi *</label>
                <input type="number" class="form-control form-control-sm unit-cost-input"
                    name="items[${idx}][unit_cost]" value="${fmt(item.unit_cost)}"
                    step="0.01" min="0" required>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Discount</label>
                <input type="number" class="form-control form-control-sm discount-input"
                    name="items[${idx}][discount]" value="0" step="0.01" min="0">
            </div>
            <div class="col-md-2">
                <label class="form-label small">TVSH</label>
                <input type="number" class="form-control form-control-sm tax-input"
                    name="items[${idx}][tax]" value="${fmt(item.tax)}" step="0.01" min="0">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Total</label>
                <input type="text" class="form-control form-control-sm line-total"
                    value="${fmt(item.line_total)}" readonly>
            </div>

            ${hasImei ? `
            <div class="col-12">
                <label class="form-label small fw-semibold">
                    <i class="ri-fingerprint-line me-1 text-primary"></i>
                    IMEI <span class="text-danger">*</span>
                    <span class="text-muted fw-normal">(${imei.length} të importuara — ndaj me presje)</span>
                </label>
                <textarea class="form-control form-control-sm imei-input"
                    name="items[${idx}][imei_numbers]"
                    rows="3" required>${imeiStr}</textarea>
                <div class="d-flex justify-content-between mt-1">
                    <small class="text-muted">
                        Vendosur: <span class="current-count fw-bold text-primary">${imei.length}</span> /
                        Kërkohen: <span class="required-count">${item.quantity}</span>
                    </small>
                    <small class="${imei.length == item.quantity ? 'text-success' : 'text-warning'}">
                        ${imei.length == item.quantity ? '✓ Sasia përputhet' : '⚠ Kontrolloni numrin'}
                    </small>
                </div>
            </div>` : ''}
        </div>
    </div>`;

        document.getElementById('productsContainer').insertAdjacentHTML('beforeend', html);
        const el = document.querySelector(`[data-index="${idx}"]`);
        if (el && typeof updateItemTotal === 'function') updateItemTotal($(el));
    }

    // ─────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────
    function x(s) {
        return s ? String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;') : '';
    }

    function fmt(n) {
        return parseFloat(n || 0).toFixed(2);
    }

    function setHidden(name, val) {
        let el = document.querySelector(`input[name="${name}"]`);
        if (!el) {
            el = document.createElement('input');
            el.type = 'hidden';
            el.name = name;
            document.getElementById('purchaseForm').appendChild(el);
        }
        el.value = val;
    }
</script>