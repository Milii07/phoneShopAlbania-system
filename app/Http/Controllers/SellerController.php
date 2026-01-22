<?php

namespace App\Http\Controllers;

use App\Models\Seller;
use Illuminate\Http\Request;

class SellerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sellers = Seller::latest()->paginate(10);
        return view('sellers.index', compact('sellers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'age' => 'required|string|max:255',

        ]);

        Seller::create($validated);

        return redirect()->route('sellers.index')
            ->with('success', 'Selleri u shtua me sukses!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $seller = Seller::findOrFail($id);

        if (request()->wantsJson()) {
            return response()->json($seller);
        }

        return view('sellers.show', compact('seller'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $seller = Seller::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'age' => 'required|string|max:255',

        ]);

        $seller->update($validated);

        return redirect()->route('sellers.index')
            ->with('success', 'Selleri u përditësua me sukses!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $seller = Seller::findOrFail($id);
        $seller->delete();

        return redirect()->route('sellers.index')
            ->with('success', 'Selleri u fshi me sukses!');
    }
}
