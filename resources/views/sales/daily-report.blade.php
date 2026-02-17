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

    /* Filter Tabs */
    .filter-tabs {
        display: flex;
        gap: 8px;
        margin-bottom: 16px;
        flex-wrap: wrap;
    }

    .filter-tab {
        padding: 8px 20px;
        border-radius: 25px;
        border: 2px solid #667eea;
        background: white;
        color: #667eea;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        font-size: 14px;
    }

    .filter-tab:hover,
    .filter-tab.active {
        background: #667eea;
        color: white;
        text-decoration: none;
    }

    .filter-tab.weekly {
        border-color: #11998e;
        color: #11998e;
    }

    .filter-tab.weekly:hover,
    .filter-tab.weekly.active {
        background: #11998e;
        color: white;
    }

    .filter-tab.monthly {
        border-color: #f5576c;
        color: #f5576c;
    }

    .filter-tab.monthly:hover,
    .filter-tab.monthly.active {
        background: #f5576c;
        color: white;
    }

    /* Period Badge */
    .period-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 8px 20px;
        border-radius: 25px;
        font-weight: 600;
        font-size: 14px;
        margin-bottom: 15px;
    }

    .period-badge.weekly {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }

    .period-badge.monthly {
        background: linear-gradient(135deg, #f5576c 0%, #f093fb 100%);
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
            <h4 class="mb-sm-0">📊 RAPORTI I SHITJEVE</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('sales.index') }}">Sales</a></li>
                    <li class="breadcrumb-item active">Raport</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Filter Section -->
<div class="row mb-4 no-print">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">

                <!-- Quick Filter Tabs -->
                <div class="mb-3">
                    <label class="form-label fw-bold d-block mb-2">
                        <i class="ri-flashlight-line me-1"></i> Filtro Shpejt:
                    </label>
                    <div class="filter-tabs">
                        {{-- Sot --}}
                        <a href="{{ route('sales.daily-report', ['period' => 'today']) }}"
                            class="filter-tab {{ $period === 'today' ? 'active' : '' }}">
                            <i class="ri-sun-line me-1"></i> Sot
                        </a>

                        {{-- Dje --}}
                        <a href="{{ route('sales.daily-report', ['period' => 'yesterday']) }}"
                            class="filter-tab {{ $period === 'yesterday' ? 'active' : '' }}">
                            <i class="ri-calendar-check-line me-1"></i> Dje
                        </a>

                        {{-- Java Kjo --}}
                        <a href="{{ route('sales.daily-report', ['period' => 'this_week']) }}"
                            class="filter-tab weekly {{ $period === 'this_week' ? 'active' : '' }}">
                            <i class="ri-calendar-2-line me-1"></i> Java Aktuale
                        </a>

                        {{-- Java Kaluar --}}
                        <a href="{{ route('sales.daily-report', ['period' => 'last_week']) }}"
                            class="filter-tab weekly {{ $period === 'last_week' ? 'active' : '' }}">
                            <i class="ri-calendar-line me-1"></i> Java Kaluar
                        </a>

                        {{-- Muaji Ky --}}
                        <a href="{{ route('sales.daily-report', ['period' => 'this_month']) }}"
                            class="filter-tab monthly {{ $period === 'this_month' ? 'active' : '' }}">
                            <i class="ri-calendar-event-line me-1"></i> Muaji Aktual
                        </a>

                        {{-- Muaji Kaluar --}}
                        <a href="{{ route('sales.daily-report', ['period' => 'last_month']) }}"
                            class="filter-tab monthly {{ $period === 'last_month' ? 'active' : '' }}">
                            <i class="ri-calendar-todo-line me-1"></i> Muaji Kaluar
                        </a>

                        {{-- Custom --}}
                        <a href="#customFilterSection"
                            class="filter-tab {{ $period === 'custom' ? 'active' : '' }}"
                            onclick="toggleCustomFilter(event)">
                            <i class="ri-settings-3-line me-1"></i> Periudhë Manuale
                        </a>
                    </div>
                </div>

                <!-- Custom Date Range (Hidden by default) -->
                <div id="customFilterSection"
                    style="{{ $period === 'custom' ? 'display:block' : 'display:none' }}">
                    <hr>
                    <form method="GET" action="{{ route('sales.daily-report') }}" class="row g-3">
                        <input type="hidden" name="period" value="custom">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">
                                <i class="ri-calendar-line me-1"></i> Nga Data:
                            </label>
                            <input type="date"
                                class="form-control"
                                name="date_from"
                                value="{{ $dateFrom ?? '' }}"
                                max="{{ date('Y-m-d') }}"
                                required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">
                                <i class="ri-calendar-line me-1"></i> Deri në Datë:
                            </label>
                            <input type="date"
                                class="form-control"
                                name="date_to"
                                value="{{ $dateTo ?? '' }}"
                                max="{{ date('Y-m-d') }}"
                                required>
                        </div>
                        <div class="col-md-4 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">
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
</div>

