@extends('layouts.app')

@section('title', 'Invoice List')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">INVOICE LIST</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item">Sales</li>
                    <li class="breadcrumb-item active">Invoice List</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Invoices</h5>
                    <a href="{{ route('sales.create') }}" class="btn btn-primary">
                        <i class="ri-add-line me-1"></i> Create Invoice
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Filter Tabs -->
                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link {{ !request('status') || request('status') == 'All' ? 'active' : '' }}"
                            href="{{ route('sales.index', ['status' => 'All']) }}">All</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('status') == 'Draft' ? 'active' : '' }}"
                            href="{{ route('sales.index', ['status' => 'Draft']) }}">Draft</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('status') == 'PrePaid' ? 'active' : '' }}"
                            href="{{ route('sales.index', ['status' => 'PrePaid']) }}">PrePaid</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('status') == 'Confirmed' ? 'active' : '' }}"
                            href="{{ route('sales.index', ['status' => 'Confirmed']) }}">Confirmed</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request('status') == 'Rejected' ? 'active' : '' }}"
                            href="{{ route('sales.index', ['status' => 'Rejected']) }}">Rejected</a>
                    </li>
                </ul>

                <!-- Filters -->
                <form method="GET" action="{{ route('sales.index') }}" class="row g-3 mb-3">
                    <input type="hidden" name="status" value="{{ request('status', 'All') }}">

                    <div class="col-md-3">
                        <input type="text"
                            class="form-control"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Search Inv number">
                    </div>

                    <div class="col-md-2">
                        <select class="form-select select2-warehouse" name="warehouse_id" id="sales_filter_warehouse">
                            <option value="">All Warehouses</option>
                            @foreach($warehouses as $w)
                            <option value="{{ $w->id }}" {{ request('warehouse_id') == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <select class="form-select" name="payment_status">
                            <option value="">Payment Status</option>
                            <option value="Paid" {{ request('payment_status') == 'Paid' ? 'selected' : '' }}>Paid</option>
                            <option value="Unpaid" {{ request('payment_status') == 'Unpaid' ? 'selected' : '' }}>Unpaid</option>
                            <option value="Partial" {{ request('payment_status') == 'Partial' ? 'selected' : '' }}>Partial</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <input type="date"
                            class="form-control"
                            name="date"
                            value="{{ request('date') }}">
                    </div>

                    <div class="col-md-3">
                        <select class="form-select" name="partner_id">
                            <option value="">Select Client</option>
                            @foreach($partners as $partner)
                            <option value="{{ $partner->id }}" {{ request('partner_id') == $partner->id ? 'selected' : '' }}>
                                {{ $partner->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ri-filter-line me-1"></i> All Filters
                        </button>
                    </div>
                </form>

                <!-- Export Buttons -->
                <div class="d-flex justify-content-end gap-2 mb-3">
                    <button class="btn btn-success btn-sm">
                        <i class="ri-file-excel-line me-1"></i> Export Excel
                    </button>
                    <button class="btn btn-danger btn-sm">
                        <i class="ri-file-pdf-line me-1"></i> Export PDF
                    </button>
                    <button class="btn btn-info btn-sm" onclick="window.print()">
                        <i class="ri-printer-line me-1"></i> Print
                    </button>
                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <table id="sales_table" class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Invoice Number</th>
                                <th>Client</th>
                                <th>Invoice Date</th>
                                <th>Due Date</th>
                                <th>Grand Total</th>
                                <th>Status</th>
                                <th>Payment Status</th>
                                <th class="no-sort">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sales as $sale)
                            <tr>
                                <td>{{ $sale->id }}</td>
                                <td>
                                    <a href="{{ route('sales.show', $sale->id) }}" class="text-primary fw-bold">
                                        {{ $sale->invoice_number }}
                                    </a>
                                </td>
                                <td>{{ $sale->partner->name }}</td>
                                <td>{{ $sale->invoice_date->format('d-m-Y') }}</td>
                                <td>{{ $sale->due_date ? $sale->due_date->format('d-m-Y') : '-' }}</td>
                                <td>
                                    <strong>{{ $sale->currency->symbol }} {{ number_format($sale->total_amount, 2) }}</strong>
                                </td>
                                <td>
                                    @if($sale->sale_status === 'Confirmed')
                                    <span class="badge bg-success">Completed</span>
                                    @elseif($sale->sale_status === 'Draft')
                                    <span class="badge bg-secondary">Draft</span>
                                    @elseif($sale->sale_status === 'PrePaid')
                                    <span class="badge bg-info">PrePaid</span>
                                    @else
                                    <span class="badge bg-danger">Rejected</span>
                                    @endif
                                </td>
                                <td>
                                    @if($sale->payment_status === 'Paid')
                                    <span class="badge bg-success">Paid</span>
                                    @elseif($sale->payment_status === 'Partial')
                                    <span class="badge bg-warning">Partially Paid</span>
                                    @else
                                    <span class="badge bg-danger">Unpaid</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                            Action
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('sales.show', $sale->id) }}">
                                                    <i class="ri-eye-line me-1"></i> View
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('sales.edit', $sale->id) }}">
                                                    <i class="ri-pencil-line me-1"></i> Edit
                                                </a>
                                            </li>
                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="#" onclick="event.preventDefault(); if(confirm('Are you sure?')) document.getElementById('delete-form-{{ $sale->id }}').submit();">
                                                    <i class="ri-delete-bin-line me-1"></i> Delete
                                                </a>
                                                <form id="delete-form-{{ $sale->id }}" action="{{ route('sales.destroy', $sale->id) }}" method="POST" style="display: none;">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        Showing {{ $sales->firstItem() ?? 0 }} to {{ $sales->lastItem() ?? 0 }} of {{ $sales->total() }} entries
                    </div>
                    <div>
                        {{ $sales->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        try {
            if (window.jQuery && jQuery.fn.select2) {
                jQuery('#sales_filter_warehouse').select2({
                    placeholder: 'Filter by warehouse',
                    allowClear: true,
                    width: 'resolve'
                }).on('change', function() {
                    jQuery(this).closest('form').submit();
                });
            }
        } catch (e) {
            console.warn('Select2 init failed for sales:', e);
        }
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        try {
            if (window.jQuery && jQuery.fn.DataTable) {
                jQuery('#sales_table').DataTable({
                    paging: false,
                    info: false,
                    lengthChange: false,
                    searching: true,
                    order: [
                        [0, 'desc']
                    ],
                    columnDefs: [{
                        orderable: false,
                        targets: 'no-sort'
                    }]
                });
            }
        } catch (e) {
            console.warn('DataTables init failed for sales:', e);
        }
    });
</script>
@endpush