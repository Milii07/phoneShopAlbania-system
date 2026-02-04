@extends('layouts.app')

@section('title', 'Warehouses')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Warehouses</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Warehouses</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1">Lista e Warehouses</h5>
                <div>
                    <a href="{{ route('warehouses.create') }}" class="btn btn-success">
                        <i class="ri-add-line align-middle me-1"></i> Shto Warehouse
                    </a>
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
                                <th scope="col">Emri</th>
                                <th scope="col">Lokacioni</th>
                                <th scope="col">Përshkrimi</th>
                                <th scope="col" style="width: 150px;">Krijuar më</th>
                                <th scope="col" style="width: 150px;">Veprime</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($warehouses as $warehouse)
                            <tr>
                                <td class="fw-medium">{{ $warehouse->id }}</td>
                                <td><strong>{{ $warehouse->name }}</strong></td>
                                <td>{{ $warehouse->location ?? 'N/A' }}</td>
                                <td>{{ Str::limit($warehouse->description, 50) ?? 'N/A' }}</td>
                                <td>{{ $warehouse->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="hstack gap-1">
                                        <!-- Show Button -->
                                        <button type="button"
                                            class="btn btn-sm btn-info" id="modal_show" data-id="{{ $warehouse->id }}"
                                            title="Shiko">
                                            <i class="ri-eye-line"></i>
                                        </button>

                                        <!-- Edit Button -->
                                        <button type="button"
                                            class="btn btn-sm btn-primary" id="modal_edit" data-id="{{ $warehouse->id }}"
                                            title="Modifiko">
                                            <i class="ri-pencil-line"></i>
                                        </button>

                                        <!-- Delete Button -->
                                        <button type="button"
                                            class="btn btn-sm btn-danger" id="modal_delete" data-id="{{ $warehouse->id }}"
                                            title="Fshij">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="ri-database-2-line fs-1 d-block mb-2"></i>
                                    Nuk ka warehouse të regjistruar.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($warehouses->hasPages())
                <div class="mt-3">
                    {{ $warehouses->links('pagination::bootstrap-5') }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@include('warehouses.show')

@include('warehouses.edit')

@endsection

@push('scripts')
<script>
    $('#modal_show').on('click', function() {
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

        fetch('/warehouses/' + id, {
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
                                    <th class="ps-0" scope="row">Lokacioni:</th>
                                    <td class="text-muted">${data.location || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <th class="ps-0" scope="row">Përshkrimi:</th>
                                    <td class="text-muted">${data.description || 'N/A'}</td>
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

    $('#modal_edit').on('click', function() {
        var id = $(this).data('id');

        const modal = new bootstrap.Modal(document.getElementById('editModal'));
        const modalBody = document.getElementById('editModalBody');
        const form = document.getElementById('editForm');

        // Show loading spinner
        modalBody.innerHTML = `
            <div class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Duke ngarkuar...</span>
                </div>
            </div>
        `;

        modal.show();

        // Set form action
        form.action = '/warehouses/' + id;

        // Fetch warehouse data
        fetch('/warehouses/' + id, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                modalBody.innerHTML = `
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Emri <span class="text-danger">*</span></label>
                        <input type="text" 
                            class="form-control" 
                            id="edit_name" 
                            name="name" 
                            value="${data.name}"
                            required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_location" class="form-label">Lokacioni</label>
                        <input type="text" 
                            class="form-control" 
                            id="edit_location" 
                            name="location" 
                            value="${data.location || ''}">
                    </div>

                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Përshkrimi</label>
                        <textarea class="form-control" 
                                id="edit_description" 
                                name="description" 
                                rows="3">${data.description || ''}</textarea>
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

    $('#modal_delete').on('click', function() {
        var id = $(this).data('id');

        Swal.fire({
            title: 'A jeni të sigurt?',
            text: "Ky veprim nuk mund të kthehet mbrapsht!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Po, fshije!',
            cancelButtonText: 'Anulo',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // Create and submit form
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/warehouses/' + id;

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
</script>
@endpush