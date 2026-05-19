<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\SaleItem;
use App\Models\PurchaseItem;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [];

        // Only show stats for admin users
        if (Auth::user() && Auth::user()->hasRole('admin')) {
            // Total Sales Revenue
            $stats['total_sales'] = Sale::where('payment_status', 'completed')
                ->sum('total_amount') ?? 0;

            // Total Purchases Cost
            $stats['total_purchases'] = Purchase::where('payment_status', 'completed')
                ->sum('total_amount') ?? 0;

            // Total Products Sold
            $stats['products_sold'] = SaleItem::count() ?? 0;

            // Total Products in Stock
            $stats['total_stock'] = DB::table('product_warehouse')
                ->sum('quantity') ?? 0;

            // Pending Sales (unpaid)
            $stats['pending_sales'] = Sale::where('payment_status', 'pending')
                ->sum('total_amount') ?? 0;

            // Pending Purchases (unpaid)
            $stats['pending_purchases'] = Purchase::where('payment_status', 'pending')
                ->sum('total_amount') ?? 0;

            // Total Profit
            $stats['total_profit'] = Sale::where('payment_status', 'completed')
                ->sum('profit_total') ?? 0;

            // Active Products
            $stats['active_products'] = Product::count() ?? 0;

            // Sales This Month
            $stats['sales_this_month'] = Sale::whereMonth('invoice_date', now()->month)
                ->whereYear('invoice_date', now()->year)
                ->where('payment_status', 'completed')
                ->sum('total_amount') ?? 0;

            // Purchases This Month
            $stats['purchases_this_month'] = Purchase::whereMonth('purchase_date', now()->month)
                ->whereYear('purchase_date', now()->year)
                ->where('payment_status', 'completed')
                ->sum('total_amount') ?? 0;

            // Total Sales Count
            $stats['sales_count'] = Sale::count() ?? 0;

            // Total Purchases Count
            $stats['purchases_count'] = Purchase::count() ?? 0;
        }

        return view('dashboard.index', compact('stats'));
    }
}
