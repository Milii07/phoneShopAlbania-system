<?php

namespace App\Http\Controllers;

use App\Models\OnlineOrder;
use App\Models\Sale;
use App\Models\Partner;
use App\Models\Warehouse;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OnlineOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = OnlineOrder::with(['sale', 'partner', 'warehouse', 'currency']);

        // Filters
        if ($request->filled('status')) {
            if ($request->status == 'paid') {
                $query->where('is_paid', true);
            } elseif ($request->status == 'unpaid') {
                $query->where('is_paid', false);
            } elseif ($request->status == 'overdue') {
                $query->where('is_paid', false)
                    ->whereNotNull('expected_payment_date')
                    ->where('expected_payment_date', '<', now());
            }
        }

        if ($request->filled('partner_id')) {
            $query->where('partner_id', $request->partner_id);
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('search')) {
            $query->where('order_number', 'like', '%' . $request->search . '%');
        }

        $orders = $query->latest()->paginate(15);
        $partners = Partner::all();
        $warehouses = Warehouse::all();

        // Compute unpaid totals grouped by currency (across all unpaid orders)
        $totalsByCurrencyId = OnlineOrder::where('is_paid', false)
            ->groupBy('currency_id')
            ->selectRaw('currency_id, SUM(order_amount) as total')
            ->pluck('total', 'currency_id')
            ->toArray();

        $unpaidTotals = [];
        if (!empty($totalsByCurrencyId)) {
            $currencyIds = array_keys($totalsByCurrencyId);
            $currencies = Currency::whereIn('id', $currencyIds)->get()->keyBy('id');
            foreach ($totalsByCurrencyId as $cid => $total) {
                $curr = $currencies->get($cid);
                $code = $curr->code ?? $curr->symbol ?? $cid;
                $symbol = $curr->symbol ?? $code;
                $unpaidTotals[$code] = ['symbol' => $symbol, 'total' => (float) $total];
            }
        }

        return view('online-orders.index', compact('orders', 'partners', 'warehouses', 'unpaidTotals'));
    }

    public function create()
    {
        // Get only online sales that don't have an order yet
        $sales = Sale::where('purchase_location', 'online')
            ->whereDoesntHave('onlineOrder')
            ->with(['partner', 'warehouse', 'currency'])
            ->latest()
            ->get();

        $partners = Partner::all();
        $warehouses = Warehouse::all();
        $currencies = Currency::all();

        return view('online-orders.create', compact('sales', 'partners', 'warehouses', 'currencies'));
    }

    public function store(Request $request)
    {

        // Support creating a single order or multiple orders at once (sale_ids[])
        $isMultiple = $request->filled('sale_ids');

        if ($isMultiple) {
            $request->validate([
                'sale_ids' => 'required|array|min:1',
                'sale_ids.*' => 'required|exists:sales,id',
                'order_date' => 'required|date',
                'expected_payment_date' => 'nullable|date|after_or_equal:order_date',
                'delivery_address' => 'nullable|string',
                'notes' => 'nullable|string',
            ]);

            $saleIds = $request->input('sale_ids');
            $created = 0;
            $skipped = [];

            DB::beginTransaction();
            try {
                foreach ($saleIds as $sid) {
                    // skip if an order already exists for this sale
                    if (OnlineOrder::where('sale_id', $sid)->exists()) {
                        $skipped[] = $sid;
                        continue;
                    }

                    $sale = Sale::with(['partner', 'warehouse', 'currency'])->find($sid);
                    if (!$sale) {
                        $skipped[] = $sid;
                        continue;
                    }

                    $data = [
                        'sale_id' => $sid,
                        'order_number' => OnlineOrder::generateOrderNumber(),
                        'partner_id' => $sale->partner_id,
                        'warehouse_id' => $sale->warehouse_id,
                        'currency_id' => $sale->currency_id,
                        'order_amount' => $sale->total_amount,
                        'order_date' => $request->order_date,
                        'expected_payment_date' => $request->expected_payment_date,
                        'is_paid' => false,
                        'delivery_address' => $request->delivery_address,
                        'notes' => $request->notes,
                    ];

                    OnlineOrder::create($data);
                    $created++;
                }

                DB::commit();

                $message = "U krijuan: {$created} porosi.";
                if (!empty($skipped)) {
                    $message .= ' U anuluan për shkak se tashmë ekzistojnë porosi për ' . count($skipped) . ' shitje.';
                }

                return redirect()->route('online-orders.index')
                    ->with('success', $message);
            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->back()
                    ->with('error', 'Gabim gjatë krijimit: ' . $e->getMessage())
                    ->withInput();
            }
        }

        // Single order flow (backwards compatible)
        $validated = $request->validate([
            'sale_id' => 'required|exists:sales,id|unique:online_orders,sale_id',
            'partner_id' => 'required|exists:partners,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'currency_id' => 'required|exists:currencies,id',
            'order_amount' => 'required|numeric|min:0.01',
            'order_date' => 'required|date',
            'expected_payment_date' => 'nullable|date|after_or_equal:order_date',
            'delivery_address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            $validated['order_number'] = OnlineOrder::generateOrderNumber();
            $validated['is_paid'] = false;

            OnlineOrder::create($validated);

            return redirect()->route('online-orders.index')
                ->with('success', 'Porosia online u shtua me sukses!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gabim: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(OnlineOrder $onlineOrder)
    {
        $onlineOrder->load(['sale.items', 'partner', 'warehouse', 'currency']);
        return view('online-orders.show', compact('onlineOrder'));
    }

    public function edit(OnlineOrder $onlineOrder)
    {
        $sales = Sale::where('purchase_location', 'online')
            ->where(function ($q) use ($onlineOrder) {
                $q->whereDoesntHave('onlineOrder')
                    ->orWhere('id', $onlineOrder->sale_id);
            })
            ->with(['partner', 'warehouse', 'currency'])
            ->latest()
            ->get();

        $partners = Partner::all();
        $warehouses = Warehouse::all();
        $currencies = Currency::all();

        return view('online-orders.edit', compact('onlineOrder', 'sales', 'partners', 'warehouses', 'currencies'));
    }

    public function update(Request $request, OnlineOrder $onlineOrder)
    {
        $validated = $request->validate([
            'sale_id' => 'required|exists:sales,id|unique:online_orders,sale_id,' . $onlineOrder->id,
            'partner_id' => 'required|exists:partners,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'currency_id' => 'required|exists:currencies,id',
            'order_amount' => 'required|numeric|min:0.01',
            'order_date' => 'required|date',
            'expected_payment_date' => 'nullable|date|after_or_equal:order_date',
            'delivery_address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            $onlineOrder->update($validated);

            return redirect()->route('online-orders.index')
                ->with('success', 'Porosia u përditësua me sukses!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gabim: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(OnlineOrder $onlineOrder)
    {
        try {
            $onlineOrder->delete();
            return redirect()->route('online-orders.index')
                ->with('success', 'Porosia u fshi me sukses!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gabim: ' . $e->getMessage());
        }
    }

    // Mark as paid
    public function markAsPaid(Request $request, OnlineOrder $onlineOrder)
    {
        $validated = $request->validate([
            'payment_received_date' => 'required|date',
            'payment_method' => 'required|in:Cash,Bank',
            'notes' => 'nullable|string',
        ]);

        try {
            $onlineOrder->update([
                'is_paid' => true,
                'payment_received_date' => $validated['payment_received_date'],
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes'] ?? $onlineOrder->notes,
            ]);

            // Sync sale payment status if related sale exists
            try {
                if ($onlineOrder->sale) {
                    $onlineOrder->sale->update(['payment_status' => 'Paid']);
                }
            } catch (\Exception $e) {
                // non-fatal: log and continue
                Log::warning('Failed to sync sale payment_status after marking online order paid: ' . $e->getMessage());
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pagesa u shënua si e kryer!',
                ]);
            }

            return redirect()->route('online-orders.show', $onlineOrder->id)
                ->with('success', 'Pagesa u shënua si e kryer!');
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

    // Mark as unpaid
    public function markAsUnpaid(OnlineOrder $onlineOrder)
    {
        try {
            $onlineOrder->update([
                'is_paid' => false,
                'payment_received_date' => null,
                'payment_method' => null,
            ]);

            // Sync sale payment status if related sale exists
            try {
                if ($onlineOrder->sale) {
                    $onlineOrder->sale->update(['payment_status' => 'Unpaid']);
                }
            } catch (\Exception $e) {
                Log::warning('Failed to sync sale payment_status after marking online order unpaid: ' . $e->getMessage());
            }

            return redirect()->back()
                ->with('success', 'Statusi u ndryshua në "E Papaguar"!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gabim: ' . $e->getMessage());
        }
    }
}
