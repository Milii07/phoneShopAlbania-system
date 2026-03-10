<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view partners')->only(['index', 'show']);
        $this->middleware('permission:create partners')->only(['create', 'store']);
        $this->middleware('permission:edit partners')->only(['edit', 'update']);
        $this->middleware('permission:delete partners')->only(['destroy']);
    }
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
            $partner = Partner::create($validated);

            // Nëse është AJAX request (nga modali)
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Klienti u shtua me sukses!',
                    'partner' => $partner
                ]);
            }

            // Nëse është normal form submission
            return redirect()->route('partners.index')
                ->with('success', 'Partneri u shtua me sukses!');
        } catch (\Exception $e) {
            // Nëse është AJAX request
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ka ndodhur një gabim: ' . $e->getMessage()
                ], 500);
            }

            // Nëse është normal form submission
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
            $partner = Partner::with([
                'sales' => function ($q) {
                    $q->with('currency')->latest()->take(50);
                }
            ])->findOrFail($id);

            if (request()->wantsJson() || request()->ajax()) {
                $sales = $partner->sales->map(function ($sale) {
                    return [
                        'id'             => $sale->id,
                        'invoice_number' => $sale->invoice_number,
                        'invoice_date'   => $sale->invoice_date->format('d-m-Y'),
                        'total_amount'   => number_format($sale->total_amount, 2),
                        'currency'       => $sale->currency->symbol ?? 'ALL',
                        'sale_status'    => $sale->sale_status,
                        'payment_status' => $sale->payment_status,
                        'show_url'       => route('sales.show', $sale->id),
                    ];
                });

                $allSales = $partner->sales;

                return response()->json([
                    'id'         => $partner->id,
                    'name'       => $partner->name,
                    'phone'      => $partner->phone,
                    'created_at' => $partner->created_at,
                    'updated_at' => $partner->updated_at,
                    'sales'      => $sales,
                    'stats'      => [
                        'total_invoices' => $allSales->count(),
                        'total_spent'    => number_format($allSales->sum('total_amount'), 2),
                        'paid'           => $allSales->where('payment_status', 'Paid')->count(),
                        'unpaid'         => $allSales->where('payment_status', 'Unpaid')->count(),
                        'partial'        => $allSales->where('payment_status', 'Partial')->count(),
                        'total_unpaid_amount' => number_format(
                            $allSales->whereIn('payment_status', ['Unpaid', 'Partial'])->sum('total_amount'),
                            2
                        ),
                    ],
                ]);
            }

            return view('partners.show', compact('partner'));
        } catch (\Exception $e) {
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['error' => 'Partneri nuk u gjet.'], 404);
            }
            return redirect()->route('partners.index')->with('error', 'Partneri nuk u gjet.');
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
