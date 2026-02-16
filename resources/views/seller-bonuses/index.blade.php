@extends('layouts.app')

@section('title', 'Bonuset e Punëtorëve')

@push('styles')
<style>
    .bonus-card {
        transition: all 0.3s;
        border-left: 4px solid #667eea;
        background: #fff;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .bonus-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .stats-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .stats-card.phone {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }

    .stats-card.accessory {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }

    .stats-card.total {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">💰 BONUSET E PUNËTORËVE</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Bonuset</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row">
    @php
    $totalBonuses = $bonuses->sum('total_bonus');
    $totalPhoneBonuses = $bonuses->sum('phone_bonus_amount');
    $totalAccessoryBonuses = $bonuses->sum('accessory_bonus_amount');
    @endphp

    <div class="col-lg-3 col-md-6">
        <div class="stats-card total">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1 opacity-75">Bonus Total</p>
                    <h4 class="mb-0">{{ number_format($totalBonuses, 2) }} L</h4>
                </div>
                <i class="ri-money-dollar-circle-line" style="font-size: 3rem; opacity: 0.3;"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stats-card phone">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1 opacity-75">Bonus Telefonat</p>
                    <h4 class="mb-0">{{ number_format($totalPhoneBonuses, 2) }} L</h4>
                </div>
                <i class="ri-smartphone-line" style="font-size: 3rem; opacity: 0.3;"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stats-card accessory">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1 opacity-75">Bonus Aksesorë</p>
                    <h4 class="mb-0">{{ number_format($totalAccessoryBonuses, 2) }} L</h4>
                </div>
                <i class="ri-smartphone-line" style="font-size: 3rem; opacity: 0.3;"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1 opacity-75">Regjistrime</p>
                    <h4 class="mb-0">{{ $bonuses->total() }}</h4>
                </div>
                <i class="ri-file-list-line" style="font-size: 3rem; opacity: 0.3;"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filters & Actions -->
<div class="row mb-3">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">Lista e Bonuseve</h5>
                    <a href="{{ route('seller-bonuses.create') }}" class="btn btn-primary">
                        <i class="ri-add-line me-1"></i> Llogarit Bonus të Ri
                    </a>
                </div>

                <!-- Filter Form -->
                <form method="GET" action="{{ route('seller-bonuses.index') }}" class="row g-3">
                    <div class="col-md-4">
                        <select class="form-select" name="seller_id">
                            <option value="">Të gjithë Punëtorët</option>
                            @foreach($sellers as $seller)
                            <option value="{{ $seller->id }}" {{ request('seller_id') == $seller->id ? 'selected' : '' }}>
                                {{ $seller->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <input type="date"
                            class="form-control"
                            name="period_start"
                            value="{{ request('period_start') }}"
                            placeholder="Nga data...">
                    </div>

                    <div class="col-md-3">
                        <input type="date"
                            class="form-control"
                            name="period_end"
                            value="{{ request('period_end') }}"
                            placeholder="Deri në datë...">
                    </div>

                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ri-filter-line me-1"></i> Filtro
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Bonuses List -->
<div class="row">
    @forelse($bonuses as $bonus)
    <div class="col-lg-6">
        <div class="card bonus-card">
            <div class="card-body">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="mb-1">
                            <a href="{{ route('seller-bonuses.show', $bonus->id) }}" class="text-dark">
                                {{ $bonus->seller->name }}
                            </a>
                        </h5>
                        <small class="text-muted">
                            <i class="ri-calendar-line me-1"></i>
                            {{ $bonus->period_start->format('d/m/Y') }} - {{ $bonus->period_end->format('d/m/Y') }}
                        </small>
                    </div>
                    <div>
                        <span class="badge bg-primary">{{ $bonus->total_sales_count }} Shitje</span>
                    </div>
                </div>

                <!-- Sales Breakdown -->
                <div class="row mb-3">
                    <div class="col-6">
                        <div class="border rounded p-2" style="background: rgba(17, 153, 142, 0.1);">
                            <small class="text-muted d-block">📱 Xhiro Telefonat</small>
                            <strong class="text-success">{{ $bonus->phone_sales_total }} L</strong>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-2" style="background: rgba(79, 172, 254, 0.1);">
                            <small class="text-muted d-block">🔌 Xhiro Aksesorë</small>
                            <strong class="text-info">{{ $bonus->accessory_sales_total }} L</strong>
                        </div>
                    </div>
                </div>

                <!-- Bonus Breakdown -->
                <div class="border-top pt-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Bonus Telefonat ({{ $bonus->phone_bonus_percentage }}%):</span>
                        <strong class="text-success">{{ $bonus->phone_bonus_amount }} L</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Bonus Aksesorë ({{ $bonus->accessory_bonus_percentage }}%):</span>
                        <strong class="text-info">{{ $bonus->accessory_bonus_amount }} L</strong>
                    </div>
                    <div class="d-flex justify-content-between border-top pt-2">
                        <span class="fw-bold">BONUS TOTAL:</span>
                        <h5 class="mb-0 text-primary">{{ $bonus->total_bonus }} L</h5>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-3 d-flex gap-2">
                    <a href="{{ route('seller-bonuses.show', $bonus->id) }}" class="btn btn-sm btn-outline-primary flex-fill">
                        <i class="ri-eye-line me-1"></i> Detaje
                    </a>
                    <a href="{{ route('seller-bonuses.seller-report', $bonus->seller_id) }}" class="btn btn-sm btn-outline-info flex-fill">
                        <i class="ri-file-list-line me-1"></i> Raport
                    </a>
                    <form action="{{ route('seller-bonuses.destroy', $bonus->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Jeni i sigurt?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="ri-delete-bin-line"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="alert alert-info text-center">
            <i class="ri-information-line me-2"></i>
            Nuk ka bonuse të regjistruara.
        </div>
    </div>
    @endforelse
</div>

<!-- Pagination -->
@if($bonuses->hasPages())
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-center">
            {{ $bonuses->links('pagination::bootstrap-5') }}
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