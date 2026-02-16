@extends('layouts.app')

@section('title', 'Invoice Details')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Invoice Details</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('sales.index') }}">Sales</a></li>
                    <li class="breadcrumb-item active">{{ $sale->invoice_number }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Invoice: {{ $sale->invoice_number }}</h5>
                <div>
                    <a href="{{ route('sales.edit', $sale->id) }}" class="btn btn-primary btn-sm">
                        <i class="ri-pencil-line me-1"></i> Edit
                    </a>
                    <button onclick="window.print()" class="btn btn-info btn-sm">
                        <i class="ri-printer-line me-1"></i> Print
                    </button>
                    <a href="{{ route('sales.index') }}" class="btn btn-secondary btn-sm">
                        <i class="ri-arrow-left-line me-1"></i> Back
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Invoice Info -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th style="width: 180px;">Invoice Number:</th>
                                <td><strong class="text-primary">{{ $sale->invoice_number }}</strong></td>
                            </tr>
                            <tr>
                                <th>Invoice Date:</th>
                                <td>{{ $sale->invoice_date->format('d/m/Y') }}</td>
                            </tr>
                            <tr>
                                <th>Delivery Date:</th>
                                <td>{{ $sale->delivery_date ? $sale->delivery_date->format('d/m/Y') : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Due Date:</th>
                                <td>{{ $sale->due_date ? $sale->due_date->format('d/m/Y') : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Client:</th>
                                <td>{{ $sale->partner->name }}</td>
                            </tr>
                            <tr>
                                <th>Seller:</th> <!-- SHTO KËTË -->
                                <td>{{ $sale->seller ? $sale->seller->name : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Warehouse:</th>
                                <td>{{ $sale->warehouse->name }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th style="width: 180px;">Sale Status:</th>
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
                            </tr>
                            <tr>
                                <th>Payment Status:</th>
                                <td>
                                    @if($sale->payment_status === 'Paid')
                                    <span class="badge bg-success">Paid</span>
                                    @elseif($sale->payment_status === 'Partial')
                                    <span class="badge bg-warning">Partially Paid</span>
                                    @else
                                    <span class="badge bg-danger">Unpaid</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Payment Method:</th>
                                <td>{{ $sale->payment_method }}</td>
                            </tr>
                            <tr>
                                <th>Vendi i Blerjes:</th>
                                <td>
                                    @if($sale->purchase_location == 'shop')
                                    <span class="badge bg-success">🏪 Dyqan</span>
                                    @else
                                    <span class="badge bg-info">🌐 Online</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Payment Term:</th>
                                <td>{{ $sale->payment_term ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Currency:</th>
                                <td>{{ $sale->currency->code }} ({{ $sale->currency->symbol }})</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Products Table -->
                <h6 class="mb-3">Products</h6>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Warehouse</th>
                                <th>Category</th>
                                <th>Brand</th>
                                <th>Qty</th>
                                <th>Unit Type</th>
                                <th>Unit Price</th>
                                <th>Discount</th>
                                <th>Tax</th>
                                <th>Line Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sale->items as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->product_name }}</strong>
                                    @if($item->storage || $item->ram || $item->color)
                                    <br>
                                    <small class="text-muted">
                                        @if($item->storage){{ $item->storage }}@endif
                                        @if($item->ram) | {{ $item->ram }}@endif
                                        @if($item->color) | {{ $item->color }}@endif
                                    </small>
                                    @endif

                                    @if($item->imei_numbers && count($item->imei_numbers) > 0)
                                    <br>
                                    <button class="btn btn-sm btn-outline-info mt-2" type="button" data-bs-toggle="collapse" data-bs-target="#imei-{{ $item->id }}">
                                        <i class="ri-barcode-line me-1"></i> Show IMEI ({{ count($item->imei_numbers) }})
                                    </button>
                                    <div class="collapse mt-2" id="imei-{{ $item->id }}">
                                        <div class="card card-body bg-light">
                                            <small><strong>IMEI Numbers:</strong></small>
                                            <ul class="mb-0 ps-3">
                                                @foreach($item->imei_numbers as $imei)
                                                <li><code>{{ $imei }}</code></li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                    @endif
                                </td>
                                <td>{{ $item->warehouse ? $item->warehouse->name : '-' }}</td>
                                <td>{{ $item->category ? $item->category->name : '-' }}</td>
                                <td>{{ $item->brand ? $item->brand->name : '-' }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ $item->unit_type }}</td>
                                <td>{{ number_format($item->unit_price, 2) }}</td>
                                <td>{{ number_format($item->discount, 2) }}</td>
                                <td>{{ number_format($item->tax, 2) }}</td>
                                <td><strong>{{ number_format($item->line_total, 2) }}</strong></td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="9" class="text-end"><strong>Sub Total:</strong></td>
                                <td><strong>{{ number_format($sale->subtotal, 2) }} {{ $sale->currency->symbol }}</strong></td>
                            </tr>
                            <tr>
                                <td colspan="9" class="text-end"><strong>Tax:</strong></td>
                                <td><strong>{{ number_format($sale->tax, 2) }} {{ $sale->currency->symbol }}</strong></td>
                            </tr>
                            <tr>
                                <td colspan="9" class="text-end"><strong>Discount:</strong></td>
                                <td><strong>{{ number_format($sale->discount, 2) }} {{ $sale->currency->symbol }}</strong></td>
                            </tr>
                            <tr class="table-success">
                                <td colspan="9" class="text-end"><strong>Total Amount:</strong></td>
                                <td><strong class="text-success fs-5">{{ number_format($sale->total_amount, 2) }} {{ $sale->currency->symbol }}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Description -->
                @if($sale->description)
                <div class="mt-4">
                    <h6>Description:</h6>
                    <p class="text-muted">{{ $sale->description }}</p>
                </div>
                @endif

                <!-- Notes -->
                @if($sale->notes)
                <div class="mt-3">
                    <h6>Notes:</h6>
                    <p class="text-muted">{{ $sale->notes }}</p>
                </div>
                @endif

                <!-- Timestamps -->
                <div class="mt-4 pt-3 border-top">
                    <div class="row text-muted small">
                        <div class="col-md-6">
                            <strong>Created:</strong> {{ $sale->created_at->format('d/m/Y H:i') }}
                        </div>
                        <div class="col-md-6 text-md-end">
                            <strong>Updated:</strong> {{ $sale->updated_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    @media print {

        .page-title-box,
        .card-header,
        .btn,
        .breadcrumb {
            display: none !important;
        }

        .card {
            border: none !important;
            box-shadow: none !important;
        }
    }
</style>
@endpush