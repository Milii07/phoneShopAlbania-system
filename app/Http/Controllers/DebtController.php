<?php

namespace App\Http\Controllers;

use App\Models\Debt;
use App\Models\DebtPayment;
use App\Models\Partner;
use App\Models\Warehouse;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DebtController extends Controller
{
    public function index(Request $request)
    {
        $query = Debt::with(['supplier', 'warehouse', 'currency', 'payments']);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('overdue')) {
            $query->whereNotNull('due_date')
                ->where('due_date', '<', now())
                ->whereIn('status', ['pending', 'partial']);
        }

        // Get paginated debts for the listing
        $debts = $query->latest()->paginate(15);

        // Compute totals (total, paid, remaining) grouped by currency for the current filtered set
        $totals = (clone $query)
            ->selectRaw('currency_id, SUM(total_amount) as total_sum, SUM(paid_amount) as paid_sum, SUM(remaining_amount) as remaining_sum')
            ->groupBy('currency_id')
            ->get();

        $totalsByCurrency = [];
        if ($totals->isNotEmpty()) {
            $currencyIds = $totals->pluck('currency_id')->filter()->unique()->values()->all();
            $currencies = Currency::whereIn('id', $currencyIds)->get()->keyBy('id');
            foreach ($totals as $t) {
                $curr = $currencies->get($t->currency_id);
                $code = $curr->code ?? $curr->symbol ?? $t->currency_id;
                $symbol = $curr->symbol ?? $code;
                $totalsByCurrency[$code] = [
                    'symbol' => $symbol,
                    'total' => (float) $t->total_sum,
                    'paid' => (float) $t->paid_sum,
                    'remaining' => (float) $t->remaining_sum,
                ];
            }
        }

        $suppliers = Partner::all();
        $warehouses = Warehouse::all();

        return view('debts.index', compact('debts', 'suppliers', 'warehouses', 'totalsByCurrency'));
    }

    public function create()
    {
        $suppliers = Partner::all();
        $warehouses = Warehouse::all();
        $currencies = Currency::all();

        return view('debts.create', compact('suppliers', 'warehouses', 'currencies'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:partners,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'currency_id' => 'required|exists:currencies,id',
            'total_amount' => 'required|numeric|min:0',
            'debt_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:debt_date',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            $validated['debt_number'] = Debt::generateDebtNumber();
            $validated['remaining_amount'] = $validated['total_amount'];
            $validated['paid_amount'] = 0;
            $validated['status'] = 'pending';

            Debt::create($validated);

            return redirect()->route('debts.index')
                ->with('success', 'Borxhi u shtua me sukses!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gabim: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(Debt $debt)
    {
        $debt->load(['supplier', 'warehouse', 'currency', 'payments']);
        return view('debts.show', compact('debt'));
    }

    public function edit(Debt $debt)
    {
        $suppliers = Partner::all();
        $warehouses = Warehouse::all();
        $currencies = Currency::all();

        return view('debts.edit', compact('debt', 'suppliers', 'warehouses', 'currencies'));
    }

    public function update(Request $request, Debt $debt)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:partners,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'currency_id' => 'required|exists:currencies,id',
            'total_amount' => 'required|numeric|min:0',
            'debt_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:debt_date',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            // Recalculate remaining amount
            $validated['remaining_amount'] = $validated['total_amount'] - $debt->paid_amount;

            $debt->update($validated);
            $debt->updateStatus();

            return redirect()->route('debts.index')
                ->with('success', 'Borxhi u përditësua me sukses!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gabim: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(Debt $debt)
    {
        try {
            $debt->delete();
            return redirect()->route('debts.index')
                ->with('success', 'Borxhi u fshi me sukses!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gabim: ' . $e->getMessage());
        }
    }

    // Add Payment
    public function addPayment(Request $request, Debt $debt)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $debt->remaining_amount,
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:Cash,Bank',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($debt, $validated) {
                // Create payment
                DebtPayment::create([
                    'debt_id' => $debt->id,
                    'amount' => $validated['amount'],
                    'payment_date' => $validated['payment_date'],
                    'payment_method' => $validated['payment_method'],
                    'notes' => $validated['notes'] ?? null,
                ]);

                // Update debt
                $debt->paid_amount += $validated['amount'];
                $debt->remaining_amount -= $validated['amount'];
                $debt->save();
                $debt->updateStatus();
            });

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pagesa u shtua me sukses!',
                    'debt' => $debt->fresh()->load('payments'),
                ]);
            }

            return redirect()->route('debts.show', $debt->id)
                ->with('success', 'Pagesa u shtua me sukses!');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gabim: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Gabim: ' . $e->getMessage());
        }
    }
}
