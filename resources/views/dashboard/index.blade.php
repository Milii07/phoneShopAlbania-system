@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

@role('admin')
<!-- Admin Dashboard Stats -->
<div class="mb-6">
    <h1 class="h3 mb-2 fw-bold text-dark">Dashboard Analytics</h1>
    <p class="text-muted small">Welcome back! Here's your business performance at a glance.</p>
</div>

<div class="row g-2 dashboard-grid">

    <!-- Total Sales Card -->
    <div class="col-lg-3 col-md-6 col-sm-6">
        <a href="{{ route('sales.index') }}" class="stat-box bg-blue-500 text-decoration-none d-block h-100">
            <div class="stat-box-inner">
                <div class="icon-wrapper mb-2">
                    <i class="ri-shopping-bag-3-line"></i>
                </div>
                <div class="stat-value fw-bold">${{ number_format($stats['total_sales'] ?? 0, 2) }}</div>
                <div class="stat-label">Total Sales</div>
            </div>
            <div class="box-overlay"></div>
        </a>
    </div>

    <!-- Total Purchases Card -->
    <div class="col-lg-3 col-md-6 col-sm-6">
        <a href="{{ route('purchases.index') }}" class="stat-box bg-purple-500 text-decoration-none d-block h-100">
            <div class="stat-box-inner">
                <div class="icon-wrapper mb-2">
                    <i class="ri-shopping-cart-2-line"></i>
                </div>
                <div class="stat-value fw-bold">${{ number_format($stats['total_purchases'] ?? 0, 2) }}</div>
                <div class="stat-label">Total Purchases</div>
            </div>
            <div class="box-overlay"></div>
        </a>
    </div>

    <!-- Products Sold Card -->
    <div class="col-lg-3 col-md-6 col-sm-6">
        <a href="{{ route('sales.index') }}" class="stat-box bg-emerald-500 text-decoration-none d-block h-100">
            <div class="stat-box-inner">
                <div class="icon-wrapper mb-2">
                    <i class="ri-barcode-line"></i>
                </div>
                <div class="stat-value fw-bold">{{ number_format($stats['products_sold'] ?? 0) }}</div>
                <div class="stat-label">Products Sold</div>
            </div>
            <div class="box-overlay"></div>
        </a>
    </div>

    <!-- Total Stock Card -->
    <div class="col-lg-3 col-md-6 col-sm-6">
        <a href="{{ route('stock-movements.index') }}" class="stat-box bg-orange-500 text-decoration-none d-block h-100">
            <div class="stat-box-inner">
                <div class="icon-wrapper mb-2">
                    <i class="ri-inbox-line"></i>
                </div>
                <div class="stat-value fw-bold">{{ number_format($stats['total_stock'] ?? 0) }}</div>
                <div class="stat-label">In Stock</div>
            </div>
            <div class="box-overlay"></div>
        </a>
    </div>

    <!-- Pending Sales Card -->
    <div class="col-lg-3 col-md-6 col-sm-6">
        <a href="{{ route('sales.index') }}?payment_status=pending" class="stat-box bg-yellow-500 text-decoration-none d-block h-100">
            <div class="stat-box-inner">
                <div class="icon-wrapper mb-2">
                    <i class="ri-time-line"></i>
                </div>
                <div class="stat-value fw-bold">${{ number_format($stats['pending_sales'] ?? 0, 2) }}</div>
                <div class="stat-label">Pending Sales</div>
            </div>
            <div class="box-overlay"></div>
        </a>
    </div>

    <!-- Pending Purchases Card -->
    <div class="col-lg-3 col-md-6 col-sm-6">
        <a href="{{ route('purchases.index') }}?payment_status=pending" class="stat-box bg-pink-500 text-decoration-none d-block h-100">
            <div class="stat-box-inner">
                <div class="icon-wrapper mb-2">
                    <i class="ri-alert-line"></i>
                </div>
                <div class="stat-value fw-bold">${{ number_format($stats['pending_purchases'] ?? 0, 2) }}</div>
                <div class="stat-label">Pending Purchases</div>
            </div>
            <div class="box-overlay"></div>
        </a>
    </div>

    <!-- Total Profit Card -->
    <div class="col-lg-3 col-md-6 col-sm-6">
        <a href="{{ route('sales.daily-report') }}" class="stat-box bg-green-500 text-decoration-none d-block h-100">
            <div class="stat-box-inner">
                <div class="icon-wrapper mb-2">
                    <i class="ri-money-dollar-circle-line"></i>
                </div>
                <div class="stat-value fw-bold">${{ number_format($stats['total_profit'] ?? 0, 2) }}</div>
                <div class="stat-label">Total Profit</div>
            </div>
            <div class="box-overlay"></div>
        </a>
    </div>

    <!-- Active Products Card -->
    <div class="col-lg-3 col-md-6 col-sm-6">
        <a href="{{ route('products.index') }}" class="stat-box bg-cyan-500 text-decoration-none d-block h-100">
            <div class="stat-box-inner">
                <div class="icon-wrapper mb-2">
                    <i class="ri-database-line"></i>
                </div>
                <div class="stat-value fw-bold">{{ number_format($stats['active_products'] ?? 0) }}</div>
                <div class="stat-label">Active Products</div>
            </div>
            <div class="box-overlay"></div>
        </a>
    </div>

    <!-- Sales This Month Card -->
    <div class="col-lg-3 col-md-6 col-sm-6">
        <a href="{{ route('sales.index') }}" class="stat-box bg-indigo-500 text-decoration-none d-block h-100">
            <div class="stat-box-inner">
                <div class="icon-wrapper mb-2">
                    <i class="ri-calendar-2-line"></i>
                </div>
                <div class="stat-value fw-bold">${{ number_format($stats['sales_this_month'] ?? 0, 2) }}</div>
                <div class="stat-label">Sales This Month</div>
            </div>
            <div class="box-overlay"></div>
        </a>
    </div>

    <!-- Purchases This Month Card -->
    <div class="col-lg-3 col-md-6 col-sm-6">
        <a href="{{ route('purchases.index') }}" class="stat-box bg-red-500 text-decoration-none d-block h-100">
            <div class="stat-box-inner">
                <div class="icon-wrapper mb-2">
                    <i class="ri-calendar-check-line"></i>
                </div>
                <div class="stat-value fw-bold">${{ number_format($stats['purchases_this_month'] ?? 0, 2) }}</div>
                <div class="stat-label">Purchases This Month</div>
            </div>
            <div class="box-overlay"></div>
        </a>
    </div>

    <!-- Total Sales Count Card -->
    <div class="col-lg-3 col-md-6 col-sm-6">
        <a href="{{ route('sales.index') }}" class="stat-box bg-teal-500 text-decoration-none d-block h-100">
            <div class="stat-box-inner">
                <div class="icon-wrapper mb-2">
                    <i class="ri-file-list-line"></i>
                </div>
                <div class="stat-value fw-bold">{{ number_format($stats['sales_count'] ?? 0) }}</div>
                <div class="stat-label">Total Invoices</div>
            </div>
            <div class="box-overlay"></div>
        </a>
    </div>

    <!-- Total Purchases Count Card -->
    <div class="col-lg-3 col-md-6 col-sm-6">
        <a href="{{ route('purchases.index') }}" class="stat-box bg-fuchsia-500 text-decoration-none d-block h-100">
            <div class="stat-box-inner">
                <div class="icon-wrapper mb-2">
                    <i class="ri-receipt-line"></i>
                </div>
                <div class="stat-value fw-bold">{{ number_format($stats['purchases_count'] ?? 0) }}</div>
                <div class="stat-label">Total Purchase Orders</div>
            </div>
            <div class="box-overlay"></div>
        </a>
    </div>

