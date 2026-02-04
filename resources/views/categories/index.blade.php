@extends('layouts.app')

@section('title', 'Kategoritë')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Kategoritë</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Kategoritë</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1">Lista e Kategorive</h5>
                <div>
                    <button type="button" class="btn btn-success" id="btn_create">
                        <i class="ri-add-line align-middle me-1"></i> Shto Kategori
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
                                <th scope="col" style="width: 80px;">ID</th>
                                <th scope="col">Emri i Kategorisë</th>
                                <th scope="col" style="width: 180px;">Krijuar më</th>
                                <th scope="col" style="width: 150px;">Veprime</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $category)
                            <tr>
                                <td class="fw-medium">{{ $category->id }}</td>
                                <td><strong>{{ $category->name }}</strong></td>
                                <td>{{ $category->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="hstack gap-1">
                                        <!-- Show Button -->
                                        <button type="button"
                                            class="btn btn-sm btn-info" id="modal_show" data-id="{{ $category->id }}"
                                            title="Shiko">
                                            <i class="ri-eye-line"></i>
                                        </button>

                                        <!-- Edit Button -->
                                        <button type="button"
                                            class="btn btn-sm btn-primary" id="modal_edit" data-id="{{ $category->id }}"
                                            title="Modifiko">
                                            <i class="ri-pencil-line"></i>
                                        </button>

                                        <!-- Delete Button -->
                                        <button type="button"
                                            class="btn btn-sm btn-danger" id="modal_delete" data-id="{{ $category->id }}"
                                            title="Fshij">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="ri-folder-line fs-1 d-block mb-2"></i>
                                    Nuk ka kategori të regjistruara.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($categories->hasPages())
                <div class="mt-3">
                    {{ $categories->links('pagination::bootstrap-5') }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@include('categories.create')

@include('categories.show')

@include('categories.edit')

@endsection

@push('scripts')
<script>
    // Create Modal
    $('#btn_create').on('click', function() {
        var modal = new bootstrap.Modal(document.getElementById('createModal'));
        modal.show();
    });

    // Show Modal
    $(document).on('click', '#modal_show', function() {
        var id = $(this).data('id');

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

        fetch('/categories/' + id, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
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
                console.error('Error:', error);
                modalBody.innerHTML = `
                    <div class="alert alert-danger" role="alert">
                        <i class="ri-error-warning-line align-middle me-2"></i>
                        Ka ndodhur një gabim gjatë ngarkimit të të dhënave.
                    </div>
                `;
            });
    });

    // Edit Modal
    $(document).on('click', '#modal_edit', function() {
        var id = $(this).data('id');

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

        form.action = '/categories/' + id;

        fetch('/categories/' + id, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                modalBody.innerHTML = `
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Emri i Kategorisë <span class="text-danger">*</span></label>
                        <input type="text"
                            class="form-control"
                            id="edit_name"
                            name="name"
                            value="${data.name}"
                            placeholder="Shkruani emrin e kategorisë"
                            required>
                    </div>
                `;
            })
            .catch(error => {
                console.error('Error:', error);
                modalBody.innerHTML = `
                    <div class="alert alert-danger" role="alert">
                        <i class="ri-error-warning-line align-middle me-2"></i>
                        Ka ndodhur një gabim gjatë ngarkimit të të dhënave.
                    </div>
                `;
            });
    });

    // Delete Modal
    $(document).on('click', '#modal_delete', function() {
        var id = $(this).data('id');

        Swal.fire({
            title: 'A jeni të sigurt?',
            text: "Kjo kategori do të fshihet përgjithmonë!",
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
                form.action = '/categories/' + id;

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

    // Success message
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