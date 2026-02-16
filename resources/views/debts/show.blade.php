@extends('layouts.app')

@section('title', 'Detajet e Parave - ' . $debt->debt_number)

@push('styles')
<style>
    .debt-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        border-radius: 10px 10px 0 0;
    }

    .debt-header.overdue {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }

    .debt-header.paid {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }

    .info-card {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
    }

    .payment-item {
        border-left: 4px solid #28a745;
        background: #f8f9fa;
        padding: 15px;
        margin-bottom: 10px;
        border-radius: 5px;
        transition: all 0.3s;
    }

    .payment-item:hover {
        background: #e9ecef;
        transform: translateX(5px);
    }

    .timeline {
        position: relative;
        padding-left: 30px;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 8px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #dee2e6;
    }

    .timeline-item {
        position: relative;
        padding-bottom: 20px;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: -26px;
        top: 5px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #28a745;
        border: 2px solid white;
        box-shadow: 0 0 0 2px #28a745;
    }

    @media print {
        .no-print {
            display: none !important;
        }
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">💰 Detajet e Borxhit</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('debts.index') }}">Borxhet</a></li>
                    <li class="breadcrumb-item active">{{ $debt->debt_number }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Debt Header -->
        <div class="card">
            <div class="debt-header 
                @if($debt->is_overdue) overdue 
                @elseif($debt->status == 'paid') paid 
                @endif">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3 class="mb-2">{{ $debt->debt_number }}</h3>
                        <p class="mb-0 opacity-75">
                            <i class="ri-calendar-line me-2"></i>
                            {{ $debt->debt_date->format('d/m/Y') }}
                        </p>
                    </div>
                    <div class="text-end">
                        @if($debt->status == 'paid')
                        <span class="badge bg-light text-success fs-6">
                            <i class="ri-checkbox-circle-line me-1"></i>
                            E Paguar Plotësisht
                        </span>
                        @elseif($debt->status == 'partial')
                        <span class="badge bg-light text-info fs-6">
                            <i class="ri-time-line me-1"></i>
                            Pjesërisht e Paguar
                        </span>
                        @else
                        <span class="badge bg-light text-danger fs-6">
                            <i class="ri-close-circle-line me-1"></i>
                            E Papaguar
                        </span>
                        @endif

                        @if($debt->is_overdue)
                        <br>
                        <span class="badge bg-danger mt-2">
                            <i class="ri-alarm-warning-line me-1"></i>
                            SKADUAR {{ $debt->due_date->diffForHumans() }}
                        </span>
                        @elseif($debt->is_due_soon)
                        <br>
                        <span class="badge bg-warning mt-2">
                            <i class="ri-time-line me-1"></i>
                            Afrohet afati ({{ $debt->due_date->diffForHumans() }})
                        </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Debt Details -->
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="info-card">
                            <label class="text-muted small mb-1">FURNIZUESI</label>
                            <h6 class="mb-0">{{ $debt->supplier->name }}</h6>
                            @if($debt->supplier->phone)
                            <small class="text-muted">
                                <i class="ri-phone-line me-1"></i>
                                {{ $debt->supplier->phone }}
                            </small>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-card">
                            <label class="text-muted small mb-1">MAGAZINA</label>
                            <h6 class="mb-0">{{ $debt->warehouse->name }}</h6>
                            @if($debt->warehouse->location)
                            <small class="text-muted">
                                <i class="ri-map-pin-line me-1"></i>
                                {{ $debt->warehouse->location }}
                            </small>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Financial Summary -->
                <div class="border rounded p-4 mb-4" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <p class="text-muted mb-2">Shuma Totale</p>
                            <h4 class="mb-0">{{ number_format($debt->total_amount, 2) }} {{ $debt->currency->symbol }}</h4>
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted mb-2">E Paguar</p>
                            <h4 class="mb-0 text-success">{{ number_format($debt->paid_amount, 2) }} {{ $debt->currency->symbol }}</h4>
                        </div>
                        <div class="col-md-4">
                            <p class="text-muted mb-2">Mbetet</p>
                            <h4 class="mb-0 text-danger">{{ number_format($debt->remaining_amount, 2) }} {{ $debt->currency->symbol }}</h4>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    @if($debt->total_amount > 0)
                    @php
                    $percentage = ($debt->paid_amount / $debt->total_amount) * 100;
                    @endphp
                    <div class="mt-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Progresi i Pagesës</span>
                            <span class="text-muted small">{{ number_format($percentage, 1) }}%</span>
                        </div>
                        <div class="progress" style="height: 12px;">
                            <div class="progress-bar {{ $percentage == 100 ? 'bg-success' : 'bg-info' }}"
                                role="progressbar"
                                style="width: {{ $percentage }}%">
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Additional Info -->
                @if($debt->description)
                <div class="mb-3">
                    <h6 class="mb-2">
                        <i class="ri-file-text-line me-2"></i>
                        Përshkrimi
                    </h6>
                    <p class="text-muted mb-0">{{ $debt->description }}</p>
                </div>
                @endif

                @if($debt->notes)
                <div class="mb-3">
                    <h6 class="mb-2">
                        <i class="ri-sticky-note-line me-2"></i>
                        Shënime
                    </h6>
                    <p class="text-muted mb-0">{{ $debt->notes }}</p>
                </div>
                @endif

                <!-- Dates -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-card">
                            <small class="text-muted">Data e Borxhit</small>
                            <p class="mb-0 fw-bold">{{ $debt->debt_date->format('d/m/Y') }}</p>
                        </div>
                    </div>
                    @if($debt->due_date)
                    <div class="col-md-6">
                        <div class="info-card {{ $debt->is_overdue ? 'border-danger' : '' }}">
                            <small class="text-muted">Afati i Pagesës</small>
                            <p class="mb-0 fw-bold {{ $debt->is_overdue ? 'text-danger' : '' }}">
                                {{ $debt->due_date->format('d/m/Y') }}
                                @if($debt->is_overdue)
                                <span class="badge bg-danger ms-2">Skaduar</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Actions -->
                <div class="mt-4 d-flex gap-2 no-print">
                    @if($debt->status != 'paid')
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#paymentModal">
                        <i class="ri-money-dollar-circle-line me-1"></i> Shto Pagesë
                    </button>
                    @endif
                    <a href="{{ route('debts.edit', $debt->id) }}" class="btn btn-primary">
                        <i class="ri-pencil-line me-1"></i> Modifiko
                    </a>
                    <button onclick="window.print()" class="btn btn-info">
                        <i class="ri-printer-line me-1"></i> Print
                    </button>
                    <a href="{{ route('debts.index') }}" class="btn btn-secondary">
                        <i class="ri-arrow-left-line me-1"></i> Kthehu
                    </a>
                </div>
            </div>
        </div>

        <!-- Payment History -->
        @if($debt->payments->count() > 0)
        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="ri-history-line me-2"></i>
                    Historiku i Pagesave ({{ $debt->payments->count() }})
                </h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    @foreach($debt->payments->sortByDesc('payment_date') as $payment)
                    <div class="timeline-item">
                        <div class="payment-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">
                                        {{ number_format($payment->amount, 2) }} {{ $debt->currency->symbol }}
                                    </h6>
                                    <p class="mb-1 text-muted small">
                                        <i class="ri-calendar-line me-1"></i>
                                        {{ $payment->payment_date->format('d/m/Y') }}
                                    </p>
                                    <span class="badge {{ $payment->payment_method == 'Cash' ? 'bg-success' : 'bg-primary' }}">
                                        {{ $payment->payment_method }}
                                    </span>
                                    @if($payment->notes)
                                    <p class="mb-0 mt-2 small text-muted">
                                        <i class="ri-message-3-line me-1"></i>
                                        {{ $payment->notes }}
                                    </p>
                                    @endif
                                </div>
                                <small class="text-muted">
                                    {{ $payment->created_at->diffForHumans() }}
                                </small>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Quick Stats -->
        <div class="card">
            <div class="card-body">
                <h6 class="card-title mb-3">
                    <i class="ri-bar-chart-line me-2"></i>
                    Statistika
                </h6>

                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Numri i Pagesave:</span>
                        <strong>{{ $debt->payments->count() }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Pagesa Mesatare:</span>
                        <strong>
                            {{ $debt->payments->count() > 0 ? number_format($debt->payments->avg('amount'), 2) : '0.00' }} {{ $debt->currency->symbol }}
                        </strong>
                    </div>
                    @if($debt->due_date)
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Ditë deri në afat:</span>
                        <strong class="{{ $debt->is_overdue ? 'text-danger' : '' }}">
                            {{ $debt->is_overdue ? 'Skaduar' : now()->diffInDays($debt->due_date) . ' ditë' }}
                        </strong>
                    </div>
                    @endif
                </div>

                <div class="alert {{ $debt->status == 'paid' ? 'alert-success' : 'alert-warning' }} mb-0">
                    <i class="ri-information-line me-2"></i>
                    <small>
                        @if($debt->status == 'paid')
                        Ky borxh është paguar plotësisht.
                        @else
                        Keni {{ number_format($debt->remaining_amount, 2) }} {{ $debt->currency->symbol }} për të paguar.
                        @endif
                    </small>
                </div>
            </div>
        </div>

        <!-- Timestamps -->
        <div class="card mt-3">
            <div class="card-body">
                <h6 class="card-title mb-3">
                    <i class="ri-time-line me-2"></i>
                    Informacion Shtesë
                </h6>
                <div class="small">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Krijuar:</span>
                        <strong>{{ $debt->created_at->format('d/m/Y H:i') }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Përditësuar:</span>
                        <strong>{{ $debt->updated_at->format('d/m/Y H:i') }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="ri-money-dollar-circle-line me-2"></i>
                    Shto Pagesë
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('debts.add-payment', $debt->id) }}" method="POST" id="paymentForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <div class="d-flex justify-content-between">
                            <strong>Mbetet për paguar:</strong>
                            <strong class="text-danger">{{ number_format($debt->remaining_amount, 2) }} {{ $debt->currency->symbol }}</strong>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Shuma <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number"
                                class="form-control"
                                name="amount"
                                id="payment_amount"
                                step="0.01"
                                max="{{ $debt->remaining_amount }}"
                                placeholder="0.00"
                                required>
                            <span class="input-group-text">{{ $debt->currency->symbol }}</span>
                        </div>
                        <small class="text-muted">
                            Maksimumi: {{ number_format($debt->remaining_amount, 2) }} {{ $debt->currency->symbol }}
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Data e Pagesës <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="payment_date" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mënyra e Pagesës <span class="text-danger">*</span></label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" value="Cash" id="cash" checked>
                                <label class="form-check-label" for="cash">
                                    <i class="ri-money-dollar-circle-line me-1"></i> Cash
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" value="Bank" id="bank">
                                <label class="form-check-label" for="bank">
                                    <i class="ri-bank-line me-1"></i> Bank
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Shënime</label>
                        <textarea class="form-control" name="notes" rows="2" placeholder="Shënime shtesë (opsionale)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mbyll</button>
                    <button type="submit" class="btn btn-success">
                        <i class="ri-save-line me-1"></i> Ruaj Pagesën
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        // Quick amount buttons
        $('#paymentForm').prepend(`
            <div class="mb-3">
                <label class="form-label small text-muted">Shpejt:</label>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary quick-amount" data-amount="{{ $debt->remaining_amount }}">
                        Totali
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary quick-amount" data-amount="{{ $debt->remaining_amount / 2 }}">
                        Gjysma
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary quick-amount" data-amount="{{ $debt->remaining_amount / 4 }}">
                        1/4
                    </button>
                </div>
            </div>
        `);

        $(document).on('click', '.quick-amount', function() {
            const amount = $(this).data('amount');
            $('#payment_amount').val(parseFloat(amount).toFixed(2));
        });

        // Form validation
        $('#paymentForm').on('submit', function(e) {
            const amount = parseFloat($('#payment_amount').val());
            const remaining = {
                {
                    $debt - > remaining_amount
                }
            };

            if (amount > remaining) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Gabim!',
                    text: 'Shuma e paguar nuk mund të jetë më e madhe se shuma e mbetur!'
                });
                return false;
            }

            if (amount <= 0) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Gabim!',
                    text: 'Shuma duhet të jetë më e madhe se 0!'
                });
                return false;
            }
        });
    });

    @if(session('success'))
    Swal.fire({
        icon: 'success',
        title: 'Sukses!',
        text: '{{ session("success") }}',
        timer: 3000,
        showConfirmButton: false
    });
    @endif

    @if(session('error'))
    Swal.fire({
        icon: 'error',
        title: 'Gabim!',
        text: '{{ session("error") }}'
    });
    @endif
</script>
@endpush