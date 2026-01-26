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
    public function index()
    {
        $products = Product::with(['warehouse', 'category', 'brand', 'currency'])
            ->latest()
            ->paginate(10);

        $warehouses = Warehouse::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();
        $currencies = Currency::orderBy('code')->get();

        return view('products.index', compact('products', 'warehouses', 'categories', 'brands', 'currencies'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $rules = [
            'warehouse_id' => 'required|exists:warehouses,id',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'currency_id' => 'required|exists:currencies,id',
        ];

        $category = Category::find($request->category_id);
        if ($category && strtolower($category->name) === 'telefona') {
            $rules['storage'] = 'required|string|max:50';
            $rules['ram'] = 'required|string|max:50';
            $rules['color'] = 'required|string|max:50';
            $rules['imei'] = 'required|string|max:50|unique:products,imei';
            $rules['magazina'] = 'required|string|max:50';
        }

        $validated = $request->validate($rules, [
            'warehouse_id.required' => 'Warehouse është i detyrueshëm.',
            'category_id.required' => 'Kategoria është e detyrueshme.',
            'brand_id.required' => 'Brand është i detyrueshëm.',
            'name.required' => 'Emri është i detyrueshëm.',
            'quantity.required' => 'Sasia është e detyrueshme.',
            'price.required' => 'Çmimi është i detyrueshëm.',
            'currency_id.required' => 'Currency është i detyrueshëm.',
            'storage.required' => 'Storage është i detyrueshëm për telefonat.',
            'ram.required' => 'RAM është i detyrueshëm për telefonat.',
            'color.required' => 'Ngjyra është e detyrueshme për telefonat.',

        ]);

        Product::create($validated);

        return redirect()->route('products.index')
            ->with('success', 'Produkti u shtua me sukses!');
    }

    public function show(Product $product)
    {
        if (request()->wantsJson() || request()->ajax()) {
            $product->load(['warehouse', 'category', 'brand']);
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
            'warehouse_id' => 'required|exists:warehouses,id',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'currency_id' => 'required|exists:currencies,id',
        ];

        $category = Category::findOrFail($request->category_id);
        if ($category && strtolower($category->name) === 'telefona') {
            $rules['storage'] = 'required|string|max:50';
            $rules['ram'] = 'required|string|max:50';
            $rules['color'] = 'required|string|max:50';
        }

        $validated = $request->validate($rules, [
            'warehouse_id.required' => 'Warehouse është i detyrueshëm.',
            'category_id.required' => 'Kategoria është e detyrueshme.',
            'brand_id.required' => 'Brand është i detyrueshëm.',
            'name.required' => 'Emri është i detyrueshëm.',
            'quantity.required' => 'Sasia është e detyrueshme.',
            'price.required' => 'Çmimi është i detyrueshëm.',
            'currency_id.required' => 'Currency është i detyrueshëm.',
            'storage.required' => 'Storage është i detyrueshëm për telefonat.',
            'ram.required' => 'RAM është i detyrueshëm për telefonat.',
            'color.required' => 'Ngjyra është e detyrueshme për telefonat.',
        ]);

        $product->update($validated);

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