<!-- Period Label -->
<div class="row">
    <div class="col-12 mb-3">
        @php
        $periodClass = in_array($period, ['this_week', 'last_week']) ? 'weekly'
        : (in_array($period, ['this_month', 'last_month']) ? 'monthly' : '');

        $periodLabel = match($period) {
        'today' => '📅 Sot: ' . \Carbon\Carbon::parse($dateFrom)->format('d/m/Y'),
        'yesterday' => '📅 Dje: ' . \Carbon\Carbon::parse($dateFrom)->format('d/m/Y'),
        'this_week' => '📅 Java Aktuale: ' . \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') . ' — ' . \Carbon\Carbon::parse($dateTo)->format('d/m/Y'),
        'last_week' => '📅 Java Kaluar: ' . \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') . ' — ' . \Carbon\Carbon::parse($dateTo)->format('d/m/Y'),
        'this_month' => '📅 Muaji ' . \Carbon\Carbon::parse($dateFrom)->translatedFormat('F Y') . ': ' . \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') . ' — ' . \Carbon\Carbon::parse($dateTo)->format('d/m/Y'),
        'last_month' => '📅 Muaji Kaluar (' . \Carbon\Carbon::parse($dateFrom)->translatedFormat('F Y') . '): ' . \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') . ' — ' . \Carbon\Carbon::parse($dateTo)->format('d/m/Y'),
        'custom' => '📅 Periudhë: ' . \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') . ' — ' . \Carbon\Carbon::parse($dateTo)->format('d/m/Y'),
        default => '📅 Raport',
        };
        @endphp
        <div class="period-badge {{ $periodClass }}">
            <i class="ri-time-line"></i>
            {{ $periodLabel }}
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row">
    <div class="col-lg-3 col-md-6">
        <div class="stat-box">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="mb-1 opacity-75">Xhiro Totale</p>
                    @foreach($totals['by_currency'] as $code => $c)
                    <h5 class="mb-0">{{ $c['xhiro'] }} {{ $c['symbol'] }}</h5>
                    @endforeach
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
                    <p class="mb-1">Fitimi Total</p>
                    @foreach($totals['by_currency'] as $code => $c)
                    <h5 class="mb-0 text-white">{{ $c['fitimi_total'] }} {{ $c['symbol'] }}</h5>
                    @endforeach
                    <small>Para ndarjes</small>
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
                    @foreach($totals['by_currency'] as $code => $c)
                    <h5 class="mb-0 text-primary">{{ $c['fitimi_juaj'] }} {{ $c['symbol'] }}</h5>
                    @endforeach
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
                        </div>
                        @foreach($warehouse['by_currency'] as $code => $c)
                        <div class="d-flex justify-content-between small">
                            <span>{{ $code }}:</span>
                            <span class="text-success">{{ $c['xhiro'] }} {{ $c['symbol'] }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Fitimi -->
                <div class="col-6">
                    <div class="p-3 bg-white rounded border">
                        <small class="text-muted d-block mb-1">📈 Fitimi Total:</small>
                        @foreach($warehouse['by_currency'] as $code => $c)
                        <h5 class="mb-0 text-success">{{ $c['fitimi_total'] }} {{ $c['symbol'] }}</h5>
                        @endforeach
                        <small class="text-muted">Para ndarjes</small>
                    </div>
                </div>

                <div class="col-6">
                    <div class="p-3 bg-white rounded border">
                        <small class="text-muted d-block mb-1">💵 Fitimi Juaj:</small>
                        @foreach($warehouse['by_currency'] as $code => $c)
                        <h5 class="mb-0 text-primary">{{ $c['fitimi_juaj'] }} {{ $c['symbol'] }}</h5>
                        @endforeach
                        <small class="text-muted">{{ $warehouse['perqindja_fitimit'] }} nga totali</small>
                    </div>
                </div>

                <!-- Mënyra e Pagesës -->
                <div class="col-12">
                    <div class="p-3 bg-white rounded border">
                        <small class="text-muted d-block mb-2">💳 Mënyra e Pagesës:</small>
                        @foreach($warehouse['by_currency'] as $code => $c)
                        <div class="d-flex justify-content-between mb-1">
                            <span>Cash ({{ $code }}):</span>
                            <strong>{{ $c['cash'] }} {{ $c['symbol'] }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Bank ({{ $code }}):</span>
                            <strong>{{ $c['bank'] }} {{ $c['symbol'] }}</strong>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="alert alert-info text-center">
            <i class="ri-information-line me-2"></i>
            Nuk ka shitje për periudhën e zgjedhur.
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
                                <td>
                                    @foreach($warehouse['by_currency'] as $code => $c)
                                    <div>{{ $c['xhiro'] }} {{ $c['symbol'] }}</div>
                                    @endforeach
                                </td>
                                <td>
                                    @foreach($warehouse['by_currency'] as $code => $c)
                                    <div class="text-success">{{ $c['fitimi_total'] }} {{ $c['symbol'] }}</div>
                                    @endforeach
                                </td>
                                <td>
                                    @foreach($warehouse['by_currency'] as $code => $c)
                                    <div class="text-primary">{{ $c['fitimi_juaj'] }} {{ $c['symbol'] }}</div>
                                    @endforeach
                                </td>
                                <td><span class="badge bg-info">{{ $warehouse['shitje_count'] }}</span></td>
                                <td>
                                    @foreach($warehouse['by_currency'] as $code => $c)
                                    <div>{{ $c['cash'] }} {{ $c['symbol'] }}</div>
                                    @endforeach
                                </td>
                                <td>
                                    @foreach($warehouse['by_currency'] as $code => $c)
                                    <div>{{ $c['bank'] }} {{ $c['symbol'] }}</div>
                                    @endforeach
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <td colspan="3" class="text-end"><strong>TOTALI:</strong></td>
                                <td>
                                    @foreach($totals['by_currency'] as $code => $c)
                                    <div>{{ $c['xhiro'] }} {{ $c['symbol'] }}</div>
                                    @endforeach
                                </td>
                                <td>
                                    @foreach($totals['by_currency'] as $code => $c)
                                    <div class="text-success">{{ $c['fitimi_total'] }} {{ $c['symbol'] }}</div>
                                    @endforeach
                                </td>
                                <td>
                                    @foreach($totals['by_currency'] as $code => $c)
                                    <div class="text-primary">{{ $c['fitimi_juaj'] }} {{ $c['symbol'] }}</div>
                                    @endforeach
                                </td>
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
    function toggleCustomFilter(e) {
        e.preventDefault();
        const section = document.getElementById('customFilterSection');
        section.style.display = section.style.display === 'none' ? 'block' : 'none';
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Nëse period është custom, shfaq seksionin e datave manuale
        const period = "{{ $period }}";
        if (period === 'custom') {
            document.getElementById('customFilterSection').style.display = 'block';
        }
    });
</script>
@endpush