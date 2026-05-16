<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductHistory;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class ProductHistoryController extends Controller
{
    /**
     * Display the product history search page
     */
    public function index()
    {
        $warehouses = Warehouse::all();
        
        return view('product-histories.index', compact('warehouses'));
    }

    /**
     * Search products with history
     */
    public function search(Request $request)
    {
        $searchTerm = $request->input('q', '');
        $searchType = $request->input('type', 'name');

        $query = Product::query();

        if ($searchTerm) {
            match ($searchType) {
                'name' => $query->where('name', 'like', "%{$searchTerm}%"),
                'sku' => $query->where('sku', 'like', "%{$searchTerm}%"),
                'barcode' => $query->where('barcode', 'like', "%{$searchTerm}%"),
                'imei' => $query->whereHas('purchaseItems', function ($q) use ($searchTerm) {
                    $q->where('imei_numbers', 'like', "%{$searchTerm}%");
                }),
                default => $query->where('name', 'like', "%{$searchTerm}%"),
            };
        }

        $products = $query->with('category', 'brand')
            ->limit(20)
            ->get();

        if ($request->wantsJson()) {
            return response()->json($products);
        }

        return view('product-histories.search-results', compact('products'));
    }

    /**
     * Display complete history timeline for a product
     */
    public function show(Product $product)
    {
        $histories = ProductHistory::forProduct($product->id)
            ->with('user', 'partner', 'warehouseFrom', 'warehouseTo')
            ->latest()
            ->paginate(50);

        $timeline = ProductHistory::forProduct($product->id)
            ->with('user', 'partner', 'warehouseFrom', 'warehouseTo')
            ->latest()
            ->get();

        $stats = [
            'total_movements' => ProductHistory::forProduct($product->id)->count(),
            'current_status' => $product->status ?? 'active',
            'last_movement' => ProductHistory::forProduct($product->id)->latest()->first(),
        ];

        return view('product-histories.show', compact('product', 'histories', 'timeline', 'stats'));
    }

    /**
     * Get product history via API (for DataTables/AJAX)
     */
    public function getHistoryData(Request $request, Product $product)
    {
        $histories = ProductHistory::forProduct($product->id)
            ->with('user', 'partner', 'warehouseFrom', 'warehouseTo')
            ->when($request->input('action_type'), function ($q) use ($request) {
                return $q->where('action_type', $request->input('action_type'));
            })
            ->when($request->input('start_date'), function ($q) use ($request) {
                return $q->whereDate('created_at', '>=', $request->input('start_date'));
            })
            ->when($request->input('end_date'), function ($q) use ($request) {
                return $q->whereDate('created_at', '<=', $request->input('end_date'));
            })
            ->latest()
            ->get();

        return response()->json([
            'draw' => $request->input('draw', 0),
            'recordsTotal' => ProductHistory::forProduct($product->id)->count(),
            'recordsFiltered' => $histories->count(),
            'data' => $histories->map(function ($history) {
                return [
                    'id' => $history->id,
                    'date' => $history->created_at->format('d/m/Y H:i'),
                    'action_type' => $history->action_type_name,
                    'action_icon' => $history->action_icon,
                    'action_color' => $history->action_badge_color,
                    'warehouse_from' => $history->warehouseFrom?->name ?? '-',
                    'warehouse_to' => $history->warehouseTo?->name ?? '-',
                    'user' => $history->user?->name ?? '-',
                    'partner' => $history->partner?->name ?? '-',
                    'quantity' => $history->quantity,
                    'purchase_price' => $history->purchase_price ? number_format($history->purchase_price, 2) : '-',
                    'sale_price' => $history->sale_price ? number_format($history->sale_price, 2) : '-',
                    'invoice' => $history->invoice_number ?? '-',
                    'status' => $history->status_name,
                    'status_color' => $history->status_badge_color,
                    'warranty' => $history->warranty ?? '-',
                    'notes' => $history->notes ?? '-',
                    'description' => $history->movement_description,
                ];
            }),
        ]);
    }

    /**
     * Show modal view for product history
     */
    public function modal(Product $product)
    {
        $histories = ProductHistory::forProduct($product->id)
            ->with('user', 'partner', 'warehouseFrom', 'warehouseTo')
            ->latest()
            ->get();

        return view('product-histories.modal', compact('product', 'histories'));
    }

    /**
     * Filter history with advanced filters
     */
    public function filter(Request $request)
    {
        $query = ProductHistory::query();

        // Filter by product
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->input('product_id'));
        }

        // Filter by action type
        if ($request->filled('action_type')) {
            $query->where('action_type', $request->input('action_type'));
        }

        // Filter by warehouse
        if ($request->filled('warehouse_id')) {
            $warehouseId = $request->input('warehouse_id');
            $query->where(function ($q) use ($warehouseId) {
                $q->where('warehouse_to_id', $warehouseId)
                  ->orWhere('warehouse_from_id', $warehouseId);
            });
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->input('end_date'));
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $histories = $query->with('product', 'user', 'partner', 'warehouseFrom', 'warehouseTo')
            ->latest()
            ->paginate(50);

        return view('product-histories.filtered', compact('histories'));
    }

    /**
     * Export product history to CSV
     */
    public function export(Request $request)
    {
        $query = ProductHistory::query();

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->input('product_id'));
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->input('end_date'));
        }

        $histories = $query->with('product', 'user', 'partner', 'warehouseFrom', 'warehouseTo')
            ->latest()
            ->get();

        $filename = 'product_history_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($histories) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM for UTF-8

            // Header
            fputcsv($file, [
                'ID',
                'Produkti',
                'Lloji i Lëvizjes',
                'Nga Magazina',
                'Në Magazinë',
                'Përdoruesi',
                'Furnitor/Blerësi',
                'Sasia',
                'Çmimi Blerjeje',
                'Çmimi Shitjeje',
                'Fatura',
                'Statusi',
                'Garancia',
                'Data',
                'Shënim',
            ]);

            foreach ($histories as $history) {
                fputcsv($file, [
                    $history->id,
                    $history->product->name ?? '-',
                    $history->action_type_name,
                    $history->warehouseFrom?->name ?? '-',
                    $history->warehouseTo?->name ?? '-',
                    $history->user?->name ?? '-',
                    $history->partner?->name ?? '-',
                    $history->quantity,
                    $history->purchase_price ?? '-',
                    $history->sale_price ?? '-',
                    $history->invoice_number ?? '-',
                    $history->status_name,
                    $history->warranty ?? '-',
                    $history->created_at->format('d/m/Y H:i'),
                    $history->notes ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get statistics for dashboard
     */
    public function getStats(Request $request)
    {
        $startDate = $request->input('start_date', now()->subMonth());
        $endDate = $request->input('end_date', now());

        $stats = [
            'total_movements' => ProductHistory::dateRange($startDate, $endDate)->count(),
            'stock_entries' => ProductHistory::byActionType('stock_entry')->dateRange($startDate, $endDate)->count(),
            'sales' => ProductHistory::byActionType('sale')->dateRange($startDate, $endDate)->count(),
            'transfers' => ProductHistory::byActionType('store_transfer')->dateRange($startDate, $endDate)->count(),
            'returns' => ProductHistory::byActionType('product_return')->dateRange($startDate, $endDate)->count(),
            'repairs' => ProductHistory::byActionType('repair_service')->dateRange($startDate, $endDate)->count(),
        ];

        return response()->json($stats);
    }
}
