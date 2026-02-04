@extends('layouts.app')

@section('title', 'Currencies')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Currencies</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Currencies</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1">Lista e Currencies</h5>
                <div>
                    <button type="button" class="btn btn-success" id="btn_create_currency">
                        <i class="ri-add-line align-middle me-1"></i> Shto Currency
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
                                <th scope="col">Kodi</th>
                                <th scope="col">Simboli</th>
                                <th scope="col">Exchange Rate</th>
                                <th scope="col" style="width: 180px;">Krijuar më</th>
                                <th scope="col" style="width: 150px;">Veprime</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($currencies as $currency)
                            <tr>
                                <td class="fw-medium">{{ $currency->id }}</td>
                                <td><strong>{{ $currency->code }}</strong></td>
                                <td>{{ $currency->symbol }}</td>
                                <td>{{ number_format($currency->exchange_rate, 4) }}</td>
                                <td>{{ $currency->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="hstack gap-1">
                                        <button type="button"
                                            class="btn btn-sm btn-info btn-show-currency" data-id="{{ $currency->id }}"
                                            title="Shiko">
                                            <i class="ri-eye-line"></i>
                                        </button>

                                        <button type="button"
                                            class="btn btn-sm btn-primary btn-edit-currency" data-id="{{ $currency->id }}"
                                            title="Modifiko">
                                            <i class="ri-pencil-line"></i>
                                        </button>

                                        <button type="button"
                                            class="btn btn-sm btn-danger btn-delete-currency" data-id="{{ $currency->id }}"
                                            title="Fshij">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="ri-money-dollar-box-line fs-1 d-block mb-2"></i>
                                    Nuk ka currency të regjistruar.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($currencies->hasPages())
                <div class="mt-3">
                    {{ $currencies->links('pagination::bootstrap-5') }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@include('currencies.create')
@include('currencies.show')
@include('currencies.edit')

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // ==================== CREATE MODAL ====================
        $('#btn_create_currency').on('click', function() {
            var modal = new bootstrap.Modal(document.getElementById('createCurrencyModal'));
            $('#createCurrencyForm')[0].reset();
            modal.show();
        });

        // ==================== SHOW MODAL ====================
        $(document).on('click', '.btn-show-currency', function() {
            var id = $(this).data('id');

            const modal = new bootstrap.Modal(document.getElementById('showCurrencyModal'));
            const modalBody = document.getElementById('showCurrencyModalBody');

            modalBody.innerHTML = `
            <div class="text-center">
                <div class="spinner-border text-info" role="status">
                    <span class="visually-hidden">Duke ngarkuar...</span>
                </div>
            </div>
        `;

            modal.show();

            fetch('/currencies/' + id, {
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
                                    <th class="ps-0" scope="row">Kodi:</th>
                                    <td class="text-muted"><strong>${data.code}</strong></td>
                                </tr>
                                <tr>
                                    <th class="ps-0" scope="row">Simboli:</th>
                                    <td class="text-muted">${data.symbol}</td>
                                </tr>
                                <tr>
                                    <th class="ps-0" scope="row">Exchange Rate:</th>
                                    <td class="text-muted"><strong>${parseFloat(data.exchange_rate).toFixed(4)}</strong></td>
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

        // ==================== EDIT MODAL ====================
        $(document).on('click', '.btn-edit-currency', function() {
            var id = $(this).data('id');

            const modal = new bootstrap.Modal(document.getElementById('editCurrencyModal'));
            const modalBody = document.getElementById('editCurrencyModalBody');
            const form = document.getElementById('editCurrencyForm');

            modalBody.innerHTML = `
            <div class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Duke ngarkuar...</span>
                </div>
            </div>
        `;

            modal.show();
            form.action = '/currencies/' + id;

            fetch('/currencies/' + id, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    modalBody.innerHTML = `
                    <div class="mb-3">
                        <label for="edit_code" class="form-label">Kodi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_code" name="code" value="${data.code}" maxlength="3" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_symbol" class="form-label">Simboli <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_symbol" name="symbol" value="${data.symbol}" maxlength="5" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_exchange_rate" class="form-label">Exchange Rate <span class="text-danger">*</span></label>
                        <input type="number" step="0.0001" class="form-control" id="edit_exchange_rate" name="exchange_rate" value="${data.exchange_rate}" min="0" required>
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

        // ==================== DELETE MODAL ====================
        $(document).on('click', '.btn-delete-currency', function() {
            var id = $(this).data('id');

            Swal.fire({
                title: 'A jeni të sigurt?',
                text: "Ky currency do të fshihet përgjithmonë!",
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
                    form.action = '/currencies/' + id;

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