@extends('layouts.app')

@section('title', 'Porositë Online')

@push('styles')
<style>
    .order-card {
        transition: all 0.3s;
        border-left: 4px solid #dee2e6;
    }

    .order-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .order-card.unpaid {
        border-left-color: #dc3545;
        background-color: rgba(220, 53, 69, 0.05);
    }

    .order-card.overdue {
        border-left-color: #dc3545;
        background-color: rgba(220, 53, 69, 0.1);
        animation: pulse 2s infinite;
    }

    @keyframes pulse {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.85;
        }
    }

    .order-card.paid {
        border-left-color: #28a745;
        background-color: rgba(40, 167, 69, 0.05);
    }

    .order-card.due-soon {
        border-left-color: #ffc107;
        background-color: rgba(255, 193, 7, 0.05);
    }

    .stats-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .stats-card.danger {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }

    .stats-card.warning {
        background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
        color: #333;
    }

    .stats-card.success {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">🌐 POROSITË ONLINE</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Porositë Online</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row">
    @php
    $totalOrders = $orders->total();
    $paidCount = $orders->where('is_paid', true)->count();
    $unpaidCount = $orders->where('is_paid', false)->count();
    $overdueCount = $orders->filter(fn($o) => $o->is_overdue)->count();
    @endphp

    <div class="col-lg-3 col-md-6">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1 opacity-75">Porosi Totale</p>
                    <h4 class="mb-0">{{ $totalOrders }}</h4>
                </div>
                <i class="ri-global-line" style="font-size: 3rem; opacity: 0.3;"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stats-card success">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1 opacity-75">E Paguar</p>
                    <h4 class="mb-0">{{ $paidCount }}</h4>
                </div>
                <i class="ri-checkbox-circle-line" style="font-size: 3rem; opacity: 0.3;"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stats-card danger">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1 opacity-75">E Papaguar</p>
                    <h4 class="mb-0">{{ $unpaidCount }}</h4>
                </div>
                <i class="ri-close-circle-line" style="font-size: 3rem; opacity: 0.3;"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stats-card warning">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1">Vonesa në Pagesë</p>
                    <h4 class="mb-0">{{ $overdueCount }}</h4>
                    <small>Kërkon vëmendje!</small>
                </div>
                <i class="ri-alarm-warning-line" style="font-size: 3rem; opacity: 0.3;"></i>
            </div>
        </div>
    </div>
</div>

@if(!empty($unpaidTotals))
<!-- Unpaid totals by currency -->
<div class="row mt-3">
    @foreach($unpaidTotals as $code => $data)
    <div class="col-lg-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1 opacity-75">Papaguar ({{ $code }})</p>
                        <h4 class="mb-0">
                            {{ number_format($data['total'], 2) }} {{ $data['symbol'] }}
                        </h4>
                    </div>
                    <i class="ri-wallet-line" style="font-size: 2.5rem; opacity: 0.25;"></i>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

<!-- Filters & Actions -->
<div class="row mb-3">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">Lista e Porosive</h5>
                    <a href="{{ route('online-orders.create') }}" class="btn btn-primary">
                        <i class="ri-add-line me-1"></i> Shto Porosi të Re
                    </a>
                </div>

                <!-- Filter Form -->
                <form method="GET" action="{{ route('online-orders.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <input type="text"
                            class="form-control"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Kërko sipas numrit...">
                    </div>

                    <div class="col-md-3">
                        <select class="form-select" name="status">
                            <option value="">Të gjitha</option>
                            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>E Paguar</option>
                            <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>E Papaguar</option>
                            <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Me Vonesa</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <select class="form-select" name="partner_id">
                            <option value="">Të gjithë Klientët</option>
                            @foreach($partners as $partner)
                            <option value="{{ $partner->id }}" {{ request('partner_id') == $partner->id ? 'selected' : '' }}>
                                {{ $partner->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <select class="form-select" name="warehouse_id">
                            <option value="">Të gjitha Magazinat</option>
                            @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ri-filter-line"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Orders List -->
<div class="row">
    @forelse($orders as $order)
    <div class="col-lg-6 col-xl-4">
        <div class="card order-card 
            @if($order->is_paid) paid 
            @elseif($order->is_overdue) overdue 
            @elseif($order->is_due_soon) due-soon 
            @else unpaid 
            @endif">
            <div class="card-body">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="mb-1">
                            <a href="{{ route('online-orders.show', $order->id) }}" class="text-dark">
                                {{ $order->order_number }}
                            </a>
                        </h6>
                        <small class="text-muted">
                            <i class="ri-calendar-line me-1"></i>
                            {{ $order->order_date->format('d/m/Y') }}
                        </small>
                    </div>
                    <div>
                        @if($order->is_paid)
                        <span class="badge bg-success">
                            <i class="ri-checkbox-circle-line me-1"></i> E Paguar
                        </span>
                        @else
                        <span class="badge bg-danger">
                            <i class="ri-close-circle-line me-1"></i> E Papaguar
                        </span>
                        @endif

                        @if($order->is_overdue)
                        <br>
                        <span class="badge bg-danger mt-1">
                            <i class="ri-alarm-warning-line"></i> VONESE
                        </span>
                        @elseif($order->is_due_soon)
                        <br>
                        <span class="badge bg-warning mt-1">
                            <i class="ri-time-line"></i> Afrohet
                        </span>
                        @endif
                    </div>
                </div>

                <!-- Client & Warehouse -->
                <div class="mb-3">
                    <p class="mb-1">
                        <i class="ri-user-line me-1"></i>
                        <strong>{{ $order->partner->name }}</strong>
                    </p>
                    <p class="mb-0 text-muted small">
                        <i class="ri-store-line me-1"></i>
                        {{ $order->warehouse->name }}
                    </p>
                    @if($order->delivery_address)
                    <p class="mb-0 text-muted small">
                        <i class="ri-map-pin-line me-1"></i>
                        {{ Str::limit($order->delivery_address, 30) }}
                    </p>
                    @endif
                </div>

                <!-- Amount -->
                <div class="border-top pt-3 mb-3">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Shuma:</span>
                        <h5 class="mb-0 {{ $order->is_paid ? 'text-success' : 'text-danger' }}">
                            {{ number_format($order->order_amount, 2) }} {{ $order->currency->symbol }}
                        </h5>
                    </div>
                </div>

                <!-- Expected Payment Date -->
                @if($order->expected_payment_date && !$order->is_paid)
                <div class="mb-3">
                    <small class="text-muted">
                        <i class="ri-calendar-check-line me-1"></i>
                        Pritej: <strong>{{ $order->expected_payment_date->format('d/m/Y') }}</strong>
                        @if($order->is_overdue)
                        <span class="text-danger">({{ $order->expected_payment_date->diffForHumans() }})</span>
                        @elseif($order->is_due_soon)
                        <span class="text-warning">({{ $order->expected_payment_date->diffForHumans() }})</span>
                        @endif
                    </small>
                </div>
                @endif

                <!-- Payment Received Date -->
                @if($order->is_paid && $order->payment_received_date)
                <div class="mb-3">
                    <small class="text-success">
                        <i class="ri-check-line me-1"></i>
                        Paguar më: <strong>{{ $order->payment_received_date->format('d/m/Y') }}</strong>
                        @if($order->payment_method)
                        ({{ $order->payment_method }})
                        @endif
                    </small>
                </div>
                @endif

                <!-- Actions -->
                <div class="d-flex gap-2">
                    <a href="{{ route('online-orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary flex-fill">
                        <i class="ri-eye-line me-1"></i> Detaje
                    </a>
                    @if(!$order->is_paid)
                    <button type="button" class="btn btn-sm btn-success flex-fill"
                        data-bs-toggle="modal"
                        data-bs-target="#paymentModal{{ $order->id }}">
                        <i class="ri-check-line me-1"></i> Shëno si të Paguar
                    </button>
                    @endif
                    <a href="{{ route('online-orders.edit', $order->id) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="ri-pencil-line"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    @if(!$order->is_paid)
    <div class="modal fade" id="paymentModal{{ $order->id }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="ri-check-line me-2"></i>
                        Shëno si të Paguar - {{ $order->order_number }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('online-orders.mark-paid', $order->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <strong>Shuma:</strong> {{ number_format($order->order_amount, 2) }} {{ $order->currency->symbol }}
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Data e Marrjes së Pagesës <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="payment_received_date" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mënyra e Pagesës <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" value="Cash" checked>
                                    <label class="form-check-label">Cash</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" value="Bank">
                                    <label class="form-check-label">Bank</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Shënime</label>
                            <textarea class="form-control" name="notes" rows="2" placeholder="Shënime shtesë..."></textarea>
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
    @empty
    <div class="col-12">
        <div class="alert alert-info text-center">
            <i class="ri-information-line me-2"></i>
            Nuk ka porosi online të regjistruara.
        </div>
    </div>
    @endforelse
</div>

<!-- Pagination -->
@if($orders->hasPages())
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-center">
            {{ $orders->links('pagination::bootstrap-5') }}
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