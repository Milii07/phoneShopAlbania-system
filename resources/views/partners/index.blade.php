@extends('layouts.app')

@section('title', 'Partnerët')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Partnerët</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Partnerët</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1">Lista e Partnerëve</h5>
                <div>
                    <button type="button" class="btn btn-success" id="btn_create">
                        <i class="ri-add-line align-middle me-1"></i> Shto Partner
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
                                <th scope="col">Emri</th>
                                <th scope="col">Nr. Telefoni</th>

                                <th scope="col" style="width: 150px;">Veprime</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($partners as $partner)
                            <tr>
                                <td class="fw-medium">{{ $partner->id }}</td>
                                <td><strong>{{ $partner->name }}</strong></td>
                                <td>{{ $partner->phone }}</td>

                                <td>
                                    <div class="hstack gap-1">
                                        <button type="button"
                                            class="btn btn-sm btn-info btn-show"
                                            data-id="{{ $partner->id }}"
                                            title="Shiko">
                                            <i class="ri-eye-line"></i>
                                        </button>

                                        <button type="button"
                                            class="btn btn-sm btn-primary btn-edit"
                                            data-id="{{ $partner->id }}"
                                            title="Modifiko">
                                            <i class="ri-pencil-line"></i>
                                        </button>

                                        <button type="button"
                                            class="btn btn-sm btn-danger btn-delete"
                                            data-id="{{ $partner->id }}"
                                            title="Fshij">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="ri-user-line fs-1 d-block mb-2"></i>
                                    Nuk ka partnerë të regjistruar.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($partners->hasPages())
                <div class="mt-3">
                    {{ $partners->links('pagination::bootstrap-5') }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@include('partners.create')
@include('partners.show')
@include('partners.edit')

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // ==================== CREATE MODAL ====================
        $('#btn_create').on('click', function() {
            var modal = new bootstrap.Modal(document.getElementById('createModal'));
            $('#createPartnerForm')[0].reset();
            modal.show();
        });

        // ==================== SHOW MODAL ====================
        $(document).on('click', '.btn-show', function(e) {
            e.preventDefault();
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

            fetch('/partners/' + id, {
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
                                    <th class="ps-0" scope="row">Nr. Telefoni:</th>
                                    <td class="text-muted">${data.phone}</td>
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
            form.action = '/partners/' + id;

            fetch('/partners/' + id, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    modalBody.innerHTML = `
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="edit_name" class="form-label">Emri <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_name" name="name" value="${data.name}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_phone" class="form-label">Nr. Telefoni <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_phone" name="phone" value="${data.phone}" required>
                        </div>
                   
                    </div>
                `;
                })
                .catch(error => {
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

            Swal.fire({
                title: 'A jeni të sigurt?',
                text: "Ky partner do të fshihet përgjithmonë!",
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
                    form.action = '/partners/' + id;

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