@extends('layouts.app')

@section('title', 'Detajet e Porosisë')

@push('styles')
<style>
    .order-header {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
        padding: 30px;
        border-radius: 10px 10px 0 0;
    }

    .order-header.paid {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }

    .order-header.overdue {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }

    .info-card {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
    }

    .product-item {
        background: #f8f9fa;
        border-left: 4px solid #4facfe;
        padding: 15px;
        margin-bottom: 10px;
        border-radius: 5px;
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
            <h4 class="mb-sm-0">🌐 Detajet e Porosisë Online</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('online-orders.index') }}">Porositë Online</a></li>
                    <li class="breadcrumb-item active">{{ $onlineOrder->order_number }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Order Header -->
        <div class="card">
            <div class="order-header 
                @if($onlineOrder->is_paid) paid 
                @elseif($onlineOrder->is_overdue) overdue 
                @endif">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h3 class="mb-2">{{ $onlineOrder->order_number }}</h3>
                        <p class="mb-0 opacity-75">
                            <i class="ri-calendar-line me-2"></i>
                            {{ $onlineOrder->order_date->format('d/m/Y') }}
                        </p>
                    </div>
                    <div class="text-end">
                        @if($onlineOrder->is_paid)
                        <span class="badge bg-light text-success fs-6">
                            <i class="ri-checkbox-circle-line me-1"></i>
                            E Paguar
                        </span>
                        @else
                        <span class="badge bg-light text-danger fs-6">
                            <i class="ri-close-circle-line me-1"></i>
                            E Papaguar
                        </span>
                        @endif

                        @if($onlineOrder->is_overdue)
                        <br>
                        <span class="badge bg-danger mt-2">
                            <i class="ri-alarm-warning-line me-1"></i>
                            VONESE {{ $onlineOrder->expected_payment_date->diffForHumans() }}
                        </span>
                        @elseif($onlineOrder->is_due_soon)
                        <br>
                        <span class="badge bg-warning mt-2">
                            <i class="ri-time-line me-1"></i>
                            Afrohet ({{ $onlineOrder->expected_payment_date->diffForHumans() }})
                        </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Order Details -->
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="info-card">
                            <label class="text-muted small mb-1">KLIENTI</label>
                            <h6 class="mb-0">{{ $onlineOrder->partner->name }}</h6>
                            @if($onlineOrder->partner->phone)
                            <small class="text-muted">
                                <i class="ri-phone-line me-1"></i>
                                {{ $onlineOrder->partner->phone }}
                            </small>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-card">
                            <label class="text-muted small mb-1">MAGAZINA</label>
                            <h6 class="mb-0">{{ $onlineOrder->warehouse->name }}</h6>
                            @if($onlineOrder->warehouse->location)
                            <small class="text-muted">
                                <i class="ri-map-pin-line me-1"></i>
                                {{ $onlineOrder->warehouse->location }}
                            </small>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Amount Display -->
                <div class="border rounded p-4 mb-4 text-center" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                    <p class="text-muted mb-2">Shuma e Porosisë</p>
                    <h2 class="mb-0 {{ $onlineOrder->is_paid ? 'text-success' : 'text-danger' }}">
                        {{ number_format($onlineOrder->order_amount, 2) }} {{ $onlineOrder->currency->symbol }}
                    </h2>
                </div>

                <!-- Sale Reference -->
                <div class="mb-4">
                    <h6 class="mb-3">
                        <i class="ri-file-list-line me-2"></i>
                        Lidhur me Shitjen
                    </h6>
                    <div class="info-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $onlineOrder->sale->invoice_number }}</strong>
                                <br>
                                <small class="text-muted">
                                    Data: {{ $onlineOrder->sale->invoice_date->format('d/m/Y') }}
                                </small>
                            </div>
                            <a href="{{ route('sales.show', $onlineOrder->sale->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="ri-eye-line me-1"></i> Shiko Faturën
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Products from Sale -->
                @if($onlineOrder->sale->items->count() > 0)
                <div class="mb-4">
                    <h6 class="mb-3">
                        <i class="ri-shopping-bag-line me-2"></i>
                        Produktet ({{ $onlineOrder->sale->items->count() }})
                    </h6>
                    @foreach($onlineOrder->sale->items as $item)
                    <div class="product-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">{{ $item->product_name }}</h6>
                                @if($item->storage || $item->ram || $item->color)
                                <small class="text-muted">
                                    @if($item->storage){{ $item->storage }}@endif
                                    @if($item->ram) | {{ $item->ram }}@endif
                                    @if($item->color) | {{ $item->color }}@endif
                                </small>
                                @endif
                                <p class="mb-0 mt-1">
                                    <span class="badge bg-secondary">Qty: {{ $item->quantity }}</span>
                                </p>
                            </div>
                            <div class="text-end">
                                <strong>{{ number_format($item->line_total, 2) }} {{ $onlineOrder->currency->symbol }}</strong>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif

                <!-- Delivery Address -->
                @if($onlineOrder->delivery_address)
                <div class="mb-3">
                    <h6 class="mb-2">
                        <i class="ri-map-pin-line me-2"></i>
                        Adresa e Dërgesës
                    </h6>
                    <p class="text-muted mb-0">{{ $onlineOrder->delivery_address }}</p>
                </div>
                @endif

                <!-- Notes -->
                @if($onlineOrder->notes)
                <div class="mb-3">
                    <h6 class="mb-2">
                        <i class="ri-sticky-note-line me-2"></i>
                        Shënime
                    </h6>
                    <p class="text-muted mb-0">{{ $onlineOrder->notes }}</p>
                </div>
                @endif

                <!-- Dates -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-card">
                            <small class="text-muted">Data e Porosisë</small>
                            <p class="mb-0 fw-bold">{{ $onlineOrder->order_date->format('d/m/Y') }}</p>
                        </div>
                    </div>
                    @if($onlineOrder->expected_payment_date)
                    <div class="col-md-6">
                        <div class="info-card {{ $onlineOrder->is_overdue ? 'border-danger' : '' }}">
                            <small class="text-muted">Data e Pritshme e Pagesës</small>
                            <p class="mb-0 fw-bold {{ $onlineOrder->is_overdue ? 'text-danger' : '' }}">
                                {{ $onlineOrder->expected_payment_date->format('d/m/Y') }}
                                @if($onlineOrder->is_overdue)
                                <span class="badge bg-danger ms-2">Vonese</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Payment Info -->
                @if($onlineOrder->is_paid && $onlineOrder->payment_received_date)
                <div class="mt-3">
                    <div class="alert alert-success">
                        <div class="d-flex align-items-start">
                            <i class="ri-check-line fs-4 me-2"></i>
                            <div>
                                <strong>Paguar më {{ $onlineOrder->payment_received_date->format('d/m/Y') }}</strong>
                                @if($onlineOrder->payment_method)
                                <p class="mb-0">Mënyra: {{ $onlineOrder->payment_method }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Actions -->
                <div class="mt-4 d-flex gap-2 no-print">
                    @if(!$onlineOrder->is_paid)
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#paymentModal">
                        <i class="ri-check-line me-1"></i> Shëno si të Paguar
                    </button>
                    @else
                    <form action="{{ route('online-orders.mark-unpaid', $onlineOrder->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Jeni i sigurt që doni ta ktheni në E Papaguar?')">
                        @csrf
                        <button type="submit" class="btn btn-warning">
                            <i class="ri-close-line me-1"></i> Shëno si E Papaguar
                        </button>
                    </form>
                    @endif
                    <a href="{{ route('online-orders.edit', $onlineOrder->id) }}" class="btn btn-primary">
                        <i class="ri-pencil-line me-1"></i> Modifiko
                    </a>
                    <button onclick="window.print()" class="btn btn-info">
                        <i class="ri-printer-line me-1"></i> Print
                    </button>
                    <a href="{{ route('online-orders.index') }}" class="btn btn-secondary">
                        <i class="ri-arrow-left-line me-1"></i> Kthehu
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Quick Info -->
        <div class="card">
            <div class="card-body">
                <h6 class="card-title mb-3">
                    <i class="ri-information-line me-2"></i>
                    Informacion i Shpejtë
                </h6>

                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Statusi:</span>
                        <strong class="{{ $onlineOrder->is_paid ? 'text-success' : 'text-danger' }}">
                            {{ $onlineOrder->is_paid ? 'E Paguar' : 'E Papaguar' }}
                        </strong>
                    </div>
                    @if($onlineOrder->expected_payment_date && !$onlineOrder->is_paid)
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Ditë deri pritje:</span>
                        <strong class="{{ $onlineOrder->is_overdue ? 'text-danger' : '' }}">
                            {{ $onlineOrder->is_overdue ? 'Skaduar' : now()->diffInDays($onlineOrder->expected_payment_date) . ' ditë' }}
                        </strong>
                    </div>
                    @endif
                </div>

                <div class="alert {{ $onlineOrder->is_paid ? 'alert-success' : 'alert-warning' }} mb-0">
                    <i class="ri-information-line me-2"></i>
                    <small>
                        @if($onlineOrder->is_paid)
                        Kjo porosi është paguar nga posta.
                        @else
                        Në pritje të pagesës nga posta.
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
                        <strong>{{ $onlineOrder->created_at->format('d/m/Y H:i') }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Përditësuar:</span>
                        <strong>{{ $onlineOrder->updated_at->format('d/m/Y H:i') }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
@if(!$onlineOrder->is_paid)
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="ri-check-line me-2"></i>
                    Shëno si të Paguar
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('online-orders.mark-paid', $onlineOrder->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <div class="d-flex justify-content-between">
                            <strong>Shuma:</strong>
                            <strong class="text-success">{{ number_format($onlineOrder->order_amount, 2) }} {{ $onlineOrder->currency->symbol }}</strong>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Data e Marrjes së Pagesës <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="payment_received_date" value="{{ date('Y-m-d') }}" required>
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
                        <i class="ri-check-line me-1"></i> Konfirmo Pagesën
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
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