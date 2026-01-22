<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function index()
    {
        $currencies = Currency::orderBy('created_at', 'desc')->paginate(10);
        return view('currencies.index', compact('currencies'));
    }

    public function create()
    {
        return view('currencies.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:3|unique:currencies,code',
            'symbol' => 'required|string|max:5',
            'exchange_rate' => 'required|numeric|min:0',
        ], [
            'code.required' => 'Kodi është i detyrueshëm.',
            'code.unique' => 'Ky kod ekziston tashmë.',
            'symbol.required' => 'Simboli është i detyrueshëm.',
            'exchange_rate.required' => 'Exchange rate është i detyrueshëm.',
        ]);

        Currency::create($validated);

        return redirect()->route('currencies.index')
            ->with('success', 'Currency u shtua me sukses!');
    }

    public function show(Currency $currency)
    {
        // Return JSON for AJAX requests
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json($currency);
        }

        return view('currencies.show', compact('currency'));
    }

    public function edit(Currency $currency)
    {
        return view('currencies.edit', compact('currency'));
    }

    public function update(Request $request, Currency $currency)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:3|unique:currencies,code,' . $currency->id,
            'symbol' => 'required|string|max:5',
            'exchange_rate' => 'required|numeric|min:0',
        ], [
            'code.required' => 'Kodi është i detyrueshëm.',
            'code.unique' => 'Ky kod ekziston tashmë.',
            'symbol.required' => 'Simboli është i detyrueshëm.',
            'exchange_rate.required' => 'Exchange rate është i detyrueshëm.',
        ]);

        $currency->update($validated);

        return redirect()->route('currencies.index')
            ->with('success', 'Currency u përditësua me sukses!');
    }

    public function destroy(Currency $currency)
    {
        $currency->delete();

        return redirect()->route('currencies.index')
            ->with('success', 'Currency u fshi me sukses!');
    }
}
