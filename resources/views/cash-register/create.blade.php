@extends('layouts.app')

@section('title', 'Ndryshe Arka')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Ndryshe Arka</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('cash-register.index') }}">Arka</a></li>
                    <li class="breadcrumb-item active">Ndryshe</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 offset-lg-2">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Krijo Regjistër të Ri Arkë</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('cash-register.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="register_date" class="form-label">Data e Arkës <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('register_date') is-invalid @enderror"
                            id="register_date" name="register_date" value="{{ old('register_date', date('Y-m-d')) }}" required>
                        @error('register_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="seller_id" class="form-label">Punonjësi i Arkës</label>
                        <select class="form-select @error('seller_id') is-invalid @enderror"
                            id="seller_id" name="seller_id">
                            <option value="">-- Zgjidh Shitësin --</option>
                            @foreach($sellers as $seller)
                                <option value="{{ $seller->id }}" {{ old('seller_id') == $seller->id ? 'selected' : '' }}>
                                    {{ $seller->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('seller_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Saldot Fillestare sipas Monedhave</label>
                        <div class="row">
                            @foreach($currencies as $currency)
                                <div class="col-md-6 mb-2">
                                    <label class="form-label">
                                        {{ $currency->code }} ({{ $currency->symbol }})
                                    </label>
                                    <input type="number" class="form-control" 
                                        name="initial_balances[{{ $currency->id }}]"
                                        step="0.01" min="0" value="{{ old('initial_balances.' . $currency->id, 0) }}"
                                        placeholder="0.00">
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Shënime</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror"
                            id="notes" name="notes" rows="4" placeholder="Shënime opsionale...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-line me-1"></i> Krijo Arka
                        </button>
                        <a href="{{ route('cash-register.index') }}" class="btn btn-secondary">
                            <i class="ri-arrow-left-line me-1"></i> Kthehu
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
