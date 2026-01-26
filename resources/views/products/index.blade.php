@extends('layouts.app')

@section('title', 'Produktet')

@section('content')
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
            <div class="card-header d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1">Lista e Produkteve</h5>
                <div>
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
                    <table class="table table-bordered table-hover table-nowrap align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" style="width: 50px;">ID</th>
                                <th scope="col">Warehouse</th>
                                <th scope="col">Emri</th>
                                <th scope="col">Brand</th>
                                <th scope="col">Kategoria</th>
                                <th scope="col">Sasia</th>
                                <th scope="col">Çmimi</th>
                                <th scope="col">Valuta</th>
                                <th scope="col">Storage</th>
                                <th scope="col">RAM</th>
                                <th scope="col">Ngjyra</th>
                                <th scope="col">Imei</th>
                                <th scope="col">Magazina</th>
                                <th scope="col" style="width: 150px;">Veprime</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $product)
                            <tr>
                                <td class="fw-medium">{{ $product->id }}</td>
                                <td>{{ $product->warehouse->name }}</td>

                                <td>{{ $product->brand->name }}</td>
                                <td>
                                    <span class="badge bg-primary">{{ $product->category->name }}</span>
                                </td>
                                <td><strong>{{ $product->name }}</strong></td>
                                <td>
                                    <span class="badge bg-{{ $product->quantity > 0 ? 'success' : 'danger' }}">
                                        {{ $product->quantity }}
                                    </span>
                                </td>
                                <td>
                                    {{ number_format($product->price, 2) }}
                                </td>
                                <td>{{ $product->currency ? $product->currency->symbol : '' }}</strong>
                                </td>

                                <td>{{ $product->storage ?? '-' }}</td>
                                <td>{{ $product->ram ?? '-' }}</td>
                                <td>{{ $product->color ?? '-' }}</td>
                                <td>{{ $product->imei ?? '-' }}</td>
                                <td>{{ $product->magazina ?? '-' }}</td>

                                <td>
                                    <div class="hstack gap-1">
                                        <button type="button"
                                            class="btn btn-sm btn-info btn-show" data-id="{{ $product->id }}"
                                            title="Shiko">
                                            <i class="ri-eye-line"></i>
                                        </button>

                                        <button type="button"
                                            class="btn btn-sm btn-primary btn-edit" data-id="{{ $product->id }}"
                                            title="Modifiko">
                                            <i class="ri-pencil-line"></i>
                                        </button>

                                        <button type="button"
                                            class="btn btn-sm btn-danger btn-delete" data-id="{{ $product->id }}"
                                            title="Fshij">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="ri-shopping-bag-line fs-1 d-block mb-2"></i>
                                    Nuk ka produkte të regjistruara.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($products->hasPages())
                <div class="mt-3">
                    {{ $products->links() }}
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
<script>
    console.log('Script loaded!');

    $(document).ready(function() {
        console.log('jQuery ready!');
        console.log('Warehouses:', @json($warehouses));
        console.log('Categories:', @json($categories));
        console.log('Brands:', @json($brands));

        console.log('Show buttons:', $('.btn-show').length);
        console.log('Edit buttons:', $('.btn-edit').length);
        console.log('Delete buttons:', $('.btn-delete').length);

        let warehouses = @json($warehouses);
        let categories = @json($categories);
        let brands = @json($brands);
        let currencies = @json($currencies);

        $('#btn_create').on('click', function() {
            console.log('Create button clicked!');
            var modal = new bootstrap.Modal(document.getElementById('createModal'));
            $('#createProductForm')[0].reset();
            $('#phone_fields').hide();
            $('#storage, #ram, #color, #imei, #magazina').prop('required', false);
            modal.show();
        });

        // Event listener për ndryshimin e kategorisë në Create Modal
        $(document).on('change', '#category_id', function() {
            const selectedOption = $(this).find('option:selected');
            const categoryName = selectedOption.text().trim().toLowerCase();
            console.log('Category changed to:', categoryName);

            if (categoryName === 'telefona' || categoryName === 'telefon') {
                $('#phone_fields').slideDown();
                $('#storage, #ram, #color, #imei, #magazina').prop('required', true);
            } else {
                $('#phone_fields').slideUp();
                $('#storage, #ram, #color, #imei, #magazina').prop('required', false).val('');
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

            console.log(modal)
            modal.show();

            fetch('/products/' + id, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Data received:', data);
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
                        <tr>
                            <th class="ps-0" scope="row">IMEI:</th>
                            <td class="text-muted"><code>${data.imei || 'N/A'}</code></td>
                        </tr>
                        <tr>
                            <th class="ps-0" scope="row">Magazina:</th>
                            <td class="text-muted"><strong>${data.magazina || 'N/A'}</strong></td>
                        </tr>
                    `;
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
                                    <th class="ps-0" scope="row">Warehouse:</th>
                                    <td class="text-muted">${data.warehouse.name}</td>
                                </tr>
                                <tr>
                                    <th class="ps-0" scope="row">Sasia:</th>
                                    <td class="text-muted"><span class="badge bg-${data.quantity > 0 ? 'success' : 'danger'}">${data.quantity}</span></td>
                                </tr>
                                <tr>
                                    <th class="ps-0" scope="row">Çmimi:</th>
                                    <td class="text-muted"><strong>${parseFloat(data.price).toFixed(2)} Lekë</strong></td>
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
                    console.error('Fetch error:', error);
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
                                <div class="col-md-6 mb-3">
                                    <label for="edit_imei" class="form-label">IMEI <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit_imei" name="imei" value="${data.imei || ''}" placeholder="p.sh. 123456789012345" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_magazina" class="form-label">Magazina <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="edit_magazina" name="magazina" value="${data.magazina || ''}" required>
                                </div>
                            </div>
                        </div>
                    `;
                    }

                    modalBody.innerHTML = `
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_warehouse_id" class="form-label">Warehouse <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_warehouse_id" name="warehouse_id" required>
                                <option value="">Zgjidh Warehouse</option>
                                ${warehouses.map(w => `<option value="${w.id}" ${w.id == data.warehouse_id ? 'selected' : ''}>${w.name}</option>`).join('')}
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
                        <div class="col-md-6 mb-3">
                            <label for="edit_name" class="form-label">Emri <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_name" name="name" value="${data.name}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_quantity" class="form-label">Sasia <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="edit_quantity" name="quantity" value="${data.quantity}" min="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_price" class="form-label">Çmimi (Lekë) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="edit_price" name="price" value="${data.price}" step="0.01" min="0" required>
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

                    // Event listener për ndryshimin e kategorisë
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
                                            <div class="col-md-6 mb-3">
                                                <label for="edit_imei" class="form-label">IMEI <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="edit_imei" name="imei" value="" placeholder="p.sh. 123456789012345" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="edit_magazina" class="form-label">Magazina <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="edit_magazina" name="magazina" value="" required>
                                            </div>
                                        </div>
                                    </div>
                                `;
                                    $('#editModalBody .row').after(phoneHTML);
                                }
                                $('#edit_phone_fields').slideDown();
                                $('#edit_storage, #edit_ram, #edit_color, #edit_imei, #edit_magazina').prop('required', true);
                            } else {
                                $('#edit_phone_fields').slideUp();
                                $('#edit_storage, #edit_ram, #edit_color, #edit_imei, #edit_magazina').prop('required', false);
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

        // ==================== DELETE MODAL ====================
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