</div>
@else
<!-- Non-Admin Message -->
<div class="d-flex align-items-center justify-content-center" style="min-height: 60vh;">
    <div class="text-center">
        <div class="card border-0 shadow-sm p-5" style="background: #f8f9fa;">
            <div class="mb-3">
                <i class="ri-lock-line" style="font-size: 3rem; color: #0d6efd;"></i>
            </div>
            <h2 class="h4 fw-bold text-dark mb-2">Access Restricted</h2>
            <p class="text-muted">Dashboard statistics are only available for administrators.</p>
        </div>
    </div>
</div>
@endrole

@endsection

@push('styles')
<style>
    .dashboard-grid {
        animation: fadeInGrid 0.6s ease-out;
    }

    @keyframes fadeInGrid {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .stat-box {
        position: relative;
        display: block;
        width: 100%;
        min-height: 140px;
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.15);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        animation: slideUp 0.6s ease-out backwards;
    }

    .stat-box:nth-child(1) { animation-delay: 0.05s; }
    .stat-box:nth-child(2) { animation-delay: 0.1s; }
    .stat-box:nth-child(3) { animation-delay: 0.15s; }
    .stat-box:nth-child(4) { animation-delay: 0.2s; }
    .stat-box:nth-child(5) { animation-delay: 0.25s; }
    .stat-box:nth-child(6) { animation-delay: 0.3s; }
    .stat-box:nth-child(7) { animation-delay: 0.35s; }
    .stat-box:nth-child(8) { animation-delay: 0.4s; }
    .stat-box:nth-child(9) { animation-delay: 0.45s; }
    .stat-box:nth-child(10) { animation-delay: 0.5s; }
    .stat-box:nth-child(11) { animation-delay: 0.55s; }
    .stat-box:nth-child(12) { animation-delay: 0.6s; }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .stat-box-inner {
        position: relative;
        z-index: 2;
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        color: white;
        padding: 12px;
    }

    .icon-wrapper {
        font-size: 28px;
        animation: iconFloat 3s ease-in-out infinite;
    }

    @keyframes iconFloat {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-6px); }
    }

    .stat-value {
        font-size: 16px;
        margin: 4px 0;
        letter-spacing: 0.3px;
        line-height: 1.2;
    }

    .stat-label {
        font-size: 11px;
        font-weight: 500;
        margin: 0;
        letter-spacing: 0.5px;
        opacity: 0.95;
    }

    .box-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: radial-gradient(circle at 30% 30%, rgba(255, 255, 255, 0.25), transparent);
        z-index: 1;
        pointer-events: none;
    }

    .stat-box:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
        border-color: rgba(255, 255, 255, 0.25);
    }

    .stat-box:hover .icon-wrapper {
        animation: iconBounce 0.6s ease-out;
    }

    @keyframes iconBounce {
        0% { transform: scale(1) rotateZ(0deg); }
        50% { transform: scale(1.15) rotateZ(10deg); }
        100% { transform: scale(1) rotateZ(0deg); }
    }

    /* Background Gradients */
    .bg-blue-500 { background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); }
    .bg-purple-500 { background: linear-gradient(135deg, #a855f7 0%, #6b21a8 100%); }
    .bg-emerald-500 { background: linear-gradient(135deg, #10b981 0%, #065f46 100%); }
    .bg-orange-500 { background: linear-gradient(135deg, #f97316 0%, #b45309 100%); }
    .bg-yellow-500 { background: linear-gradient(135deg, #eab308 0%, #854d0e 100%); }
    .bg-pink-500 { background: linear-gradient(135deg, #ec4899 0%, #831843 100%); }
    .bg-green-500 { background: linear-gradient(135deg, #22c55e 0%, #15803d 100%); }
    .bg-cyan-500 { background: linear-gradient(135deg, #06b6d4 0%, #164e63 100%); }
    .bg-indigo-500 { background: linear-gradient(135deg, #6366f1 0%, #312e81 100%); }
    .bg-red-500 { background: linear-gradient(135deg, #ef4444 0%, #7f1d1d 100%); }
    .bg-teal-500 { background: linear-gradient(135deg, #14b8a6 0%, #134e4a 100%); }
    .bg-fuchsia-500 { background: linear-gradient(135deg, #d946ef 0%, #831843 100%); }
</style>
@endpush

@push('scripts')
<script>
    console.log('Dashboard loaded with admin statistics');
</script>
@endpush