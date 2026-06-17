@extends('layouts.app')

@section('title', 'Arka - ' . $register->register_date->format('d/m/Y'))

@section('content')

{{-- Page header --}}
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-0">Arka</h4>
        <span class="text-muted">{{ $register->register_date->format('d/m/Y') }}
            &nbsp;·&nbsp;
            @if($register->status === 'open')
                <span class="badge bg-success">E Hapur</span>
            @elseif($register->status === 'closed')
                <span class="badge bg-warning text-dark">E Mbyllur</span>
            @else
                <span class="badge bg-primary">E Balancuar</span>
            @endif
            @if($register->seller)
                &nbsp;·&nbsp; <i class="ri-user-line"></i> {{ $register->seller->name }}
            @endif
        </span>
    </div>
    <div class="d-flex gap-2">
        @if($register->status === 'open')
            @can('add cash-register')
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#shtoModal">
                <i class="ri-add-circle-line me-1"></i> Shto
            </button>
            @endcan
            @can('remove cash-register')
            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#hiqModal">
                <i class="ri-indeterminate-circle-line me-1"></i> Hiq
            </button>
            @endcan
            @can('close cash-register')
            <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#closeModal">
                <i class="ri-lock-line me-1"></i> Mbyll Arkën
            </button>
            @endcan
        @elseif($register->status === 'closed')
            @can('balance cash-register')
            <form action="{{ route('cash-register.balance', $register) }}" method="POST">
                @csrf
                <button class="btn btn-primary"><i class="ri-check-double-line me-1"></i> Balancuo</button>
            </form>
            @endcan
        @endif
        @can('view cash-register')
        <a href="{{ route('cash-register.index') }}" class="btn btn-outline-secondary">
            <i class="ri-list-check me-1"></i> Të gjitha arkat
        </a>
        @endcan
    </div>
</div>

