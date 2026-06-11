@extends('layouts.app')

@section('title', 'Detajet e Arkës')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Detajet e Arkës - {{ $register->register_date->format('d/m/Y') }}</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('cash-register.index') }}">Arka</a></li>
                    <li class="breadcrumb-item active">Detajet</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Header Info -->
<div class="row mb-3">
    <div class="col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-2">Punonjësi</p>
                        <h5>{{ $register->employee?->name ?? 'E panjohur' }}</h5>
                    </div>
                    <i class="ri-user-line fs-2 text-muted"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-2">Saldo Fillestare</p>
                        <h5>{{ number_format($register->total_opening, 2, ',', '.') }} L</h5>
                    </div>
                    <i class="ri-bank-line fs-2 text-info"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-2">Shitje</p>
                        <h5 class="text-success">{{ number_format($register->total_transactions, 2, ',', '.') }} L</h5>
                    </div>
                    <i class="ri-shopping-cart-line fs-2 text-success"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-2">Saldo Përfundimtare</p>
                        <h5><strong>{{ number_format($register->total_closing, 2, ',', '.') }} L</strong></h5>
                    </div>
                    <i class="ri-money-dollar-circle-line fs-2 text-primary"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status and Actions -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <span class="me-3">
                        <strong>Gjendje:</strong>
                        @if($register->status === 'open')
                            <span class="badge bg-info">E Hapur</span>
                        @elseif($register->status === 'closed')
                            <span class="badge bg-warning">E Mbyllur</span>
                        @else
                            <span class="badge bg-success">E Balancuar</span>
                        @endif
                    </span>
                </div>
                <div class="d-flex gap-2">
                    @if($register->status === 'open')
                        <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#closeModal">
                            <i class="ri-lock-line me-1"></i> Mbyll Arkën
                        </button>
                    @elseif($register->status === 'closed')
                        <form action="{{ route('cash-register.balance', $register) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success">
                                <i class="ri-check-double-line me-1"></i> Balancuo
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('cash-register.edit', $register) }}" class="btn btn-sm btn-primary">
                        <i class="ri-pencil-line me-1"></i> Redakto
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Balances by Currency -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Saldot sipas Monedhave</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Monedha</th>
                                <th class="text-end">Saldo Fillestare</th>
                                <th class="text-end">Shitje</th>
                                <th class="text-end">Rregullime</th>
                                <th class="text-end">Saldo Përfundimtare</th>
                                <th>Aksione</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($register->balances as $balance)
                            <tr>
                                <td>
                                    <strong>{{ $balance->currency->code }}</strong> ({{ $balance->currency->symbol }})
                                </td>
                                <td class="text-end">{{ number_format($balance->opening_balance, 2, ',', '.') }}</td>
                                <td class="text-end text-success">{{ number_format($balance->sales_total, 2, ',', '.') }}</td>
                                <td class="text-end text-warning">{{ number_format($balance->adjustments_total, 2, ',', '.') }}</td>
                                <td class="text-end">
                                    <strong>{{ number_format($balance->closing_balance, 2, ',', '.') }}</strong>
                                </td>
                                <td>
                                    @if($register->status === 'open')
                                        <button type="button" class="btn btn-xs btn-outline-primary" 
                                            data-bs-toggle="modal" data-bs-target="#adjustmentModal"
                                            data-currency-id="{{ $balance->currency_id }}"
                                            data-currency-code="{{ $balance->currency->code }}">
                                            <i class="ri-add-line"></i> Rregullim
                                        </button>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Transactions -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Transaksionet</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Koha</th>
                                <th>Monedha</th>
                                <th>Lloji</th>
                                <th class="text-end">Shuma</th>
                                <th>Përshkrimi</th>
                                <th>Përdorues</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($register->transactions as $transaction)
                            <tr>
                                <td>{{ $transaction->created_at->format('H:i:s') }}</td>
                                <td><strong>{{ $transaction->currency->code }}</strong></td>
                                <td>
                                    <span class="badge {{ $transaction->getTypeBadgeClass() }}">
                                        {{ $transaction->getTypeLabel() }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    @if($transaction->type === 'expense')
                                        <span class="text-danger">- {{ number_format($transaction->amount, 2, ',', '.') }}</span>
                                    @else
                                        <span class="text-success">+ {{ number_format($transaction->amount, 2, ',', '.') }}</span>
                                    @endif
                                </td>
                                <td>{{ $transaction->description ?? '-' }}</td>
                                <td>{{ $transaction->createdBy?->name ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    Nuk ka transaksione
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Adjustment Modal -->
<div class="modal fade" id="adjustmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-white">
                    <i class="ri-edit-line me-2"></i> Shto Rregullim
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('cash-register.adjustment', $register) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="currency_id" id="currency_id">

                    <div class="mb-3">
                        <label class="form-label">Monedha</label>
                        <input type="text" class="form-control" id="currency_display" disabled>
                    </div>

                    <div class="mb-3">
                        <label for="amount" class="form-label">Shuma <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="amount" name="amount" step="0.01" required placeholder="0.00">
                        <small class="text-muted">Pozitiv për shtim, negativ për zbritje</small>
                    </div>

                    <div class="mb-3">
                        <label for="reason" class="form-label">Arsyeja <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="reason" name="reason" rows="3" required placeholder="Shpjego pse bëhet ky rregullim..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anulo</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-check-line me-1"></i> Shto Rregullim
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Close Cash Register Modal -->
<div class="modal fade" id="closeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="ri-lock-line me-2"></i> Mbyll Arkën
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('cash-register.close', $register) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="text-muted mb-3">Vendos shumat e deklaruara sipas monedhave:</p>
                    
                    @foreach($register->balances as $balance)
                    <div class="mb-3">
                        <label for="declared_{{ $balance->currency_id }}" class="form-label">
                            {{ $balance->currency->code }} ({{ $balance->currency->symbol }})
                        </label>
                        <div class="input-group">
                            <input type="number" class="form-control" 
                                id="declared_{{ $balance->currency_id }}"
                                name="declared_amounts[{{ $balance->currency_id }}]"
                                step="0.01" required
                                placeholder="0.00"
                                value="{{ $balance->closing_balance }}">
                            <span class="input-group-text">{{ $balance->currency->symbol }}</span>
                        </div>
                        <small class="text-muted">Llogarit: {{ number_format($balance->closing_balance, 2) }}</small>
                    </div>
                    @endforeach
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anulo</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="ri-lock-line me-1"></i> Mbyll Arkën
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Handle adjustment modal
    var adjustmentModal = document.getElementById('adjustmentModal');
    adjustmentModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var currencyId = button.getAttribute('data-currency-id');
        var currencyCode = button.getAttribute('data-currency-code');

        document.getElementById('currency_id').value = currencyId;
        document.getElementById('currency_display').value = currencyCode;
    });
</script>
@endpush
