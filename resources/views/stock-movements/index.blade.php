@extends('layouts.app')

@section('title', 'Stoku i Produkteve')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Stoku i Produkteve</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Stoku i Produkteve</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-xl-6 col-md-6">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-uppercase fw-medium text-muted mb-0">Numri i Produkteve</p>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-22 fw-semibold ff-secondary mb-0">
                            {{ $stats['total_products'] ?? 0 }}
                        </h4>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-primary-subtle rounded fs-3">
                            <i class="ri-stack-line text-primary"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-6 col-md-6">
        <div class="card card-animate">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-uppercase fw-medium text-muted mb-0">Totali i Stokut</p>
                    </div>
                </div>
                <div class="d-flex align-items-end justify-content-between mt-4">
                    <div>
                        <h4 class="fs-22 fw-semibold ff-secondary mb-0 text-success">
                            {{ $stats['total_stock'] ?? 0 }}
                        </h4>
                    </div>
                    <div class="avatar-sm flex-shrink-0">
                        <span class="avatar-title bg-success-subtle rounded fs-3">
                            <i class="ri-stack-line text-success"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h5 class="card-title mb-0 flex-grow-1">Lista e Lëvizjeve</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('stock-movements.export.pdf') }}@if(request()->query())?{{ http_build_query(request()->query()) }}@endif" class="btn btn-outline-primary btn-sm ajax-export" data-type="pdf" data-url="{{ route('stock-movements.export.pdf') }}@if(request()->query())?{{ http_build_query(request()->query()) }}@endif">
                        <i class="ri-file-paper-line align-middle me-1"></i> Export PDF
                    </a>
                    <a href="{{ route('stock-movements.export.xlsx') }}@if(request()->query())?{{ http_build_query(request()->query()) }}@endif" class="btn btn-outline-secondary btn-sm ajax-export" data-type="xlsx" data-url="{{ route('stock-movements.export.xlsx') }}@if(request()->query())?{{ http_build_query(request()->query()) }}@endif">
                        <i class="ri-file-excel-2-line align-middle me-1"></i> Export XLSX
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ri-check-line align-middle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="ri-error-warning-line align-middle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <!-- Filters: warehouse + category -->
                <form method="GET" action="{{ route('stock-movements.index') }}" class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Warehouse</label>
                        <select name="warehouse_id" class="form-select">
                            <option value="">Të gjitha</option>
                            @foreach($warehouses as $w)
                            <option value="{{ $w->id }}" {{ request('warehouse_id') == $w->id ? 'selected' : '' }}>
                                {{ $w->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Kategoria</label>
                        <select name="category_id" class="form-select">
                            <option value="">Të gjitha</option>
                            @foreach($categories as $c)
                            <option value="{{ $c->id }}" {{ request('category_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="d-flex gap-2 w-100">
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-search-line"></i> Filtroni
                            </button>
                            <a href="{{ route('stock-movements.index') }}" class="btn btn-secondary">
                                <i class="ri-refresh-line"></i> Pastro
                            </a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-nowrap align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Produkti</th>
                                <th>Kategoria</th>
                                <th>Marka</th>
                                <th>Çmimi</th>
                                <th>Totali Stok</th>
                                @foreach($warehouses as $w)
                                <th>{{ $w->name }}</th>
                                @endforeach
                                <th>Veprime</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $product)
                            <tr>
                                <td>
                                    <strong>{{ $product->name }}</strong>
                                    @if($product->storage || $product->color)
                                    <br>
                                    <small class="text-muted">
                                        @if($product->storage){{ $product->storage }}@endif
                                        @if($product->color) | {{ $product->color }}@endif
                                    </small>
                                    @endif
                                </td>
                                <td>{{ $product->category?->name ?? '-' }}</td>
                                <td>{{ $product->brand?->name ?? '-' }}</td>
                                <td>{{ number_format($product->unit_price ?? $product->price ?? 0, 2) }}</td>
                                <td>{{ $product->getTotalQuantityAttribute() }}</td>
                                @foreach($warehouses as $w)
                                @php
                                $wh = $product->warehouses->firstWhere('id', $w->id);
                                $qty = $wh && isset($wh->pivot->quantity) ? $wh->pivot->quantity : 0;
                                @endphp
                                <td>{{ $qty }}</td>
                                @endforeach
                                <td class="align-top">
                                    <div class="d-flex flex-column gap-1">
                                        <a href="{{ route('products.show', $product->id) }}" class="btn btn-sm btn-info">
                                            <i class="ri-eye-line"></i>
                                        </a>
                                        @php $unsold = $product->unsold_imeis ?? []; @endphp
                                        @if(count($unsold) > 0)
                                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#unsold-imei-{{ $product->id }}">
                                            IMEI ({{ count($unsold) }})
                                        </button>
                                        @else
                                        <span class="badge bg-secondary">N/A</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @if(!empty($product->unsold_imeis) && count($product->unsold_imeis) > 0)
                            <tr class="table-light">
                                <td colspan="{{ 6 + ($warehouses->count()) }}">
                                    <div class="collapse" id="unsold-imei-{{ $product->id }}">
                                        <div class="py-2">
                                            <strong>IMEI në stok (pa u shitur):</strong>
                                            <ul class="mb-0">
                                                @foreach($product->unsold_imeis as $imei)
                                                <li><code>{{ $imei }}</code></li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endif
                            @empty
                            <tr>
                                <td colspan="{{ 6 + ($warehouses->count()) }}" class="text-center py-4 text-muted">
                                    <i class="ri-inbox-line fs-1"></i>
                                    <p class="mt-2">Nuk ka produkte të disponueshme</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($products->hasPages())
                <div class="mt-3">
                    {{ $products->links('pagination::bootstrap-5') }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection