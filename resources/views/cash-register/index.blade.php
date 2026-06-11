@extends('layouts.app')

@section('title', 'Arka - Regjistrat Ditore')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Regjistrat e Arkës</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Arka</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Lista e Regjistrave Ditore</h5>
                <a href="{{ route('cash-register.create') }}" class="btn btn-primary btn-sm">
                    <i class="ri-add-line me-1"></i> Ndryshe Arka
                </a>
            </div>
            <div class="card-body">
                <!-- Filters -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Gjendje</label>
                        <select class="form-select form-select-sm" onchange="filterTable()">
                            <option value="">-- Të Gjitha --</option>
                            <option value="open">E Hapur</option>
                            <option value="closed">E Mbyllur</option>
                            <option value="balanced">E Balancuar</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nga Data</label>
                        <input type="date" class="form-control form-control-sm" id="fromDate" onchange="filterTable()">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Deri Data</label>
                        <input type="date" class="form-control form-control-sm" id="toDate" onchange="filterTable()">
                    </div>
                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Data</th>
                                <th>Punonjësi</th>
                                <th>Saldo Fillestare</th>
                                <th>Saldo Përfundimtare</th>
                                <th>Shitje</th>
                                <th>Rregullime</th>
                                <th>Gjendje</th>
                                <th>Aksione</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($registers as $register)
                            <tr>
                                <td>
                                    <strong>{{ $register->register_date->format('d/m/Y') }}</strong>
                                </td>
                                <td>{{ $register->employee?->name ?? 'E panjohur' }}</td>
                                <td class="text-end">
                                    {{ number_format($register->total_opening, 2, ',', '.') }} L
                                </td>
                                <td class="text-end">
                                    <strong>{{ number_format($register->total_closing, 2, ',', '.') }} L</strong>
                                </td>
                                <td class="text-end text-success">
                                    <strong>{{ number_format($register->total_transactions, 2, ',', '.') }} L</strong>
                                </td>
                                <td class="text-end text-warning">
                                    {{ number_format($register->total_adjustments, 2, ',', '.') }} L
                                </td>
                                <td>
                                    @if($register->status === 'open')
                                        <span class="badge bg-info">E Hapur</span>
                                    @elseif($register->status === 'closed')
                                        <span class="badge bg-warning">E Mbyllur</span>
                                    @else
                                        <span class="badge bg-success">E Balancuar</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('cash-register.show', $register) }}" class="btn btn-sm btn-info" title="Shiko">
                                        <i class="ri-eye-line"></i>
                                    </a>
                                    <a href="{{ route('cash-register.edit', $register) }}" class="btn btn-sm btn-warning" title="Redakto">
                                        <i class="ri-pencil-line"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="ri-inbox-line fs-1"></i>
                                    <p class="mt-2">Nuk ka regjitra të arkës</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($registers->hasPages())
                <div class="mt-3">
                    {{ $registers->links('pagination::bootstrap-5') }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function filterTable() {
        const status = document.querySelector('[onchange="filterTable()"]').value || '';
        const fromDate = document.getElementById('fromDate').value || '';
        const toDate = document.getElementById('toDate').value || '';

        const params = new URLSearchParams({
            status: status,
            from_date: fromDate,
            to_date: toDate,
        });

        window.location.href = '{{ route("cash-register.index") }}?' + params.toString();
    }
</script>
@endpush
