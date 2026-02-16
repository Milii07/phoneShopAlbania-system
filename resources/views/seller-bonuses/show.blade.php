@extends('layouts.app')

@section('title', 'Detajet e Bonusit')

@push('styles')
<style>
    .bonus-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        border-radius: 10px;
        margin-bottom: 30px;
    }

    .info-card {
        background: white;
        border-radius: 8px;
        padding: 20px;
        border-left: 4px solid #667eea;
        margin-bottom: 15px;
        transition: all 0.3s;
    }

    .info-card:hover {
        transform: translateX(5px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .info-card.phone {
        border-left-color: #11998e;
        background: linear-gradient(135deg, rgba(17, 153, 142, 0.05) 0%, rgba(56, 239, 125, 0.05) 100%);
    }

    .info-card.accessory {
        border-left-color: #4facfe;
        background: linear-gradient(135deg, rgba(79, 172, 254, 0.05) 0%, rgba(0, 242, 254, 0.05) 100%);
    }

    .info-card.total {
        border-left-color: #f5576c;
        background: linear-gradient(135deg, rgba(240, 147, 251, 0.05) 0%, rgba(245, 87, 108, 0.05) 100%);
    }

    .sale-item {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 10px;
        transition: all 0.3s;
    }

    .sale-item:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        border-color: #667eea;
    }

    .tab-content {
        padding: 20px 0;
    }

    .nav-tabs .nav-link {
        color: #666;
        border: none;
        padding: 12px 24px;
        font-weight: 500;
    }

    .nav-tabs .nav-link.active {
        color: #667eea;
        border-bottom: 3px solid #667eea;
        background: transparent;
    }

    .badge-category {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">📊 Detajet e Bonusit</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('seller-bonuses.index') }}">Bonuset</a></li>
                    <li class="breadcrumb-item active">Detaje</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Bonus Header -->
<div class="bonus-header">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <h3 class="text-white mb-2">
                <i class="ri-user-line me-2"></i>
                {{ $sellerBonus->seller->name }}
            </h3>
            <p class="mb-0 opacity-75">
                <i class="ri-calendar-line me-2"></i>
                Periudha: {{ $sellerBonus->period_start->format('d/m/Y') }} - {{ $sellerBonus->period_end->format('d/m/Y') }}
            </p>
        </div>
        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
            <h2 class="text-white mb-0">{{ number_format($sellerBonus->total_bonus, 2) }} L</h2>
            <p class="mb-0 opacity-75">Bonus Total</p>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row">
    <!-- Phone Sales -->
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="info-card phone">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h6 class="text-muted mb-1">📱 TELEFONA</h6>
                    <h4 class="mb-0 text-success">{{ number_format($sellerBonus->phone_sales_total, 2) }} L</h4>
                    <small class="text-muted">Xhiro Totale</small>
                </div>
                <span class="badge bg-success">{{ $sellerBonus->phone_bonus_percentage }}%</span>
            </div>
            <div class="border-top pt-2">
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">Bonus:</span>
                    <strong class="text-success">{{ number_format($sellerBonus->phone_bonus_amount, 2) }} L</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Accessory Sales -->
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="info-card accessory">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h6 class="text-muted mb-1">🔌 AKSESORË</h6>
                    <h4 class="mb-0 text-info">{{ number_format($sellerBonus->accessory_sales_total, 2) }} L</h4>
                    <small class="text-muted">Xhiro Totale</small>
                </div>
                <span class="badge bg-info">{{ $sellerBonus->accessory_bonus_percentage }}%</span>
            </div>
            <div class="border-top pt-2">
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">Bonus:</span>
                    <strong class="text-info">{{ number_format($sellerBonus->accessory_bonus_amount, 2) }} L</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Total -->
    <div class="col-lg-4 col-md-12 mb-4">
        <div class="info-card total">
            <div class="mb-3">
                <h6 class="text-muted mb-1">💰 TOTALI</h6>
                <h4 class="mb-0 text-primary">{{ number_format($sellerBonus->total_bonus, 2) }} L</h4>
                <small class="text-muted">Bonus Total</small>
            </div>
            <div class="border-top pt-2">
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">Numri i Shitjeve:</span>
                    <strong class="text-primary">{{ $sellerBonus->total_sales_count }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Notes (if any) -->
@if($sellerBonus->notes)
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="ri-file-text-line me-2"></i>
                    Shënime
                </h6>
                <p class="mb-0">{{ $sellerBonus->notes }}</p>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Sales Details -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">
                    <i class="ri-shopping-cart-line me-2"></i>
                    Detajet e Shitjeve
                </h5>

                <!-- Tabs -->
                <ul class="nav nav-tabs" id="salesTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="all-sales-tab" data-bs-toggle="tab" data-bs-target="#all-sales" type="button">
                            <i class="ri-list-check me-1"></i>
                            Të Gjitha ({{ $sales->count() }})
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="phone-sales-tab" data-bs-toggle="tab" data-bs-target="#phone-sales" type="button">
                            <i class="ri-smartphone-line me-1"></i>
                            Telefona ({{ count($phoneSales) }})
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="accessory-sales-tab" data-bs-toggle="tab" data-bs-target="#accessory-sales" type="button">
                            <i class="ri-usb-line me-1"></i>
                            Aksesorë ({{ count($accessorySales) }})
                        </button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="salesTabContent">
                    <!-- All Sales -->
                    <div class="tab-pane fade show active" id="all-sales" role="tabpanel">
                        @forelse($sales as $sale)
                        <div class="sale-item">
                            <div class="row align-items-center">
                                <div class="col-lg-3">
                                    <h6 class="mb-1">
                                        <a href="{{ route('sales.show', $sale->id) }}" class="text-dark">
                                            {{ $sale->invoice_number }}
                                        </a>
                                    </h6>
                                    <small class="text-muted">
                                        <i class="ri-calendar-line me-1"></i>
                                        {{ $sale->invoice_date->format('d/m/Y') }}
                                    </small>
                                </div>
                                <div class="col-lg-3">
                                    <small class="text-muted d-block">Klienti</small>
                                    <strong>{{ $sale->partner->business_name ?? 'N/A' }}</strong>
                                </div>
                                <div class="col-lg-2">
                                    <small class="text-muted d-block">Produktet</small>
                                    @foreach($sale->items->unique('category_id') as $item)
                                    @if($item->category)
                                    <span class="badge badge-category bg-soft-primary text-primary me-1">
                                        {{ $item->category->name }}
                                    </span>
                                    @endif
                                    @endforeach
                                </div>
                                <div class="col-lg-2 text-end">
                                    <small class="text-muted d-block">Shuma</small>
                                    <h6 class="mb-0 text-primary">{{ number_format($sale->total_amount, 2) }} {{ $sale->currency->code ?? 'L' }}</h6>
                                </div>
                                <div class="col-lg-2 text-end">
                                    <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="ri-eye-line"></i> Detaje
                                    </a>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="alert alert-info text-center">
                            <i class="ri-information-line me-2"></i>
                            Nuk ka shitje në këtë periudhë.
                        </div>
                        @endforelse
                    </div>

                    <!-- Phone Sales -->
                    <div class="tab-pane fade" id="phone-sales" role="tabpanel">
                        @forelse($phoneSales as $sale)
                        <div class="sale-item">
                            <div class="row align-items-center">
                                <div class="col-lg-3">
                                    <h6 class="mb-1">
                                        <a href="{{ route('sales.show', $sale->id) }}" class="text-dark">
                                            {{ $sale->invoice_number }}
                                        </a>
                                    </h6>
                                    <small class="text-muted">
                                        <i class="ri-calendar-line me-1"></i>
                                        {{ $sale->invoice_date->format('d/m/Y') }}
                                    </small>
                                </div>
                                <div class="col-lg-3">
                                    <small class="text-muted d-block">Klienti</small>
                                    <strong>{{ $sale->partner->business_name ?? 'N/A' }}</strong>
                                </div>
                                <div class="col-lg-2">
                                    <span class="badge bg-success">
                                        <i class="ri-smartphone-line me-1"></i>
                                        Telefona
                                    </span>
                                </div>
                                <div class="col-lg-2 text-end">
                                    <small class="text-muted d-block">Shuma</small>
                                    <h6 class="mb-0 text-success">{{ number_format($sale->total_amount, 2) }} {{ $sale->currency->code ?? 'L' }}</h6>
                                </div>
                                <div class="col-lg-2 text-end">
                                    <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-sm btn-outline-success">
                                        <i class="ri-eye-line"></i> Detaje
                                    </a>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="alert alert-info text-center">
                            <i class="ri-information-line me-2"></i>
                            Nuk ka shitje telefonash në këtë periudhë.
                        </div>
                        @endforelse
                    </div>

                    <!-- Accessory Sales -->
                    <div class="tab-pane fade" id="accessory-sales" role="tabpanel">
                        @forelse($accessorySales as $sale)
                        <div class="sale-item">
                            <div class="row align-items-center">
                                <div class="col-lg-3">
                                    <h6 class="mb-1">
                                        <a href="{{ route('sales.show', $sale->id) }}" class="text-dark">
                                            {{ $sale->invoice_number }}
                                        </a>
                                    </h6>
                                    <small class="text-muted">
                                        <i class="ri-calendar-line me-1"></i>
                                        {{ $sale->invoice_date->format('d/m/Y') }}
                                    </small>
                                </div>
                                <div class="col-lg-3">
                                    <small class="text-muted d-block">Klienti</small>
                                    <strong>{{ $sale->partner->business_name ?? 'N/A' }}</strong>
                                </div>
                                <div class="col-lg-2">
                                    <span class="badge bg-info">
                                        <i class="ri-usb-line me-1"></i>
                                        Aksesorë
                                    </span>
                                </div>
                                <div class="col-lg-2 text-end">
                                    <small class="text-muted d-block">Shuma</small>
                                    <h6 class="mb-0 text-info">{{ number_format($sale->total_amount, 2) }} {{ $sale->currency->code ?? 'L' }}</h6>
                                </div>
                                <div class="col-lg-2 text-end">
                                    <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-sm btn-outline-info">
                                        <i class="ri-eye-line"></i> Detaje
                                    </a>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="alert alert-info text-center">
                            <i class="ri-information-line me-2"></i>
                            Nuk ka shitje aksesorësh në këtë periudhë.
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div class="row">
    <div class="col-12">
        <div class="d-flex gap-2 justify-content-end">
            <a href="{{ route('seller-bonuses.index') }}" class="btn btn-secondary">
                <i class="ri-arrow-left-line me-1"></i> Kthehu
            </a>
            <a href="{{ route('seller-bonuses.seller-report', $sellerBonus->seller_id) }}" class="btn btn-info">
                <i class="ri-file-list-line me-1"></i> Raport i Plotë
            </a>
            <button class="btn btn-primary" onclick="window.print()">
                <i class="ri-printer-line me-1"></i> Printo
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Print Styles
    window.addEventListener('beforeprint', () => {
        document.querySelector('.page-title-box').style.display = 'none';
        document.querySelectorAll('.btn').forEach(btn => btn.style.display = 'none');
    });

    window.addEventListener('afterprint', () => {
        document.querySelector('.page-title-box').style.display = 'flex';
        document.querySelectorAll('.btn').forEach(btn => btn.style.display = 'inline-block');
    });
</script>
@endpush