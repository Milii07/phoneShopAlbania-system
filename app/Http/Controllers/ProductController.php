<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Currency;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['warehouses', 'category', 'brand', 'currency'])
            ->latest();

        // Filter by warehouse if provided
        if ($request->has('warehouse_id') && $request->warehouse_id) {
            $query->whereHas('warehouses', function ($q) use ($request) {
                $q->where('warehouse_id', $request->warehouse_id);
            });
        }

        $products = $query->paginate(10);

        $warehouses = Warehouse::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $currencies = Currency::orderBy('code')->get();

        return view('products.index', compact('products', 'warehouses', 'categories', 'brands', 'currencies'));
    }

    public function create()
    {
        $warehouses = Warehouse::all();
        $categories = Category::all();
        $brands = Brand::all();
        $currencies = Currency::all();

        if (request()->expectsJson()) {
            return response()->json([
                'warehouses' => $warehouses,
                'categories' => $categories,
                'brands' => $brands,
                'currencies' => $currencies,
            ]);
        }

        return view('products.create', compact('warehouses', 'categories', 'brands', 'currencies'));
    }

    public function store(Request $request)
    {
        $rules = [
            'warehouse_ids' => 'required|array|min:1',
            'warehouse_ids.*' => 'exists:warehouses,id',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'currency_id' => 'required|exists:currencies,id',
        ];

        $category = Category::find($request->category_id);
        if ($category && strtolower($category->name) === 'telefona') {
            $rules['storage'] = 'required|string|max:50';
            $rules['ram'] = 'required|string|max:50';
            $rules['color'] = 'required|string|max:50';
        }

        $validated = $request->validate($rules, [
            'warehouse_ids.required' => 'Duhet të zgjedhësh të paktën një warehouse.',
            'warehouse_ids.min' => 'Duhet të zgjedhësh të paktën një warehouse.',
            'category_id.required' => 'Kategoria është e detyrueshme.',
            'brand_id.required' => 'Brand është i detyrueshëm.',
            'name.required' => 'Emri është i detyrueshëm.',
            'price.required' => 'Çmimi është i detyrueshëm.',
            'currency_id.required' => 'Currency është i detyrueshëm.',
            'storage.required' => 'Storage është i detyrueshëm për telefonat.',
            'ram.required' => 'RAM është i detyrueshëm për telefonat.',
            'color.required' => 'Ngjyra është e detyrueshme për telefonat.',
        ]);

        // Create product
        $product = Product::create([
            'category_id' => $validated['category_id'],
            'brand_id' => $validated['brand_id'],
            'name' => $validated['name'],
            'price' => $validated['price'],
            'currency_id' => $validated['currency_id'],
            'storage' => $validated['storage'] ?? null,
            'ram' => $validated['ram'] ?? null,
            'color' => $validated['color'] ?? null,
        ]);

        // Attach warehouses (quantity = 0 per default)
        $warehouseData = [];
        foreach ($validated['warehouse_ids'] as $warehouseId) {
            $warehouseData[$warehouseId] = ['quantity' => 0];
        }
        $product->warehouses()->attach($warehouseData);

        return redirect()->route('products.index')
            ->with('success', 'Produkti u shtua me sukses!');
    }

    public function show(Product $product)
    {
        $product->load(['warehouses', 'category', 'brand', 'currency']);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json($product);
        }

        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $rules = [
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'name' => 'required|string|max:255',
            'unit_price' => 'required|numeric|min:0',
            'currency_id' => 'required|exists:currencies,id',
            'warehouse_ids' => 'required|array|min:1',
            'warehouse_ids.*' => 'exists:warehouses,id',
        ];

        $category = Category::findOrFail($request->category_id);
        if ($category && strtolower($category->name) === 'telefona') {
            $rules['storage'] = 'required|string|max:50';
            $rules['ram'] = 'required|string|max:50';
            $rules['color'] = 'required|string|max:50';
        }

        $validated = $request->validate($rules, [
            'category_id.required' => 'Kategoria është e detyrueshme.',
            'brand_id.required' => 'Brand është i detyrueshëm.',
            'name.required' => 'Emri është i detyrueshëm.',
            'unit_price.required' => 'Çmimi është i detyrueshëm.',
            'currency_id.required' => 'Currency është i detyrueshëm.',
            'warehouse_ids.required' => 'Duhet të zgjedhësh të paktën një warehouse.',
            'storage.required' => 'Storage është i detyrueshëm për telefonat.',
            'ram.required' => 'RAM është i detyrueshëm për telefonat.',
            'color.required' => 'Ngjyra është e detyrueshme për telefonat.',
        ]);

        // Update basic product info
        $product->update([
            'category_id' => $validated['category_id'],
            'brand_id' => $validated['brand_id'],
            'name' => $validated['name'],
            'price' => $validated['unit_price'],
            'currency_id' => $validated['currency_id'],
            'storage' => $validated['storage'] ?? null,
            'ram' => $validated['ram'] ?? null,
            'color' => $validated['color'] ?? null,
        ]);

        // Sync warehouses (quantity = 0, kjo ruhet në purchase/sale)
        // $warehouseData = [];
        // foreach ($validated['warehouse_ids'] as $warehouseId) {
        //     $warehouseData[$warehouseId] = ['quantity' => 0];
        // }
        $product->warehouses()->sync($validated['warehouse_ids']);

        return redirect()->route('products.index')
            ->with('success', 'Produkti u përditësua me sukses!');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Produkti u fshi me sukses!');
    }
}
