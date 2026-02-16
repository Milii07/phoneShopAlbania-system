@extends('layouts.app')

@section('title', 'Raporti i Punëtorit')

@push('styles')
<style>
    .seller-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 40px;
        border-radius: 10px;
        margin-bottom: 30px;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-box {
        background: white;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        border: 2px solid #f0f0f0;
        transition: all 0.3s;
    }

    .stat-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        border-color: #667eea;
    }

    .stat-box.phone {
        border-left: 4px solid #11998e;
    }

    .stat-box.accessory {
        border-left: 4px solid #4facfe;
    }

    .stat-box.total {
        border-left: 4px solid #f5576c;
    }

    .stat-box.count {
        border-left: 4px solid #667eea;
    }

    .sale-row {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 10px;
        transition: all 0.3s;
    }

    .sale-row:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        transform: translateX(5px);
    }

    .filter-card {
        background: white;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    @media print {
        .no-print {
            display: none !important;
        }

        .sale-row {
            page-break-inside: avoid;
        }
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between no-print">
            <h4 class="mb-sm-0">📈 Raporti i Punëtorit</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('seller-bonuses.index') }}">Bonuset</a></li>
                    <li class="breadcrumb-item active">Raport</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Seller Header -->
<div class="seller-header">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <h2 class="text-white mb-2">
                <i class="ri-user-star-line me-2"></i>
                {{ $seller->name }}
            </h2>
            <p class="mb-0 opacity-75">
                <i class="ri-phone-line me-2"></i>
                Telefon: {{ $seller->phone ?? 'N/A' }}
            </p>
            <p class="mb-0 opacity-75">
                <i class="ri-percent-line me-2"></i>
                Bonusi: {{ $seller->phone_bonus_percentage }}% (Telefona) | {{ $seller->accessory_bonus_percentage }}% (Aksesorë)
            </p>
        </div>
        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
            <h3 class="text-white mb-1">{{ $sales->total() }} Shitje</h3>
            <p class="mb-0 opacity-75">
                @if(request('date_from') && request('date_to'))
                {{ \Carbon\Carbon::parse(request('date_from'))->format('d/m/Y') }} -
                {{ \Carbon\Carbon::parse(request('date_to'))->format('d/m/Y') }}
                @else
                Të gjitha periudhat
                @endif
            </p>
        </div>
    </div>
</div>

<!-- Statistics Grid -->
<div class="stats-grid">
    <div class="stat-box phone">
        <div class="mb-2">
            <i class="ri-smartphone-line" style="font-size: 2rem; color: #11998e;"></i>
        </div>
        <h6 class="text-muted mb-1">Xhiro Telefona</h6>
        <h4 class="mb-0 text-success">{{ number_format($totalPhoneSales, 2) }} L</h4>
        <small class="text-muted">
            Bonus: {{ number_format($totalPhoneSales * $seller->phone_bonus_percentage / 100, 2) }} L
        </small>
    </div>

    <div class="stat-box accessory">
        <div class="mb-2">
            <i class="ri-usb-line" style="font-size: 2rem; color: #4facfe;"></i>
        </div>
        <h6 class="text-muted mb-1">Xhiro Aksesorë</h6>
        <h4 class="mb-0 text-info">{{ number_format($totalAccessorySales, 2) }} L</h4>
        <small class="text-muted">
            Bonus: {{ number_format($totalAccessorySales * $seller->accessory_bonus_percentage / 100, 2) }} L
        </small>
    </div>

    <div class="stat-box total">
        <div class="mb-2">
            <i class="ri-money-dollar-circle-line" style="font-size: 2rem; color: #f5576c;"></i>
        </div>
        <h6 class="text-muted mb-1">Xhiro Totale</h6>
        <h4 class="mb-0 text-danger">{{ number_format($totalSales, 2) }} L</h4>
        <small class="text-muted">Të gjitha kategoritë</small>
    </div>

    <div class="stat-box count">
        <div class="mb-2">
            <i class="ri-shopping-cart-line" style="font-size: 2rem; color: #667eea;"></i>
        </div>
        <h6 class="text-muted mb-1">Numri i Shitjeve</h6>
        <h4 class="mb-0 text-primary">{{ $sales->total() }}</h4>
        <small class="text-muted">Shitje të konfirmuara</small>
    </div>
</div>

