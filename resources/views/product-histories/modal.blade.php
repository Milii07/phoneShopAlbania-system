<!-- Product History Modal Content -->
<div class="row">
    <div class="col-12">
        <h5 class="mb-3">
            @if($product->category)
                <span class="badge bg-light text-dark">{{ $product->category->name }}</span>
            @endif
            {{ $product->name }}
        </h5>

        <div class="row mb-3">
            <div class="col-md-6">
                <p><strong>Çmimi i Blerjës:</strong> {{ number_format($product->purchase_price, 2) }} €</p>
            </div>
            <div class="col-md-6">
                <p><strong>Çmimi i Shitjës:</strong> {{ number_format($product->selling_price, 2) }} €</p>
            </div>
        </div>

        <!-- Timeline -->
        <div class="timeline-container mt-4">
            @if($histories->count() > 0)
                <div class="position-relative ps-3">
                    @foreach($histories as $history)
                        <div class="timeline-item mb-4">
                            <!-- Timeline line -->
                            <div class="timeline-line"></div>

                            <!-- Timeline point (dot) -->
                            <div class="timeline-dot">
                                <i class="{{ $history->action_icon }}"></i>
                            </div>

                            <!-- Content -->
                            <div class="timeline-content">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1">
                                            <span class="badge bg-{{ $history->action_badge_color }}">
                                                {{ $history->action_type_name }}
                                            </span>
                                        </h6>
                                        <small class="text-muted">
                                            <i class="ri-calendar-line"></i>
                                            {{ $history->created_at->format('d/m/Y H:i') }}
                                        </small>
                                    </div>
                                    <span class="badge bg-{{ $history->status_badge_color }}">
                                        {{ $history->status_name }}
                                    </span>
                                </div>

                                <div class="card bg-light border-0 mt-2">
                                    <div class="card-body p-2">
                                        <div class="row g-2 text-sm">
                                            @if($history->warehouseFrom)
                                                <div class="col-md-6">
                                                    <strong>Nga Magazina:</strong><br>
                                                    <span class="badge bg-secondary">{{ $history->warehouseFrom->name }}</span>
                                                </div>
                                            @endif

                                            @if($history->warehouseTo)
                                                <div class="col-md-6">
                                                    <strong>Në Magazinë:</strong><br>
                                                    <span class="badge bg-primary">{{ $history->warehouseTo->name }}</span>
                                                </div>
                                            @endif

                                            @if($history->user)
                                                <div class="col-md-6">
                                                    <strong>Përdoruesi:</strong><br>
                                                    {{ $history->user->name }}
                                                </div>
                                            @endif

                                            @if($history->partner)
                                                <div class="col-md-6">
                                                    <strong>Furnitor/Blerësi:</strong><br>
                                                    {{ $history->partner->name }}
                                                    @if($history->partner->phone)
                                                        <br><small>{{ $history->partner->phone }}</small>
                                                    @endif
                                                </div>
                                            @endif

                                            <div class="col-md-6">
                                                <strong>Sasia:</strong><br>
                                                {{ $history->quantity }}
                                            </div>

                                            @if($history->invoice_number)
                                                <div class="col-md-6">
                                                    <strong>Fatura:</strong><br>
                                                    <span class="badge bg-info">{{ $history->invoice_number }}</span>
                                                </div>
                                            @endif

                                            @if($history->purchase_price)
                                                <div class="col-md-6">
                                                    <strong>Çmimi i Blerjës:</strong><br>
                                                    {{ number_format($history->purchase_price, 2) }} €
                                                </div>
                                            @endif

                                            @if($history->sale_price)
                                                <div class="col-md-6">
                                                    <strong>Çmimi i Shitjës:</strong><br>
                                                    {{ number_format($history->sale_price, 2) }} €
                                                </div>
                                            @endif

                                            @if($history->warranty)
                                                <div class="col-md-6">
                                                    <strong>Garancia:</strong><br>
                                                    {{ $history->warranty }}
                                                </div>
                                            @endif

                                            @if($history->imei)
                                                <div class="col-md-6">
                                                    <strong>IMEI:</strong><br>
                                                    <code>{{ $history->imei }}</code>
                                                </div>
                                            @endif
                                        </div>

                                        @if($history->notes)
                                            <div class="mt-2">
                                                <strong>Shënime:</strong><br>
                                                <small class="text-muted">{{ $history->notes }}</small>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-info" role="alert">
                    <i class="ri-info-line"></i> Nuk ka histori për këtë produkt.
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.timeline-container {
    position: relative;
}

.timeline-item {
    position: relative;
    padding-left: 2rem;
}

.timeline-line {
    position: absolute;
    left: 0.35rem;
    top: 2rem;
    bottom: -2rem;
    width: 2px;
    background: #dee2e6;
}

.timeline-item:last-child .timeline-line {
    display: none;
}

.timeline-dot {
    position: absolute;
    left: -0.5rem;
    top: 0;
    width: 2rem;
    height: 2rem;
    background: #fff;
    border: 2px solid #0d6efd;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    color: #0d6efd;
    z-index: 1;
}

.timeline-content {
    flex: 1;
}

.text-sm {
    font-size: 0.875rem;
}
</style>
