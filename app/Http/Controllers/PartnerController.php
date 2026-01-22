<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $partners = Partner::latest()->paginate(10);
        return view('partners.index', compact('partners'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('partners.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',

        ], [
            'name.required' => 'Emri është i detyrueshëm.',
            'phone.required' => 'Numri i telefonit është i detyrueshëm.',

        ]);

        try {
            Partner::create($validated);

            return redirect()->route('partners.index')
                ->with('success', 'Partneri u shtua me sukses!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Ka ndodhur një gabim: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $partner = Partner::findOrFail($id);

            // Nëse është AJAX request, kthe JSON
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json($partner);
            }

            // Nëse jo, kthe view
            return view('partners.show', compact('partner'));
        } catch (\Exception $e) {
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'error' => 'Partneri nuk u gjet.'
                ], 404);
            }

            return redirect()->route('partners.index')
                ->with('error', 'Partneri nuk u gjet.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $partner = Partner::findOrFail($id);
            return view('partners.edit', compact('partner'));
        } catch (\Exception $e) {
            return redirect()->route('partners.index')
                ->with('error', 'Partneri nuk u gjet.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $partner = Partner::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:255',

            ], [
                'name.required' => 'Emri është i detyrueshëm.',
                'phone.required' => 'Numri i telefonit është i detyrueshëm.',

            ]);

            $partner->update($validated);

            return redirect()->route('partners.index')
                ->with('success', 'Partneri u përditësua me sukses!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Ka ndodhur një gabim: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $partner = Partner::findOrFail($id);
            $partner->delete();

            return redirect()->route('partners.index')
                ->with('success', 'Partneri u fshi me sukses!');
        } catch (\Exception $e) {
            return redirect()->route('partners.index')
                ->with('error', 'Ka ndodhur një gabim gjatë fshirjes: ' . $e->getMessage());
        }
    }

    /**
     * Search partners (bonus feature)
     */
    public function search(Request $request)
    {
        $query = $request->get('query');

        $partners = Partner::where('name', 'like', "%{$query}%")
            ->orWhere('phone', 'like', "%{$query}%")
            ->latest()
            ->paginate(10);

        return view('partners.index', compact('partners'));
    }
}