{{-- Per-currency balance containers --}}
<div class="row g-3 mb-4">
    @foreach($register->balances->sortBy('currency.code') as $balance)
    <div class="col-xl-3 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-0">
                {{-- Currency header --}}
                <div class="px-4 pt-4 pb-3 border-bottom d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-0 small text-uppercase fw-semibold">{{ $balance->currency->name ?? $balance->currency->code }}</p>
                        <h2 class="mb-0 fw-bold">
                            {{ number_format($balance->closing_balance, 2, ',', '.') }}
                            <small class="fs-6 text-muted fw-normal">{{ $balance->currency->symbol }}</small>
                        </h2>
                    </div>
                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center"
                         style="width:52px;height:52px;font-size:1.4rem;font-weight:700;color:#495057;">
                        {{ $balance->currency->symbol }}
                    </div>
                </div>
                {{-- Breakdown --}}
                <div class="px-4 py-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Fillestare</span>
                        <span class="small fw-semibold">{{ number_format($balance->opening_balance, 2, ',', '.') }} {{ $balance->currency->symbol }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-success small"><i class="ri-arrow-down-s-line"></i> Hyrje</span>
                        <span class="text-success small fw-semibold">+ {{ number_format($balance->sales_total, 2, ',', '.') }} {{ $balance->currency->symbol }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-danger small"><i class="ri-arrow-up-s-line"></i> Dalje</span>
                        <span class="text-danger small fw-semibold">- {{ number_format($balance->expenses_total, 2, ',', '.') }} {{ $balance->currency->symbol }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Transactions table --}}
<div class="card shadow-sm">
    <div class="card-header d-flex align-items-center justify-content-between bg-white">
        <h6 class="mb-0 fw-semibold">Lëvizjet</h6>
        <span class="text-muted small">{{ $register->transactions->count() }} gjithsej</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Koha</th>
                        <th>Monedha</th>
                        <th>Lloji</th>
                        <th class="text-end">Shuma</th>
                        <th>Përshkrimi</th>
                        <th class="pe-4">Nga</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($register->transactions as $tx)
                    <tr>
                        <td class="ps-4 text-muted small">{{ $tx->created_at->format('H:i') }}</td>
                        <td><strong>{{ $tx->currency->code }}</strong></td>
                        <td>
                            @if($tx->type === 'income')
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Hyrje</span>
                            @elseif($tx->type === 'expense')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Dalje</span>
                            @else
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Rregullim</span>
                            @endif
                        </td>
                        <td class="text-end fw-semibold">
                            @if($tx->type === 'expense')
                                <span class="text-danger">- {{ number_format($tx->amount, 2, ',', '.') }}</span>
                            @else
                                <span class="text-success">+ {{ number_format($tx->amount, 2, ',', '.') }}</span>
                            @endif
                        </td>
                        <td class="text-muted small">{{ $tx->description ?? '—' }}</td>
                        <td class="pe-4 text-muted small">{{ $tx->createdBy?->name ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="ri-inbox-2-line d-block fs-2 mb-2"></i>
                            Nuk ka lëvizje ende
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ── SHTO MODAL ──────────────────────────────────────────── --}}
<div class="modal fade" id="shtoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:#198754;color:#fff;">
                <h5 class="modal-title"><i class="ri-add-circle-line me-2"></i>Shto para në arkë</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('cash-register.bulk-add', $register) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Përshkrimi <span class="text-danger">*</span></label>
                        <input type="text" name="description" class="form-control" required
                               placeholder="P.sh. Hapje arka, depozitim…">
                    </div>
                    <p class="text-muted small mb-2">Vendos shumën për çdo monedhë (lër bosh ato që nuk ndryshojnë):</p>
                    @foreach($register->balances->sortBy('currency.code') as $balance)
                    <div class="mb-3">
                        <label class="form-label d-flex align-items-center gap-2">
                            <span class="badge bg-secondary fs-6">{{ $balance->currency->symbol }}</span>
                            <span class="fw-semibold">{{ $balance->currency->code }}</span>
                            <span class="text-muted small ms-auto">Gjendje: {{ number_format($balance->closing_balance, 2, ',', '.') }}</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">{{ $balance->currency->symbol }}</span>
                            <input type="number" name="amounts[{{ $balance->currency_id }}]"
                                   class="form-control" step="0.01" min="0" placeholder="0.00">
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Anulo</button>
                    <button type="submit" class="btn btn-success px-4">
                        <i class="ri-check-line me-1"></i> Shto
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── HIQ MODAL ──────────────────────────────────────────── --}}
<div class="modal fade" id="hiqModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="ri-indeterminate-circle-line me-2"></i>Hiq para nga arka</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('cash-register.bulk-remove', $register) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Përshkrimi <span class="text-danger">*</span></label>
                        <input type="text" name="description" class="form-control" required
                               placeholder="P.sh. Faturë furnizuesi, qira…">
                    </div>
                    <p class="text-muted small mb-2">Vendos shumën për çdo monedhë (lër bosh ato që nuk ndryshojnë):</p>
                    @foreach($register->balances->sortBy('currency.code') as $balance)
                    <div class="mb-3">
                        <label class="form-label d-flex align-items-center gap-2">
                            <span class="badge bg-secondary fs-6">{{ $balance->currency->symbol }}</span>
                            <span class="fw-semibold">{{ $balance->currency->code }}</span>
                            <span class="text-muted small ms-auto">Gjendje: {{ number_format($balance->closing_balance, 2, ',', '.') }}</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">{{ $balance->currency->symbol }}</span>
                            <input type="number" name="amounts[{{ $balance->currency_id }}]"
                                   class="form-control" step="0.01" min="0" placeholder="0.00">
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Anulo</button>
                    <button type="submit" class="btn btn-danger px-4">
                        <i class="ri-check-line me-1"></i> Hiq
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── CLOSE MODAL ─────────────────────────────────────────── --}}
@if($register->status === 'open')
<div class="modal fade" id="closeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="ri-lock-line me-2"></i>Mbyll Arkën</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('cash-register.close', $register) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="text-muted mb-3">Numëro fizikisht dhe vendos shumat sipas monedhave:</p>
                    @foreach($register->balances->sortBy('currency.code') as $balance)
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            {{ $balance->currency->code }} ({{ $balance->currency->symbol }})
                            <small class="text-muted fw-normal">— llogarit: {{ number_format($balance->closing_balance, 2, ',', '.') }}</small>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">{{ $balance->currency->symbol }}</span>
                            <input type="number" class="form-control"
                                   name="declared_amounts[{{ $balance->currency_id }}]"
                                   step="0.01" min="0" required
                                   value="{{ $balance->closing_balance }}">
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Anulo</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="ri-lock-line me-1"></i> Mbyll Arkën
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection
