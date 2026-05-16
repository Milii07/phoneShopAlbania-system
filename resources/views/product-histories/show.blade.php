@extends('layouts.app')

@section('title', 'Histori Produkti: ' . $product->name)

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-sm-0">{{ $product->name }}</h4>
                <p class="text-muted mb-0">
                    Histori e plotë e lëvizjeve të produktit
                </p>
            </div>
            <div>
                <a href="{{ route('product-history.export', ['product_id' => $product->id]) }}" class="btn btn-sm btn-primary">
                    <i class="ri-download-line"></i> Eksporto CSV
                </a>
                <a href="{{ route('product-history.index') }}" class="btn btn-sm btn-secondary">
                    <i class="ri-arrow-left-line"></i> Kthehu
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Total Lëvizjesh</p>
                        <h4 class="mb-0">{{ $stats['total_movements'] }}</h4>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-light text-primary rounded-3">
                            <i class="ri-exchange-line"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Statusi Aktual</p>
                        <h4 class="mb-0">
                            <span class="badge bg-success">{{ ucfirst($stats['current_status']) }}</span>
                        </h4>
                    </div>
                    <div class="avatar-sm">
                        <span class="avatar-title bg-light text-success rounded-3">
                            <i class="ri-checkbox-circle-line"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($stats['last_movement'])
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <p class="text-muted mb-1">Lëvizja e Fundit</p>
                <div class="d-flex align-items-center">
                    <div>
                        <h6 class="mb-1">
                            <span class="badge bg-{{ $stats['last_movement']->action_badge_color }}">
                                {{ $stats['last_movement']->action_type_name }}
                            </span>
                        </h6>
                        <small class="text-muted">
                            {{ $stats['last_movement']->created_at->format('d/m/Y H:i') }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Timeline View -->
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h5 class="mb-0">Kronologjia e Lëvizjeve</h5>
                <div class="ms-auto">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-primary active" id="timelineView">
                            <i class="ri-timeline-view"></i> Kronologjia
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="tableView">
                            <i class="ri-table-2"></i> Tabela
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <!-- Timeline View -->
                <div id="timelineContainer" class="timeline-view">
                    @if($timeline->count() > 0)
                        <div class="position-relative ps-3">
                            @foreach($timeline as $history)
                                <div class="timeline-item mb-4">
                                    <div class="timeline-line"></div>
                                    <div class="timeline-dot">
                                        <i class="{{ $history->action_icon }}"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h6 class="mb-1">
                                                    <span class="badge bg-{{ $history->action_badge_color }}">
                                                        {{ $history->action_type_name }}
                                                    </span>
                                                </h6>
                                                <small class="text-muted">
                                                    <i class="ri-calendar-line"></i>
                                                    {{ $history->created_at->format('d/m/Y H:i') }}
                                                </small>
                                            </div>
                                            <span class="badge bg-{{ $history->status_badge_color }}">
                                                {{ $history->status_name }}
                                            </span>
                                        </div>

                                        <div class="card bg-light border-0 mt-2">
                                            <div class="card-body p-3">
                                                <div class="row g-3 text-sm">
                                                    @if($history->warehouseFrom)
                                                        <div class="col-md-6">
                                                            <strong>Nga Magazina:</strong><br>
                                                            <span class="badge bg-secondary">{{ $history->warehouseFrom->name }}</span>
                                                        </div>
                                                    @endif

                                                    @if($history->warehouseTo)
                                                        <div class="col-md-6">
                                                            <strong>Në Magazinë:</strong><br>
                                                            <span class="badge bg-primary">{{ $history->warehouseTo->name }}</span>
                                                        </div>
                                                    @endif

                                                    @if($history->user)
                                                        <div class="col-md-6">
                                                            <strong>Përdoruesi:</strong><br>
                                                            <i class="ri-user-line"></i> {{ $history->user->name }}
                                                        </div>
                                                    @endif

                                                    @if($history->partner)
                                                        <div class="col-md-6">
                                                            <strong>Furnitor/Blerësi:</strong><br>
                                                            <i class="ri-phone-line"></i> {{ $history->partner->name }}
                                                            @if($history->partner->phone)
                                                                <br><small>{{ $history->partner->phone }}</small>
                                                            @endif
                                                        </div>
                                                    @endif

                                                    <div class="col-md-3">
                                                        <strong>Sasia:</strong><br>
                                                        <h6>{{ $history->quantity }}</h6>
                                                    </div>

                                                    @if($history->purchase_price)
                                                        <div class="col-md-3">
                                                            <strong>Çmimi Blerjës:</strong><br>
                                                            {{ number_format($history->purchase_price, 2) }} €
                                                        </div>
                                                    @endif

                                                    @if($history->sale_price)
                                                        <div class="col-md-3">
                                                            <strong>Çmimi Shitjës:</strong><br>
                                                            {{ number_format($history->sale_price, 2) }} €
                                                        </div>
                                                    @endif

                                                    @if($history->invoice_number)
                                                        <div class="col-md-3">
                                                            <strong>Fatura:</strong><br>
                                                            <span class="badge bg-info">{{ $history->invoice_number }}</span>
                                                        </div>
                                                    @endif

                                                    @if($history->warranty)
                                                        <div class="col-md-6">
                                                            <strong>Garancia:</strong><br>
                                                            <i class="ri-shield-line"></i> {{ $history->warranty }}
                                                        </div>
                                                    @endif

                                                    @if($history->imei)
                                                        <div class="col-md-6">
                                                            <strong>IMEI:</strong><br>
                                                            <code>{{ $history->imei }}</code>
                                                        </div>
                                                    @endif
                                                </div>

                                                @if($history->notes)
                                                    <div class="mt-3 pt-3 border-top">
                                                        <strong>Shënime:</strong><br>
                                                        <small class="text-muted">{{ $history->notes }}</small>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info" role="alert">
                            <i class="ri-information-line"></i> Nuk ka histori për këtë produkt.
                        </div>
                    @endif
                </div>

                <!-- Table View -->
                <div id="tableContainer" style="display: none;" class="table-view">
                    @if($histories->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Lloji i Lëvizjes</th>
                                        <th>Nga</th>
                                        <th>Në</th>
                                        <th>Përdoruesi</th>
                                        <th>Sasia</th>
                                        <th>Çmimi</th>
                                        <th>Statusi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($histories as $history)
                                        <tr>
                                            <td>
                                                <small>{{ $history->created_at->format('d/m/Y H:i') }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $history->action_badge_color }}">
                                                    {{ $history->action_type_name }}
                                                </span>
                                            </td>
                                            <td>
                                                <small>{{ $history->warehouseFrom?->name ?? '-' }}</small>
                                            </td>
                                            <td>
                                                <small>{{ $history->warehouseTo?->name ?? '-' }}</small>
                                            </td>
                                            <td>
                                                <small>{{ $history->user?->name ?? '-' }}</small>
                                            </td>
                                            <td>
                                                <strong>{{ $history->quantity }}</strong>
                                            </td>
                                            <td>
                                                @if($history->sale_price)
                                                    {{ number_format($history->sale_price, 2) }} €
                                                @elseif($history->purchase_price)
                                                    {{ number_format($history->purchase_price, 2) }} €
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $history->status_badge_color }}">
                                                    {{ $history->status_name }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                Shfaqja e {{ $histories->firstItem() }} deri {{ $histories->lastItem() }} nga {{ $histories->total() }} rekordet
                            </div>
                            <div>
                                {{ $histories->links() }}
                            </div>
                        </div>
                    @else
                        <div class="alert alert-info" role="alert">
                            <i class="ri-information-line"></i> Nuk ka histori për këtë produkt.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.timeline-item {
    position: relative;
    padding-left: 2rem;
}

.timeline-line {
    position: absolute;
    left: 0.35rem;
    top: 2rem;
    bottom: -2rem;
    width: 2px;
    background: #dee2e6;
}

.timeline-item:last-child .timeline-line {
    display: none;
}

.timeline-dot {
    position: absolute;
    left: -0.5rem;
    top: 0;
    width: 2rem;
    height: 2rem;
    background: #fff;
    border: 2px solid #0d6efd;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    color: #0d6efd;
    z-index: 1;
}

.text-sm {
    font-size: 0.875rem;
}
</style>
@endpush

@push('scripts')
<script>
document.getElementById('timelineView').addEventListener('click', function () {
    document.getElementById('timelineContainer').style.display = 'block';
    document.getElementById('tableContainer').style.display = 'none';
    this.classList.add('active');
    document.getElementById('tableView').classList.remove('active');
});

document.getElementById('tableView').addEventListener('click', function () {
    document.getElementById('timelineContainer').style.display = 'none';
    document.getElementById('tableContainer').style.display = 'block';
    this.classList.add('active');
    document.getElementById('timelineView').classList.remove('active');
});
</script>
@endpush
