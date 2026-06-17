<?php

namespace App\Http\Controllers;

use App\Models\DailyCashRegister;
use App\Models\CashTransaction;
use App\Models\Currency;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CashRegisterController extends Controller
{
    /**
     * Display a listing of cash registers
     */
    public function index(Request $request)
    {
        $query = DailyCashRegister::with(['employee', 'balances.currency'])
            ->orderBy('register_date', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('register_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('register_date', '<=', $request->to_date);
        }

        $registers = $query->paginate(20);

        return view('cash-register.index', compact('registers'));
    }

    /**
     * Show the form for creating a new cash register
     */
    public function create()
    {
        $currencies = Currency::orderBy('code')->get();
        $employees = User::where('role', 'user')->orWhere('role', 'cashier')->orderBy('name')->get();

        return view('cash-register.create', compact('currencies', 'employees'));
    }

    /**
     * Store a newly created cash register in storage
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'register_date' => 'required|date|unique:daily_cash_registers',
            'employee_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
            'initial_balances' => 'nullable|array',
            'initial_balances.*' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $register = DailyCashRegister::create([
                'register_date' => $validated['register_date'],
                'employee_id' => $validated['employee_id'] ?? auth()->id(),
                'notes' => $validated['notes'],
                'status' => 'open',
            ]);

            // Create balances for each currency with opening amounts
            $currencies = Currency::all();
            foreach ($currencies as $currency) {
                $openingAmount = $validated['initial_balances'][$currency->id] ?? 0;

                $register->balances()->create([
                    'currency_id' => $currency->id,
                    'opening_balance' => $openingAmount,
                    'closing_balance' => $openingAmount,
                ]);
            }

            $register->recalculateTotals();

            DB::commit();

            return redirect()->route('cash-register.show', $register)
                ->with('success', 'Arka u krijua me sukses');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating cash register: ' . $e->getMessage());

            return back()->withInput()->with('error', 'Gabim gjatë krijimit të arkës');
        }
    }

    /**
     * Display the specified cash register
     */
    public function show($id)
    {
        $register = DailyCashRegister::findOrFail($id);
        $register->ensureBalances();

        $register->load([
            'employee',
            'balances.currency',
            'transactions' => function ($q) {
                $q->with(['currency', 'createdBy'])->orderBy('created_at', 'desc');
            },
        ]);

        $currencies = Currency::orderBy('code')->get();

        return view('cash-register.show', compact('register', 'currencies'));
    }

    /**
     * Show the form for editing the specified cash register
     */
    public function edit($id)
    {
        $register = DailyCashRegister::with(['employee', 'balances'])->findOrFail($id);
        $currencies = Currency::orderBy('code')->get();
        $employees = User::orderBy('name')->get();

        return view('cash-register.edit', compact('register', 'currencies', 'employees'));
    }

    /**
     * Update the specified cash register in storage
     */
    public function update(Request $request, $id)
    {
        $register = DailyCashRegister::findOrFail($id);

        $validated = $request->validate([
            'employee_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $register->update([
            'employee_id' => $validated['employee_id'],
            'notes' => $validated['notes'],
        ]);

        return redirect()->route('cash-register.show', $register)
            ->with('success', 'Arka u përditësua me sukses');
    }

    /**
     * Add income (payment received) to cash register
     */
    public function addIncome(Request $request, $id)
    {
        $register = DailyCashRegister::findOrFail($id);

        $validated = $request->validate([
            'currency_id'  => 'required|exists:currencies,id',
            'amount'       => 'required|numeric|min:0.01',
            'description'  => 'required|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $currency = Currency::findOrFail($validated['currency_id']);
            $register->addTransaction(
                $currency,
                'income',
                $validated['amount'],
                $validated['description'],
                'manual',
                null,
                auth()->id()
            );

            DB::commit();

            return back()->with('success', 'Pagesa u shtua me sukses');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error adding income: ' . $e->getMessage());

            return back()->with('error', 'Gabim gjatë shtimit të pagesës');
        }
    }

    /**
     * Add expense (bill paid) to cash register
     */
    public function addExpense(Request $request, $id)
    {
        $register = DailyCashRegister::findOrFail($id);

        $validated = $request->validate([
            'currency_id'  => 'required|exists:currencies,id',
            'amount'       => 'required|numeric|min:0.01',
            'description'  => 'required|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $currency = Currency::findOrFail($validated['currency_id']);
            $register->addTransaction(
                $currency,
                'expense',
                $validated['amount'],
                $validated['description'],
                'manual',
                null,
                auth()->id()
            );

            DB::commit();

            return back()->with('success', 'Shpenzimi u regjistrua me sukses');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error adding expense: ' . $e->getMessage());

            return back()->with('error', 'Gabim gjatë regjistrimit të shpenzimit');
        }
    }

    /**
     * Add manual adjustment to cash register
     */
    public function addAdjustment(Request $request, $id)
    {
        $register = DailyCashRegister::findOrFail($id);

        $validated = $request->validate([
            'currency_id' => 'required|exists:currencies,id',
            'amount' => 'required|numeric|not_in:0',
            'reason' => 'required|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $currency = Currency::findOrFail($validated['currency_id']);
            $type = $validated['amount'] > 0 ? 'adjustment' : 'expense';

            $register->addTransaction(
                $currency,
                $type,
                abs($validated['amount']),
                $validated['reason'],
                'adjustment',
                null,
                auth()->id()
            );

            DB::commit();

            return back()->with('success', 'Rregullim i shtuar me sukses');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error adding adjustment: ' . $e->getMessage());

            return back()->with('error', 'Gabim gjatë shtimit të rregullimit');
        }
    }

    /**
     * Close the cash register
     */
    public function close(Request $request, $id)
    {
        $register = DailyCashRegister::findOrFail($id);

        if ($register->status !== 'open') {
            return back()->with('error', 'Kjo arka nuk mund të mbyllet në këtë gjendje');
        }

        $validated = $request->validate([
            'declared_amounts' => 'required|array',
            'declared_amounts.*' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Check declared amounts vs calculated
            $differences = [];
            foreach ($register->balances as $balance) {
                $declared = $validated['declared_amounts'][$balance->currency_id] ?? 0;
                $calculated = $balance->closing_balance;
                $diff = $declared - $calculated;

                if (abs($diff) > 0.01) {
                    $differences[$balance->currency->code] = [
                        'declared' => $declared,
                        'calculated' => $calculated,
                        'difference' => $diff,
                    ];
                }
            }

            // If there are differences, create adjustment transactions
            foreach ($differences as $code => $data) {
                $currency = Currency::where('code', $code)->first();
                if ($currency && abs($data['difference']) > 0.01) {
                    $register->addTransaction(
                        $currency,
                        'adjustment',
                        abs($data['difference']),
                        'Përshtatje gjatë mbylljes - ' . ($data['difference'] > 0 ? 'shtim' : 'zbritje'),
                        'closing',
                        null,
                        auth()->id()
                    );
                }
            }

            $register->close();

            DB::commit();

            return redirect()->route('cash-register.show', $register)
                ->with('success', 'Arka u mbyll me sukses');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error closing cash register: ' . $e->getMessage());

            return back()->with('error', 'Gabim gjatë mbylljes së arkës');
        }
    }

    /**
     * Balance the cash register
     */
    public function balance($id)
    {
        $register = DailyCashRegister::findOrFail($id);

        if ($register->status === 'balanced') {
            return back()->with('error', 'Kjo arka është tashmë e balancuar');
        }

        $register->balance();

        return redirect()->route('cash-register.show', $register)
            ->with('success', 'Arka u balancua me sukses');
    }

    /**
     * Get today's cash register (or create it), then show it
     */
    public function today()
    {
        $register = DailyCashRegister::todayOrCreate();
        return redirect()->route('cash-register.show', $register);
    }

    /**
     * Add money to multiple currencies at once
     */
    public function bulkAdd(Request $request, $id)
    {
        $register = DailyCashRegister::findOrFail($id);

        $validated = $request->validate([
            'description' => 'required|string|max:500',
            'amounts'     => 'required|array',
            'amounts.*'   => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $added = false;
            foreach ($validated['amounts'] as $currencyId => $amount) {
                if ($amount > 0) {
                    $currency = Currency::findOrFail($currencyId);
                    $register->addTransaction($currency, 'income', $amount, $validated['description'], 'manual', null, auth()->id());
                    $added = true;
                }
            }

            if (!$added) {
                return back()->with('error', 'Vendos të paktën një shumë pozitive');
            }

            DB::commit();
            return back()->with('success', 'Paratë u shtuan me sukses në arkë');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in bulkAdd: ' . $e->getMessage());
            return back()->with('error', 'Gabim gjatë shtimit të parave');
        }
    }

    /**
     * Remove money from multiple currencies at once
     */
    public function bulkRemove(Request $request, $id)
    {
        $register = DailyCashRegister::findOrFail($id);

        $validated = $request->validate([
            'description' => 'required|string|max:500',
            'amounts'     => 'required|array',
            'amounts.*'   => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $removed = false;
            foreach ($validated['amounts'] as $currencyId => $amount) {
                if ($amount > 0) {
                    $currency = Currency::findOrFail($currencyId);
                    $register->addTransaction($currency, 'expense', $amount, $validated['description'], 'manual', null, auth()->id());
                    $removed = true;
                }
            }

            if (!$removed) {
                return back()->with('error', 'Vendos të paktën një shumë pozitive');
            }

            DB::commit();
            return back()->with('success', 'Paratë u hoqën me sukses nga arka');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in bulkRemove: ' . $e->getMessage());
            return back()->with('error', 'Gabim gjatë heqjes së parave');
        }
    }

    /**
     * Export cash register data to PDF
     */
    public function exportPdf($id)
    {
        $register = DailyCashRegister::with(['employee', 'balances.currency', 'transactions'])
            ->findOrFail($id);

        $pdf = \PDF::loadView('cash-register.exports.pdf', compact('register'));

        return $pdf->download('arka_' . $register->register_date->format('Y-m-d') . '.pdf');
    }
}