<!-- Filters -->
<div class="filter-card no-print">
    <form method="GET" action="{{ route('seller-bonuses.seller-report', $seller->id) }}" class="row g-3">
        <div class="col-lg-4">
            <label class="form-label">Nga Data</label>
            <input type="date"
                class="form-control"
                name="date_from"
                value="{{ request('date_from') }}">
        </div>
        <div class="col-lg-4">
            <label class="form-label">Deri në Datë</label>
            <input type="date"
                class="form-control"
                name="date_to"
                value="{{ request('date_to') }}">
        </div>
        <div class="col-lg-4 d-flex align-items-end gap-2">
            <button type="submit" class="btn btn-primary flex-fill">
                <i class="ri-filter-line me-1"></i> Filtro
            </button>
            <a href="{{ route('seller-bonuses.seller-report', $seller->id) }}" class="btn btn-secondary">
                <i class="ri-refresh-line"></i>
            </a>
        </div>
    </form>
</div>

<!-- Sales List -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title mb-0">
                        <i class="ri-file-list-line me-2"></i>
                        Lista e Shitjeve ({{ $sales->total() }})
                    </h5>
                    <button class="btn btn-primary no-print" onclick="window.print()">
                        <i class="ri-printer-line me-1"></i> Printo
                    </button>
                </div>

                @forelse($sales as $sale)
                <div class="sale-row">
                    <div class="row align-items-center">
                        <div class="col-lg-2">
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
                            <small class="text-muted d-block">Magazina</small>
                            <span class="badge bg-soft-secondary text-secondary">
                                {{ $sale->warehouse->name ?? 'N/A' }}
                            </span>
                        </div>

                        <div class="col-lg-2">
                            <small class="text-muted d-block">Kategoritë</small>
                            @php
                            $categories = $sale->items->pluck('category.name')->unique()->filter();
                            @endphp
                            @foreach($categories as $category)
                            <span class="badge bg-soft-primary text-primary me-1">
                                {{ $category }}
                            </span>
                            @endforeach
                        </div>

                        <div class="col-lg-2 text-end">
                            <small class="text-muted d-block">Shuma Totale</small>
                            <h6 class="mb-0 text-primary">
                                {{ number_format($sale->total_amount, 2) }} {{ $sale->currency->code ?? 'L' }}
                            </h6>
                        </div>

                        <div class="col-lg-1 text-end no-print">
                            <a href="{{ route('sales.show', $sale->id) }}"
                                class="btn btn-sm btn-outline-primary"
                                title="Shiko Detajet">
                                <i class="ri-eye-line"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Sale Items Breakdown -->
                    <div class="mt-2 pt-2 border-top">
                        <small class="text-muted">Produktet:</small>
                        <div class="row mt-1">
                            @foreach($sale->items as $item)
                            <div class="col-lg-6 mb-1">
                                <small>
                                    • {{ $item->product->name ?? 'N/A' }}
                                    <span class="text-muted">
                                        ({{ $item->quantity }} × {{ number_format($item->unit_price, 2) }} =
                                        {{ number_format($item->line_total, 2) }} {{ $sale->currency->code ?? 'L' }})
                                    </span>
                                </small>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @empty
                <div class="alert alert-info text-center">
                    <i class="ri-information-line me-2"></i>
                    Nuk ka shitje për këtë punëtor në periudhën e zgjedhur.
                </div>
                @endforelse

                <!-- Pagination -->
                @if($sales->hasPages())
                <div class="d-flex justify-content-center mt-4 no-print">
                    {{ $sales->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Action Buttons -->
<div class="row no-print">
    <div class="col-12">
        <div class="d-flex gap-2 justify-content-end">
            <a href="{{ route('seller-bonuses.index') }}" class="btn btn-secondary">
                <i class="ri-arrow-left-line me-1"></i> Kthehu te Bonuset
            </a>
            <a href="{{ route('seller-bonuses.create') }}?seller_id={{ $seller->id }}" class="btn btn-primary">
                <i class="ri-add-line me-1"></i> Llogarit Bonus për këtë Periudhë
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Print styling
    window.addEventListener('beforeprint', () => {
        document.title = `Raporti - ${document.querySelector('.seller-header h2').textContent.trim()}`;
    });

    window.addEventListener('afterprint', () => {
        document.title = 'Raporti i Punëtorit';
    });
</script>
@endpush