@extends('layouts.app')

@section('title', 'Menaxhimi i Pagesave')

@push('styles')
<style>
    .debt-card {
        transition: all 0.3s;
        border-left: 4px solid #dee2e6;
    }

    .debt-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .debt-card.overdue {
        border-left-color: #dc3545;
        background-color: rgba(220, 53, 69, 0.05);
    }

    .debt-card.due-soon {
        border-left-color: #ffc107;
        background-color: rgba(255, 193, 7, 0.05);
    }

    .debt-card.paid {
        border-left-color: #28a745;
        background-color: rgba(40, 167, 69, 0.05);
    }

    .debt-card.partial {
        border-left-color: #17a2b8;
        background-color: rgba(23, 162, 184, 0.05);
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
            <h4 class="mb-sm-0">💰 MENAXHIMI I PAGESAVE</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">PAGESAT</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row">
    @php
    $totalDebt = $debts->sum('total_amount');
    $totalPaid = $debts->sum('paid_amount');
    $totalRemaining = $debts->sum('remaining_amount');
    $overdueCount = $debts->filter(fn($d) => $d->is_overdue)->count();
    @endphp

    @if(!empty($totalsByCurrency))
    @foreach($totalsByCurrency as $code => $data)
    <div class="col-lg-3 col-md-6">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1 opacity-75">Totale ({{ $code }})</p>
                    <h4 class="mb-0">{{ number_format($data['total'] ?? 0, 2) }} {{ $data['symbol'] }}</h4>
                    <small class="d-block mt-1">Paguar: {{ number_format($data['paid'] ?? 0, 2) }} {{ $data['symbol'] }} &middot; Mbetet: {{ number_format($data['remaining'] ?? 0, 2) }} {{ $data['symbol'] }}</small>
                </div>
                <i class="ri-money-dollar-circle-line" style="font-size: 3rem; opacity: 0.3;"></i>
            </div>
        </div>
    </div>
    @endforeach
    @else
    <div class="col-lg-3 col-md-6">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1 opacity-75">Pagesa Total</p>
                    <h4 class="mb-0">{{ number_format($totalDebt, 2) }} L</h4>
                </div>
                <i class="ri-money-dollar-circle-line" style="font-size: 3rem; opacity: 0.3;"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stats-card success">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1 opacity-75">E Paguar</p>
                    <h4 class="mb-0">{{ number_format($totalPaid, 2) }} L</h4>
                </div>
                <i class="ri-checkbox-circle-line" style="font-size: 3rem; opacity: 0.3;"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stats-card danger">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1 opacity-75">Mbetet për Paguar</p>
                    <h4 class="mb-0">{{ number_format($totalRemaining, 2) }} L</h4>
                </div>
                <i class="ri-alert-line" style="font-size: 3rem; opacity: 0.3;"></i>
            </div>
        </div>
    </div>
    @endif

    <div class="col-lg-3 col-md-6">
        <div class="stats-card warning">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1">Pagesa të Skaduara</p>
                    <h4 class="mb-0">{{ $overdueCount }}</h4>
                    <small>Kërkon vëmendje!</small>
                </div>
                <i class="ri-time-line" style="font-size: 3rem; opacity: 0.3;"></i>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">Lista e Pagesave</h5>
                    <a href="{{ route('debts.create') }}" class="btn btn-primary">
                        <i class="ri-add-line me-1"></i> Shto Pagesë të Re
                    </a>
                </div>

                <!-- Filter Form -->
                <form method="GET" action="{{ route('debts.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <select class="form-select" name="status">
                            <option value="">Të gjitha</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>E Papaguar</option>
                            <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Pjesërisht e Paguar</option>
                            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>E Paguar Plotësisht</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <select class="form-select" name="supplier_id">
                            <option value="">Të gjithë Furnizuesit</option>
                            @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <select class="form-select" name="warehouse_id">
                            <option value="">Të gjitha Magazinat</option>
                            @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="overdue" id="overdue" value="1" {{ request('overdue') ? 'checked' : '' }}>
                            <label class="form-check-label text-danger fw-bold" for="overdue">
                                Vetëm të Skaduara
                            </label>
                        </div>
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

<!-- Debts List -->
<div class="row">
    @forelse($debts as $debt)
    <div class="col-lg-6 col-xl-4">
        <div class="card debt-card 
            @if($debt->is_overdue) overdue 
            @elseif($debt->is_due_soon) due-soon 
            @elseif($debt->status == 'paid') paid 
            @elseif($debt->status == 'partial') partial 
            @endif">
            <div class="card-body">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="mb-1">
                            <a href="{{ route('debts.show', $debt->id) }}" class="text-dark">
                                {{ $debt->debt_number }}
                            </a>
                        </h6>
                        <small class="text-muted">
                            <i class="ri-calendar-line me-1"></i>
                            {{ $debt->debt_date->format('d/m/Y') }}
                        </small>
                    </div>
                    <div>
                        @if($debt->status == 'paid')
                        <span class="badge bg-success">E Paguar</span>
                        @elseif($debt->status == 'partial')
                        <span class="badge bg-info">Pjesërisht</span>
                        @else
                        <span class="badge bg-danger">E Papaguar</span>
                        @endif

                        @if($debt->is_overdue)
                        <span class="badge bg-danger ms-1">
                            <i class="ri-alarm-warning-line"></i> SKADUAR
                        </span>
                        @elseif($debt->is_due_soon)
                        <span class="badge bg-warning ms-1">
                            <i class="ri-time-line"></i> Afrohet Afati
                        </span>
                        @endif
                    </div>
                </div>

                <!-- Supplier & Warehouse -->
                <div class="mb-3">
                    <p class="mb-1">
                        <i class="ri-user-line me-1"></i>
                        <strong>{{ $debt->supplier->name }}</strong>
                    </p>
                    <p class="mb-0 text-muted small">
                        <i class="ri-store-line me-1"></i>
                        {{ $debt->warehouse->name }}
                    </p>
                </div>

                <!-- Amounts -->
                <div class="border-top pt-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Shuma Totale:</span>
                        <strong>{{ number_format($debt->total_amount, 2) }} {{ $debt->currency->symbol }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-success">E Paguar:</span>
                        <strong class="text-success">{{ number_format($debt->paid_amount, 2) }} {{ $debt->currency->symbol }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-danger">Mbetet:</span>
                        <strong class="text-danger">{{ number_format($debt->remaining_amount, 2) }} {{ $debt->currency->symbol }}</strong>
                    </div>
                </div>

                <!-- Progress Bar -->
                @if($debt->total_amount > 0)
                @php
                $percentage = ($debt->paid_amount / $debt->total_amount) * 100;
                @endphp
                <div class="progress mt-3" style="height: 8px;">
                    <div class="progress-bar {{ $percentage == 100 ? 'bg-success' : 'bg-info' }}"
                        role="progressbar"
                        style="width: {{ $percentage }}%;"
                        aria-valuenow="{{ $percentage }}"
                        aria-valuemin="0"
                        aria-valuemax="100">
                    </div>
                </div>
                <small class="text-muted">{{ number_format($percentage, 1) }}% e paguar</small>
                @endif

                <!-- Due Date -->
                @if($debt->due_date)
                <div class="mt-3 pt-3 border-top">
                    <small class="text-muted">
                        <i class="ri-calendar-check-line me-1"></i>
                        Afati: <strong>{{ $debt->due_date->format('d/m/Y') }}</strong>
                        @if($debt->is_overdue)
                        <span class="text-danger">({{ $debt->due_date->diffForHumans() }})</span>
                        @elseif($debt->is_due_soon)
                        <span class="text-warning">({{ $debt->due_date->diffForHumans() }})</span>
                        @endif
                    </small>
                </div>
                @endif

                <!-- Actions -->
                <div class="mt-3 d-flex gap-2">
                    <a href="{{ route('debts.show', $debt->id) }}" class="btn btn-sm btn-outline-primary flex-fill">
                        <i class="ri-eye-line me-1"></i> Detaje
                    </a>
                    @if($debt->status != 'paid')
                    <button type="button" class="btn btn-sm btn-success flex-fill"
                        data-bs-toggle="modal"
                        data-bs-target="#paymentModal{{ $debt->id }}">
                        <i class="ri-money-dollar-circle-line me-1"></i> Paguaj
                    </button>
                    @endif
                    <a href="{{ route('debts.edit', $debt->id) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="ri-pencil-line"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal{{ $debt->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="ri-money-dollar-circle-line me-2"></i>
                        Shto Pagesë - {{ $debt->debt_number }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('debts.add-payment', $debt->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <strong>Mbetet për paguar:</strong> {{ number_format($debt->remaining_amount, 2) }} {{ $debt->currency->symbol }}
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Shuma <span class="text-danger">*</span></label>
                            <input type="number"
                                class="form-control"
                                name="amount"
                                step="0.01"
                                max="{{ $debt->remaining_amount }}"
                                placeholder="0.00"
                                required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Data e Pagesës <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="payment_date" value="{{ date('Y-m-d') }}" required>
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
                            <textarea class="form-control" name="notes" rows="2"></textarea>
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
    @empty
    <div class="col-12">
        <div class="alert alert-info text-center">
            <i class="ri-information-line me-2"></i>
            Nuk ka pagese të regjistruara.
        </div>
    </div>
    @endforelse
</div>

<!-- Pagination -->
@if($debts->hasPages())
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-center">
            {{ $debts->links('pagination::bootstrap-5') }}
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