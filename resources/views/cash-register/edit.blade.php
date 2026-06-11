@extends('layouts.app')

@section('title', 'Redakto Arkën')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Redakto Arkën</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('cash-register.index') }}">Arka</a></li>
                    <li class="breadcrumb-item active">Redakto</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 offset-lg-2">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Redakto Detajet e Arkës</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('cash-register.update', $register) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Data e Arkës</label>
                        <input type="date" class="form-control" value="{{ $register->register_date->format('Y-m-d') }}" disabled>
                        <small class="text-muted">Data nuk mund të ndryshohet</small>
                    </div>

                    <div class="mb-3">
                        <label for="employee_id" class="form-label">Punonjësi i Arkës</label>
                        <select class="form-select @error('employee_id') is-invalid @enderror"
                            id="employee_id" name="employee_id">
                            <option value="">-- Zgjidh Punonjës --</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ old('employee_id', $register->employee_id) == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('employee_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Shënime</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror"
                            id="notes" name="notes" rows="4">{{ old('notes', $register->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Display current balances (read-only) -->
                    <div class="mb-3">
                        <label class="form-label">Saldot Aktuale sipas Monedhave</label>
                        <div class="row">
                            @foreach($register->balances as $balance)
                                <div class="col-md-6 mb-2">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <p class="mb-1">
                                                <strong>{{ $balance->currency->code }} ({{ $balance->currency->symbol }})</strong>
                                            </p>
                                            <p class="mb-0 text-muted">
                                                Fillestare: {{ number_format($balance->opening_balance, 2) }}<br>
                                                Përfundimtare: <strong>{{ number_format($balance->closing_balance, 2) }}</strong>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-line me-1"></i> Ruaj Ndryshimet
                        </button>
                        <a href="{{ route('cash-register.show', $register) }}" class="btn btn-secondary">
                            <i class="ri-arrow-left-line me-1"></i> Kthehu
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
