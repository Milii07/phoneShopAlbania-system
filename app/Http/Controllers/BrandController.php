<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::latest()->paginate(10);
        return view('brands.index', compact('brands'));
    }

    public function create()
    {
        return view('brands.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:brands,name',
        ], [
            'name.required' => 'Emri është i detyrueshëm.',
            'name.unique' => 'Ky emër brand ekziston tashmë.',
        ]);

        Brand::create($validated);

        return redirect()->route('brands.index')
            ->with('success', 'Brand u shtua me sukses!');
    }

    public function show(Brand $brand)
    {
        // Return JSON for AJAX requests
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json($brand);
        }

        return view('brands.show', compact('brand'));
    }

    public function edit(Brand $brand)
    {
        return view('brands.edit', compact('brand'));
    }

    public function update(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:brands,name,' . $brand->id,
        ], [
            'name.required' => 'Emri është i detyrueshëm.',
            'name.unique' => 'Ky emër brand ekziston tashmë.',
        ]);

        $brand->update($validated);

        return redirect()->route('brands.index')
            ->with('success', 'Brand u përditësua me sukses!');
    }

    public function destroy(Brand $brand)
    {
        $brand->delete();

        return redirect()->route('brands.index')
            ->with('success', 'Brand u fshi me sukses!');
    }
}
