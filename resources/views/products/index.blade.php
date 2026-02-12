@extends('layouts.app')

@section('title', 'Produktet')

@section('content')
@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}" />
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    /* Custom styling for warehouse badges */
    .warehouse-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 0.25rem;
    }

    .warehouse-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }

    /* Select2 custom styles */
    .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
        background-color: #0d6efd;
        border-color: #0d6efd;
        color: #fff;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.875rem;
    }

    .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice__remove {
        color: #fff;
        margin-right: 0.25rem;
    }

    .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: #ffdddd;
    }

    .select2-container--bootstrap-5 .select2-dropdown {
        z-index: 1056;
    }

    /* Stock badge colors */
    .stock-high {
        background-color: #28a745;
    }

    .stock-medium {
        background-color: #ffc107;
    }

    .stock-low {
        background-color: #dc3545;
    }
</style>
@endpush

<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Produktet</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Produktet</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h5 class="card-title mb-0">Lista e Produkteve</h5>

                <div class="d-flex gap-2">
                    <form method="GET" action="{{ route('products.index') }}" class="d-flex align-items-center">
                        <select name="warehouse_id" id="filter_warehouse" class="form-select form-select-sm" style="min-width:220px">
                            <option value="">Të gjitha Warehouses</option>
                            @foreach($warehouses as $w)
                            <option value="{{ $w->id }}" {{ request('warehouse_id') == $w->id ? 'selected' : '' }}>
                                {{ $w->name }} - {{ $w->location }}
                            </option>
                            @endforeach
                        </select>
                    </form>

                    <button type="button" class="btn btn-success" id="btn_create">
                        <i class="ri-add-line align-middle me-1"></i> Shto Produkt
                    </button>
                </div>
            </div>

            <div class="card-body">
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ri-check-line align-middle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                <div class="table-responsive">
                    <table id="products_table" class="table table-bordered table-hover table-nowrap align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" style="width: 50px;">ID</th>
                                <th scope="col">Emri</th>
                                <th scope="col">Brand</th>
                                <th scope="col">Kategoria</th>
                                <th scope="col">Warehouses</th>
                                <th scope="col">Total Stock</th>
                                <th scope="col">Çmimi</th>
                                <th scope="col">Storage</th>
                                <th scope="col">RAM</th>
                                <th scope="col">Ngjyra</th>
                                <th scope="col" class="no-sort" style="width: 150px;">Veprime</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $product)
                            <tr>
                                <td class="fw-medium">{{ $product->id }}</td>
                                <td><strong>{{ $product->name }}</strong></td>
                                <td>{{ $product->brand->name }}</td>
                                <td>
                                    <span class="badge bg-primary">{{ $product->category->name }}</span>
                                </td>
                                <td>
                                    <div class="warehouse-badges">
                                        @foreach($product->warehouses as $warehouse)
                                        <span class="badge bg-info warehouse-badge" title="{{ $warehouse->pivot->quantity }} copë">
                                            {{ $warehouse->name }}
                                            @if($warehouse->pivot->quantity > 0)
                                            <span class="badge bg-light text-dark ms-1">{{ $warehouse->pivot->quantity }}</span>
                                            @endif
                                        </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td>
                                    @php
                                    $totalQty = $product->total_quantity;
                                    $badgeClass = $totalQty > 10 ? 'success' : ($totalQty > 0 ? 'warning' : 'danger');
                                    @endphp
                                    <span class="badge bg-{{ $badgeClass }}">{{ $totalQty }}</span>
                                </td>
                                <td>
                                    <strong>{{ number_format($product->unit_price, 2) }} {{ $product->currency ? $product->currency->symbol : '' }}</strong>
                                </td>
                                <td>{{ $product->storage ?? '-' }}</td>
                                <td>{{ $product->ram ?? '-' }}</td>
                                <td>{{ $product->color ?? '-' }}</td>
                                <td>
                                    <div class="hstack gap-1">
                                        <button type="button"
                                            class="btn btn-sm btn-info btn-show"
                                            data-id="{{ $product->id }}"
                                            title="Shiko">
                                            <i class="ri-eye-line"></i>
                                        </button>

                                        <button type="button"
                                            class="btn btn-sm btn-primary btn-edit"
                                            data-id="{{ $product->id }}"
                                            title="Modifiko">
                                            <i class="ri-pencil-line"></i>
                                        </button>

                                        <button type="button"
                                            class="btn btn-sm btn-danger btn-delete"
                                            data-id="{{ $product->id }}"
                                            title="Fshij">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted py-4">
                                    <i class="ri-inbox-line fs-3 d-block mb-2"></i>
                                    Nuk ka produkte për të shfaqur
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($products->hasPages())
                <div class="mt-3">
                    {{ $products->links('pagination::bootstrap-5') }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@include('products.create')
@include('products.show')
@include('products.edit')

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        console.log('Products page loaded');

        // Global variables
        let warehouses = @json($warehouses);
        let categories = @json($categories);
        let brands = @json($brands);
        let currencies = @json($currencies);

        // Initialize warehouse filter Select2
        $('#filter_warehouse').select2({
            theme: 'bootstrap-5',
            placeholder: 'Filtro sipas warehouse',
            allowClear: true,
            width: '100%'
        });

        $('#filter_warehouse').on('change', function() {
            $(this).closest('form').submit();
        });

        // ==================== CREATE MODAL ====================
        $('#btn_create').on('click', function() {
            console.log('Create button clicked');
            var modal = new bootstrap.Modal(document.getElementById('createModal'));
            $('#createProductForm')[0].reset();
            $('#phone_fields').hide();
            $('#storage, #ram, #color').prop('required', false);

            // Destroy existing Select2 if any
            if ($('#create_warehouse_ids').data('select2')) {
                $('#create_warehouse_ids').select2('destroy');
            }

            // Initialize Select2 for warehouses
            setTimeout(function() {
                $('#create_warehouse_ids').select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    placeholder: 'Zgjidh warehouses',
                    allowClear: true,
                    closeOnSelect: false,
                    dropdownParent: $('#createModal')
                });
            }, 100);

            modal.show();
        });

        // Category change for phone fields (CREATE)
        $(document).on('change', '#category_id', function() {
            const selectedOption = $(this).find('option:selected');
            const categoryName = selectedOption.text().trim().toLowerCase();
            console.log('Category changed to:', categoryName);

            if (categoryName === 'telefona' || categoryName === 'telefon') {
                $('#phone_fields').slideDown();
                $('#storage, #ram, #color').prop('required', true);
            } else {
                $('#phone_fields').slideUp();
                $('#storage, #ram, #color').prop('required', false).val('');
            }
        });

        // Cleanup Select2 on modal close
        $('#createModal').on('hidden.bs.modal', function() {
            if ($('#create_warehouse_ids').data('select2')) {
                $('#create_warehouse_ids').select2('destroy');
            }
        });

        // ==================== SHOW MODAL ====================
        $(document).on('click', '.btn-show', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            console.log('Show clicked for ID:', id);

            const modal = new bootstrap.Modal(document.getElementById('showModal'));
            const modalBody = document.getElementById('showModalBody');

            modalBody.innerHTML = `
                <div class="text-center">
                    <div class="spinner-border text-info" role="status">
                        <span class="visually-hidden">Duke ngarkuar...</span>
                    </div>
                </div>
            `;

            modal.show();

            fetch('/products/' + id, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Show data received:', data);

                    let phoneFields = '';
                    const catName = data.category.name.toLowerCase();
                    if (catName === 'telefona' || catName === 'telefon') {
                        phoneFields = `
                            <tr>
                                <td colspan="2" class="bg-light"><strong><i class="ri-smartphone-line me-1"></i> Detaje të Telefonit</strong></td>
                            </tr>
                            <tr>
                                <th class="ps-0" scope="row">Storage:</th>
                                <td class="text-muted"><strong>${data.storage || 'N/A'}</strong></td>
                            </tr>
                            <tr>
                                <th class="ps-0" scope="row">RAM:</th>
                                <td class="text-muted"><strong>${data.ram || 'N/A'}</strong></td>
                            </tr>
                            <tr>
                                <th class="ps-0" scope="row">Ngjyra:</th>
                                <td class="text-muted"><strong>${data.color || 'N/A'}</strong></td>
                            </tr>
                        `;
                    }

                    // Warehouse info
                    let warehouseInfo = '';
                    if (data.warehouses && data.warehouses.length > 0) {
                        warehouseInfo = data.warehouses.map(w =>
                            `<span class="badge bg-info me-1">${w.name}: ${w.pivot.quantity} copë</span>`
                        ).join('');
                    } else {
                        warehouseInfo = '<span class="text-muted">Asnjë warehouse</span>';
                    }

                    modalBody.innerHTML = `
                        <div class="table-responsive">
                            <table class="table table-borderless mb-0">
                                <tbody>
                                    <tr>
                                        <th class="ps-0" scope="row" style="width: 150px;">ID:</th>
                                        <td class="text-muted">${data.id}</td>
                                    </tr>
                                    <tr>
                                        <th class="ps-0" scope="row">Emri:</th>
                                        <td class="text-muted"><strong>${data.name}</strong></td>
                                    </tr>
                                    <tr>
                                        <th class="ps-0" scope="row">Brand:</th>
                                        <td class="text-muted">${data.brand.name}</td>
                                    </tr>
                                    <tr>
                                        <th class="ps-0" scope="row">Kategoria:</th>
                                        <td class="text-muted"><span class="badge bg-primary">${data.category.name}</span></td>
                                    </tr>
                                    <tr>
                                        <th class="ps-0" scope="row">Warehouses:</th>
                                        <td class="text-muted">${warehouseInfo}</td>
                                    </tr>
                                    <tr>
                                        <th class="ps-0" scope="row">Çmimi:</th>
                                        <td class="text-muted"><strong>${parseFloat(data.price).toFixed(2)} ${data.currency ? data.currency.symbol : ''}</strong></td>
                                    </tr>
                                    ${phoneFields}
                                    <tr>
                                        <th class="ps-0" scope="row">Krijuar më:</th>
                                        <td class="text-muted">${new Date(data.created_at).toLocaleString('sq-AL')}</td>
                                    </tr>
                                    <tr>
                                        <th class="ps-0" scope="row">Përditësuar më:</th>
                                        <td class="text-muted">${new Date(data.updated_at).toLocaleString('sq-AL')}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    `;
                })
                .catch(error => {
                    console.error('Show fetch error:', error);
                    modalBody.innerHTML = `
                        <div class="alert alert-danger" role="alert">
                            <i class="ri-error-warning-line align-middle me-2"></i>
                            Ka ndodhur një gabim gjatë ngarkimit të të dhënave.
                        </div>
                    `;
                });
        });

        // ==================== EDIT MODAL ====================
        $(document).on('click', '.btn-edit', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            console.log('Edit clicked for ID:', id);

            const modal = new bootstrap.Modal(document.getElementById('editModal'));
            const modalBody = document.getElementById('editModalBody');
            const form = document.getElementById('editForm');

            modalBody.innerHTML = `
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Duke ngarkuar...</span>
                    </div>
                </div>
            `;

            modal.show();
            form.action = '/products/' + id;

            fetch('/products/' + id, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Edit data received:', data);
                    const catName = data.category.name.toLowerCase();
                    const isPhone = (catName === 'telefona' || catName === 'telefon');

                    let phoneFieldsHTML = '';
                    if (isPhone) {
                        phoneFieldsHTML = `
                            <div id="edit_phone_fields">
                                <hr>
                                <h6 class="mb-3"><i class="ri-smartphone-line me-1"></i> Detaje për Telefon</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="edit_storage" class="form-label">Storage (GB) <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="edit_storage" name="storage" value="${data.storage || ''}" placeholder="p.sh. 128GB" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="edit_ram" class="form-label">RAM <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="edit_ram" name="ram" value="${data.ram || ''}" placeholder="p.sh. 8GB" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="edit_color" class="form-label">Ngjyra <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="edit_color" name="color" value="${data.color || ''}" placeholder="p.sh. Black" required>
                                    </div>
                                </div>
                            </div>
                        `;
                    }

                    const selectedWarehouseIds = data.warehouses ? data.warehouses.map(w => w.id) : [];

                    modalBody.innerHTML = `
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="edit_warehouse_ids" class="form-label">
                                    Warehouses <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="edit_warehouse_ids" name="warehouse_ids[]" multiple="multiple" required>
                                    ${warehouses.map(w => `
                                        <option value="${w.id}" ${selectedWarehouseIds.includes(w.id) ? 'selected' : ''}>
                                            ${w.name} - ${w.location}
                                        </option>
                                    `).join('')}
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_category_id" class="form-label">Kategoria <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_category_id" name="category_id" required>
                                    <option value="">Zgjidh Kategorinë</option>
                                    ${categories.map(c => `<option value="${c.id}" ${c.id == data.category_id ? 'selected' : ''}>${c.name}</option>`).join('')}
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_brand_id" class="form-label">Brand <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_brand_id" name="brand_id" required>
                                    <option value="">Zgjidh Brand</option>
                                    ${brands.map(b => `<option value="${b.id}" ${b.id == data.brand_id ? 'selected' : ''}>${b.name}</option>`).join('')}
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="edit_name" class="form-label">Emri <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_name" name="name" value="${data.name}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_price" class="form-label">Çmimi <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="edit_price" name="unit_price" value="${data.price}" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_currency_id" class="form-label">Currency <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_currency_id" name="currency_id" required>
                                    <option value="">Zgjidh Currency</option>
                                    ${currencies.map(cu => `<option value="${cu.id}" ${cu.id == data.currency_id ? 'selected' : ''}>${cu.code} (${cu.symbol})</option>`).join('')}
                                </select>
                            </div>
                        </div>
                        
                        ${phoneFieldsHTML}
                    `;

                    // Initialize Select2 for edit warehouses
                    $('#edit_warehouse_ids').select2({
                        theme: 'bootstrap-5',
                        width: '100%',
                        placeholder: 'Zgjidh warehouses',
                        allowClear: true,
                        closeOnSelect: false,
                        tags: false,
                        dropdownParent: $('#editModal')
                    });

                    // Category change handler for edit
                    $(document).off('change', '#edit_category_id').on('change', '#edit_category_id', function() {
                        const selectedCategoryId = $(this).val();
                        const selectedCategory = categories.find(c => c.id == selectedCategoryId);

                        if (selectedCategory) {
                            const catName = selectedCategory.name.toLowerCase();
                            if (catName === 'telefona' || catName === 'telefon') {
                                if ($('#edit_phone_fields').length === 0) {
                                    const phoneHTML = `
                                        <div id="edit_phone_fields" style="display:none;">
                                            <hr>
                                            <h6 class="mb-3"><i class="ri-smartphone-line me-1"></i> Detaje për Telefon</h6>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="edit_storage" class="form-label">Storage (GB) <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="edit_storage" name="storage" value="" placeholder="p.sh. 128GB" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="edit_ram" class="form-label">RAM <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="edit_ram" name="ram" value="" placeholder="p.sh. 8GB" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="edit_color" class="form-label">Ngjyra <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="edit_color" name="color" value="" placeholder="p.sh. Black" required>
                                                </div>
                                            </div>
                                        </div>
                                    `;
                                    $('#editModalBody .row').first().after(phoneHTML);
                                }
                                $('#edit_phone_fields').slideDown();
                                $('#edit_storage, #edit_ram, #edit_color').prop('required', true);
                            } else {
                                $('#edit_phone_fields').slideUp();
                                $('#edit_storage, #edit_ram, #edit_color').prop('required', false);
                            }
                        }
                    });
                })
                .catch(error => {
                    console.error('Edit fetch error:', error);
                    modalBody.innerHTML = `
                        <div class="alert alert-danger" role="alert">
                            <i class="ri-error-warning-line align-middle me-2"></i>
                            Ka ndodhur një gabim gjatë ngarkimit të të dhënave.
                        </div>
                    `;
                });
        });

        // Cleanup Select2 on edit modal close
        $('#editModal').on('hidden.bs.modal', function() {
            if ($('#edit_warehouse_ids').data('select2')) {
                $('#edit_warehouse_ids').select2('destroy');
            }
        });

        // ==================== DELETE ====================
        $(document).on('click', '.btn-delete', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            console.log('Delete clicked for ID:', id);

            Swal.fire({
                title: 'A jeni të sigurt?',
                text: "Ky produkt do të fshihet përgjithmonë!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Po, fshije!',
                cancelButtonText: 'Anulo',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '/products/' + id;

                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';

                    const methodField = document.createElement('input');
                    methodField.type = 'hidden';
                    methodField.name = '_method';
                    methodField.value = 'DELETE';

                    form.appendChild(csrfToken);
                    form.appendChild(methodField);
                    document.body.appendChild(form);

                    form.submit();
                }
            });
        });

        // ==================== DATATABLES ====================
        try {
            if ($.fn.DataTable) {
                $('#products_table').DataTable({
                    paging: false,
                    info: false,
                    lengthChange: false,
                    searching: true,
                    order: [
                        [0, 'desc']
                    ],
                    columnDefs: [{
                        orderable: false,
                        targets: 'no-sort'
                    }],
                    language: {
                        search: "Kërko:",
                        zeroRecords: "Nuk u gjet asnjë rezultat",
                        emptyTable: "Nuk ka të dhëna në tabelë"
                    }
                });
            }
        } catch (e) {
            console.warn('DataTables init failed:', e);
        }
    });
</script>

@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Sukses!',
        text: '{{ session("success") }}',
        timer: 3000,
        showConfirmButton: false
    });
</script>
@endif
@endpush