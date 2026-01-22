@extends('layouts.app')

@section('title', 'Blerjet')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Blerjet</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Blerjet</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1">Lista e Blerjeve</h5>
                <div>
                    <a href="{{ route('purchases.create') }}" class="btn btn-success">
                        <i class="ri-add-line align-middle me-1"></i> Krijo Blerje
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

                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="ri-error-warning-line align-middle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-nowrap align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" style="width: 100px;">Nr. Blerjes</th>
                                <th scope="col">Data</th>
                                <th scope="col">Partneri</th>
                                <th scope="col">Warehouse</th>
                                <th scope="col">Statusi</th>
                                <th scope="col">Pagesa</th>
                                <th scope="col">Totali</th>
                                <th scope="col" style="width: 150px;">Veprime</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($purchases as $purchase)
                            <tr>
                                <td class="fw-medium">{{ $purchase->purchase_number }}</td>
                                <td>{{ $purchase->purchase_date->format('d/m/Y') }}</td>
                                <td>{{ $purchase->partner->name }}</td>
                                <td>{{ $purchase->warehouse->name }}</td>
                                <td>
                                    @if($purchase->order_status === 'Received')
                                    <span class="badge bg-success">Received</span>
                                    @elseif($purchase->order_status === 'Pending')
                                    <span class="badge bg-warning">Pending</span>
                                    @else
                                    <span class="badge bg-danger">Cancelled</span>
                                    @endif
                                </td>
                                <td>
                                    @if($purchase->payment_status === 'Paid')
                                    <span class="badge bg-success">Paid</span>
                                    @elseif($purchase->payment_status === 'Partial')
                                    <span class="badge bg-warning">Partial</span>
                                    @else
                                    <span class="badge bg-danger">Unpaid</span>
                                    @endif
                                </td>
                                <td><strong>{{ number_format($purchase->total_amount, 2) }} {{ $purchase->currency->symbol }}</strong></td>
                                <td>
                                    <div class="hstack gap-1">
                                        <a href="{{ route('purchases.show', $purchase->id) }}"
                                            class="btn btn-sm btn-info"
                                            title="Shiko">
                                            <i class="ri-eye-line"></i>
                                        </a>

                                        <a href="{{ route('purchases.edit', $purchase->id) }}"
                                            class="btn btn-sm btn-primary"
                                            title="Modifiko">
                                            <i class="ri-pencil-line"></i>
                                        </a>

                                        <button type="button"
                                            class="btn btn-sm btn-danger btn-delete"
                                            data-id="{{ $purchase->id }}"
                                            title="Fshij">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="ri-shopping-cart-line fs-1 d-block mb-2"></i>
                                    Nuk ka blerje të regjistruara.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($purchases->hasPages())
                <div class="mt-3">
                    {{ $purchases->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $(document).on('click', '.btn-delete', function(e) {
            e.preventDefault();
            var id = $(this).data('id');

            Swal.fire({
                title: 'A jeni të sigurt?',
                text: "Kjo blerje do të fshihet përgjithmonë!",
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
                    form.action = '/purchases/' + id;

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