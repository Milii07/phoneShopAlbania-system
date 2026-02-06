@extends('layouts.app')

@section('title', 'Raporti Ditor i Shitjeve')

@push('styles')
<style>
    .report-card {
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s;
    }

    .report-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .stat-box {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .stat-box.success {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }

    .stat-box.warning {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }

    .stat-box.info {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }

    .warehouse-card {
        border-left: 4px solid #667eea;
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .warehouse-card.partner {
        border-left-color: #f5576c;
    }

    .profit-badge {
        display: inline-block;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        margin-left: 10px;
    }

    .profit-badge.full {
        background: #e8f5e9;
        color: #2e7d32;
    }

    .profit-badge.shared {
        background: #fff3e0;
        color: #e65100;
    }

    @media print {
        .no-print {
            display: none !important;
        }

        .report-card,
        .warehouse-card {
            break-inside: avoid;
            page-break-inside: avoid;
        }

        body {
            font-size: 12px;
        }

        .stat-box {
            border: 1px solid #ddd;
            color: #000 !important;
            background: #fff !important;
        }
    }
</style>
@endpush

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">📊 RAPORTI DITOR I SHITJEVE</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('sales.index') }}">Sales</a></li>
                    <li class="breadcrumb-item active">Daily Report</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Date Filter -->
<div class="row mb-4 no-print">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" action="{{ route('sales.daily-report') }}" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Zgjidh Datën:</label>
                        <input type="date"
                            class="form-control"
                            name="date"
                            value="{{ $date }}"
                            max="{{ date('Y-m-d') }}"
                            required>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="ri-search-line me-1"></i> Shiko Raportin
                        </button>
                        <button type="button" onclick="window.print()" class="btn btn-info">
                            <i class="ri-printer-line me-1"></i> Print
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row">
    <div class="col-12 mb-3">
        <h5>📅 Data: <strong>{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</strong></h5>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stat-box">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1 opacity-75">Xhiro Totale</p>
                    <h3 class="mb-0">{{ $totals['xhiro_totale'] }} L</h3>
                </div>
                <div>
                    <i class="ri-money-dollar-circle-line" style="font-size: 3rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stat-box success">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1 opacity-75">Fitimi Total</p>
                    <h3 class="mb-0">{{ $totals['fitimi_total'] }} L</h3>
                    <small class="opacity-75">Para ndarjes</small>
                </div>
                <div>
                    <i class="ri-line-chart-line" style="font-size: 3rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stat-box warning">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1 opacity-75">Fitimi Juaj</p>
                    <h3 class="mb-0">{{ $totals['fitimi_juaj'] }} L</h3>
                    <small class="opacity-75">Pas ndarjes</small>
                </div>
                <div>
                    <i class="ri-wallet-3-line" style="font-size: 3rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6">
        <div class="stat-box info">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1 opacity-75">Shitje Totale</p>
                    <h3 class="mb-0">{{ $totals['shitje_totale'] }}</h3>
                    <small class="opacity-75">Invoice</small>
                </div>
                <div>
                    <i class="ri-shopping-cart-line" style="font-size: 3rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Warehouse Reports -->
<div class="row">
    <div class="col-12">
        <h5 class="mb-3">🏪 Raport për Dyqan</h5>
    </div>

    @forelse($report as $warehouse)
    <div class="col-lg-6">
        <div class="warehouse-card {{ $warehouse['perqindja_fitimit'] == '50%' ? 'partner' : '' }}">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h5 class="mb-1">
                        {{ $warehouse['dyqani'] }}
                        <span class="profit-badge {{ $warehouse['perqindja_fitimit'] == '100%' ? 'full' : 'shared' }}">
                            {{ $warehouse['perqindja_fitimit'] }}
                        </span>
                    </h5>
                    <small class="text-muted">📍 {{ $warehouse['lokacioni'] }}</small>
                </div>
                <div class="text-end">
                    <span class="badge bg-primary">{{ $warehouse['shitje_count'] }} Shitje</span>
                </div>
            </div>

            <div class="row g-3">
                <!-- Xhiro -->
                <div class="col-12">
                    <div class="p-3 bg-white rounded border">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">💰 Xhiro Totale:</span>
                            <strong class="text-primary fs-5">{{ $warehouse['xhiro_totale'] }} L</strong>
                        </div>
                        <div class="d-flex justify-content-between small">
                            <span>Euro:</span>
                            <span class="text-success">{{ $warehouse['xhiro_euro'] }} €</span>
                        </div>
                        <div class="d-flex justify-content-between small">
                            <span>Lekë:</span>
                            <span class="text-info">{{ $warehouse['xhiro_leke'] }} L</span>
                        </div>
                    </div>
                </div>

                <!-- Fitimi -->
                <div class="col-6">
                    <div class="p-3 bg-white rounded border">
                        <small class="text-muted d-block mb-1">📈 Fitimi Total:</small>
                        <h5 class="mb-0 text-success">{{ $warehouse['fitimi_total'] }} L</h5>
                        <small class="text-muted">Para ndarjes</small>
                    </div>
                </div>

                <div class="col-6">
                    <div class="p-3 bg-white rounded border">
                        <small class="text-muted d-block mb-1">💵 Fitimi Juaj:</small>
                        <h5 class="mb-0 text-primary">{{ $warehouse['fitimi_juaj'] }} L</h5>
                        <small class="text-muted">{{ $warehouse['perqindja_fitimit'] }} nga totali</small>
                    </div>
                </div>

                <!-- Mënyra e Pagesës -->
                <div class="col-12">
                    <div class="p-3 bg-white rounded border">
                        <small class="text-muted d-block mb-2">💳 Mënyra e Pagesës:</small>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Cash:</span>
                            <strong>{{ $warehouse['pagesa_cash'] }} L</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Bank:</span>
                            <strong>{{ $warehouse['pagesa_banke'] }} L</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="alert alert-info text-center">
            <i class="ri-information-line me-2"></i>
            Nuk ka shitje për datën {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}
        </div>
    </div>
    @endforelse
</div>

<!-- Detailed Table -->
@if($report->isNotEmpty())
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">📋 Detaje të Plota</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Dyqani</th>
                                <th>Lokacioni</th>
                                <th>% Fitimi</th>
                                <th>Xhiro Totale</th>
                                <th>Fitimi Total</th>
                                <th>Fitimi Juaj</th>
                                <th>Shitje</th>
                                <th>Cash</th>
                                <th>Bank</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($report as $warehouse)
                            <tr>
                                <td><strong>{{ $warehouse['dyqani'] }}</strong></td>
                                <td>{{ $warehouse['lokacioni'] }}</td>
                                <td>
                                    <span class="badge {{ $warehouse['perqindja_fitimit'] == '100%' ? 'bg-success' : 'bg-warning' }}">
                                        {{ $warehouse['perqindja_fitimit'] }}
                                    </span>
                                </td>
                                <td><strong>{{ $warehouse['xhiro_totale'] }} L</strong></td>
                                <td><strong class="text-success">{{ $warehouse['fitimi_total'] }} L</strong></td>
                                <td><strong class="text-primary">{{ $warehouse['fitimi_juaj'] }} L</strong></td>
                                <td><span class="badge bg-info">{{ $warehouse['shitje_count'] }}</span></td>
                                <td>{{ $warehouse['pagesa_cash'] }} L</td>
                                <td>{{ $warehouse['pagesa_banke'] }} L</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <td colspan="3" class="text-end"><strong>TOTALI:</strong></td>
                                <td><strong>{{ $totals['xhiro_totale'] }} L</strong></td>
                                <td><strong class="text-success">{{ $totals['fitimi_total'] }} L</strong></td>
                                <td><strong class="text-primary">{{ $totals['fitimi_juaj'] }} L</strong></td>
                                <td><strong>{{ $totals['shitje_totale'] }}</strong></td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Export Buttons -->
<div class="row mt-3 no-print">
    <div class="col-12 text-center">
        <a href="{{ route('sales.index') }}" class="btn btn-secondary">
            <i class="ri-arrow-left-line me-1"></i> Kthehu tek Shitjet
        </a>
        <button onclick="window.print()" class="btn btn-info">
            <i class="ri-printer-line me-1"></i> Print Raportin
        </button>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-set today's date on page load if no date is selected
    document.addEventListener('DOMContentLoaded', function() {
        const dateInput = document.querySelector('input[name="date"]');
        if (!dateInput.value) {
            dateInput.value = new Date().toISOString().split('T')[0];
        }
    });
</script>
@endpush