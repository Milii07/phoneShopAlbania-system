@extends('layouts.app')

@section('title', 'Detajet e Blerjes')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Detajet e Blerjes</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('purchases.index') }}">Blerjet</a></li>
                    <li class="breadcrumb-item active">{{ $purchase->purchase_number }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Blerja: {{ $purchase->purchase_number }}</h5>
                <div>
                    <a href="{{ route('purchases.edit', $purchase->id) }}" class="btn btn-primary btn-sm">
                        <i class="ri-pencil-line me-1"></i> Modifiko
                    </a>
                    <a href="{{ route('purchases.index') }}" class="btn btn-secondary btn-sm">
                        <i class="ri-arrow-left-line me-1"></i> Kthehu
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Purchase Info -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th style="width: 150px;">Nr. Blerjes:</th>
                                <td><strong>{{ $purchase->purchase_number }}</strong></td>
                            </tr>
                            <tr>
                                <th>Data:</th>
                                <td>{{ $purchase->purchase_date->format('d/m/Y') }}</td>
                            </tr>
                            <tr>
                                <th>Due Date:</th>
                                <td>{{ $purchase->due_date ? $purchase->due_date->format('d/m/Y') : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Supplier:</th>
                                <td>{{ $purchase->partner->name }}</td>
                            </tr>
                            <tr>
                                <th>Warehouse:</th>
                                <td>{{ $purchase->warehouse->name }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th style="width: 150px;">Order Status:</th>
                                <td>
                                    @if($purchase->order_status === 'Received')
                                    <span class="badge bg-success">Received</span>
                                    @elseif($purchase->order_status === 'Pending')
                                    <span class="badge bg-warning">Pending</span>
                                    @else
                                    <span class="badge bg-danger">Cancelled</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Payment Status:</th>
                                <td>
                                    @if($purchase->payment_status === 'Paid')
                                    <span class="badge bg-success">Paid</span>
                                    @elseif($purchase->payment_status === 'Partial')
                                    <span class="badge bg-warning">Partial</span>
                                    @else
                                    <span class="badge bg-danger">Unpaid</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Payment Method:</th>
                                <td>{{ $purchase->payment_method }}</td>
                            </tr>
                            <tr>
                                <th>Currency:</th>
                                <td>{{ $purchase->currency->code }} ({{ $purchase->currency->symbol }})</td>
                            </tr>
                            <tr>
                                <th>Attachment:</th>
                                <td>
                                    @if($purchase->attachment)
                                    <a href="{{ Storage::url($purchase->attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="ri-download-line"></i> Shkarko
                                    </a>
                                    @else
                                    -
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Products Table -->
                <h6 class="mb-3">Produktet</h6>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Produkti</th>
                                <th>Qty</th>
                                <th>Unit Type</th>
                                <th>Unit Cost</th>
                                <th>Sales Price</th>
                                <th>Discount</th>
                                <th>Tax</th>
                                <th>Line Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchase->items as $item)
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
                                    <button class="btn btn-sm btn-outline-info mt-2" type="button" data-bs-toggle="collapse" data-bs-target="#imei-{{ $item->id }}" aria-expanded="false">
                                        <i class="ri-barcode-line me-1"></i> Shfaq IMEI ({{ count($item->imei_numbers) }})
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
                                <td>{{ $item->quantity }}</td>
                                <td>{{ $item->unit_type }}</td>
                                <td>{{ number_format($item->unit_cost, 2) }}</td>
                                <td>{{ number_format($item->selling_price, 2) }}</td>
                                <td>{{ number_format($item->discount, 2) }}</td>
                                <td>{{ number_format($item->tax, 2) }}</td>
                                <td><strong>{{ number_format($item->line_total, 2) }}</strong></td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6" class="text-end"><strong>Sub Total:</strong></td>
                                <td><strong>{{ number_format($purchase->subtotal, 2) }} {{ $purchase->currency->symbol }}</strong></td>
                            </tr>
                            <tr>
                                <td colspan="6" class="text-end"><strong>Tax:</strong></td>
                                <td><strong>{{ number_format($purchase->tax, 2) }} {{ $purchase->currency->symbol }}</strong></td>
                            </tr>
                            <tr>
                                <td colspan="6" class="text-end"><strong>Discount:</strong></td>
                                <td><strong>{{ number_format($purchase->discount, 2) }} {{ $purchase->currency->symbol }}</strong></td>
                            </tr>
                            <tr class="table-success">
                                <td colspan="6" class="text-end"><strong>Total Amount:</strong></td>
                                <td><strong class="text-success fs-5">{{ number_format($purchase->total_amount, 2) }} {{ $purchase->currency->symbol }}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Notes -->
                @if($purchase->notes)
                <div class="mt-4">
                    <h6>Shënime:</h6>
                    <p class="text-muted">{{ $purchase->notes }}</p>
                </div>
                @endif

                <!-- Timestamps -->
                <div class="mt-4 pt-3 border-top">
                    <div class="row text-muted small">
                        <div class="col-md-6">
                            <strong>Krijuar më:</strong> {{ $purchase->created_at->format('d/m/Y H:i') }}
                        </div>
                        <div class="col-md-6 text-md-end">
                            <strong>Përditësuar më:</strong> {{ $purchase->updated_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection