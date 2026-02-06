<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::withCount('products')
            ->latest()
            ->paginate(10);

        // Add total items count for each warehouse
        $warehouses->each(function ($warehouse) {
            $warehouse->total_items = $warehouse->total_items;
        });

        return view('warehouses.index', compact('warehouses'));
    }

    public function create()
    {
        return view('warehouses.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:warehouses,name',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'profit_percentage' => 'nullable|numeric|min:0|max:100',
        ], [
            'name.required' => 'Emri është i detyrueshëm.',
            'name.unique' => 'Një warehouse me këtë emër ekziston tashmë.',
            'profit_percentage.max' => 'Përqindja nuk mund të jetë më shumë se 100%.',
        ]);

        Warehouse::create($validated);

        return redirect()->route('warehouses.index')
            ->with('success', 'Warehouse u shtua me sukses!');
    }

    public function show(Warehouse $warehouse)
    {
        $warehouse->load(['products' => function ($query) {
            $query->wherePivot('quantity', '>', 0);
        }]);

        // Return JSON for AJAX requests
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json($warehouse);
        }

        // Calculate statistics
        $stats = [
            'total_products' => $warehouse->products()->count(),
            'total_items' => $warehouse->total_items,
            'available_products' => $warehouse->available_products->count(),
            'low_stock_products' => $warehouse->getLowStockProducts(5)->count(),
            'out_of_stock' => $warehouse->out_of_stock_products->count(),
        ];

        return view('warehouses.show', compact('warehouse', 'stats'));
    }

    public function edit(Warehouse $warehouse)
    {
        return view('warehouses.edit', compact('warehouse'));
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:warehouses,name,' . $warehouse->id,
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'profit_percentage' => 'nullable|numeric|min:0|max:100',
        ], [
            'name.required' => 'Emri është i detyrueshëm.',
            'name.unique' => 'Një warehouse me këtë emër ekziston tashmë.',
            'profit_percentage.max' => 'Përqindja nuk mund të jetë më shumë se 100%.',
        ]);

        $warehouse->update($validated);

        return redirect()->route('warehouses.index')
            ->with('success', 'Warehouse u përditësua me sukses!');
    }

    public function destroy(Warehouse $warehouse)
    {
        // Check if warehouse has products with stock
        $hasStock = $warehouse->products()
            ->wherePivot('quantity', '>', 0)
            ->exists();

        if ($hasStock) {
            return redirect()->route('warehouses.index')
                ->with('error', 'Nuk mund të fshihet warehouse që ka produkte në stok!');
        }

        $warehouse->delete();

        return redirect()->route('warehouses.index')
            ->with('success', 'Warehouse u fshi me sukses!');
    }

    /**
     * Get products for a specific warehouse (API)
     */
    public function getProducts(Warehouse $warehouse)
    {
        $products = $warehouse->products()
            ->with(['category', 'brand', 'currency'])
            ->wherePivot('quantity', '>', 0)
            ->get()
            ->map(function ($product) {
                $product->stock_quantity = $product->pivot->quantity;
                return $product;
            });

        return response()->json($products);
    }

    /**
     * Get warehouse statistics (API)
     */
    public function getStats(Warehouse $warehouse)
    {
        $stats = [
            'total_product_types' => $warehouse->total_product_types,
            'total_items' => $warehouse->total_items,
            'available_products' => $warehouse->available_products->count(),
            'low_stock_count' => $warehouse->getLowStockProducts(5)->count(),
            'out_of_stock_count' => $warehouse->out_of_stock_products->count(),
        ];

        return response()->json($stats);
    }
}
