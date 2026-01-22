@extends('layouts.app')

@section('title', 'Shto Warehouse')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Shto Warehouse</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('warehouses.index') }}">Warehouses</a></li>
                    <li class="breadcrumb-item active">Shto</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Informacioni i Warehouse</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('warehouses.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label">Emri <span class="text-danger">*</span></label>
                        <input type="text"
                            class="form-control @error('name') is-invalid @enderror"
                            id="name"
                            name="name"
                            value="{{ old('name') }}"
                            placeholder="Shkruani emrin e warehouse"
                            required>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="location" class="form-label">Lokacioni</label>
                        <input type="text"
                            class="form-control @error('location') is-invalid @enderror"
                            id="location"
                            name="location"
                            value="{{ old('location') }}"
                            placeholder="Shkruani lokacionin">
                        @error('location')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Përshkrimi</label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                            id="description"
                            name="description"
                            rows="3"
                            placeholder="Shkruani përshkrimin">{{ old('description') }}</textarea>
                        @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="text-end">
                        <a href="{{ route('warehouses.index') }}" class="btn btn-light">
                            <i class="ri-arrow-left-line align-middle me-1"></i> Anulo
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="ri-save-line align-middle me-1"></i> Ruaj Warehouse
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection