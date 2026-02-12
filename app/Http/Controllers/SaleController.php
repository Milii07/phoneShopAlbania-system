<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Seller;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\Partner;
use App\Models\Warehouse;
use App\Models\Currency;
use App\Models\PurchaseItem;
use App\Services\ExchangeRateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with(['partner', 'warehouse', 'currency', 'seller']);

        if ($request->has('status') && $request->status != 'All') {
            $query->where('sale_status', $request->status);
        }

        if ($request->has('payment_status') && $request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->has('date') && $request->date) {
            $query->whereDate('invoice_date', $request->date);
        }

        if ($request->has('partner_id') && $request->partner_id) {
            $query->where('partner_id', $request->partner_id);
        }

        if ($request->has('warehouse_id') && $request->warehouse_id) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->has('search') && $request->search) {
            $query->where('invoice_number', 'like', '%' . $request->search . '%');
        }

        $sales = $query->latest()->paginate(15);
        $partners = Partner::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();

        return view('sales.index', compact('sales', 'partners', 'warehouses'));
    }

    public function create()
    {
        $partners = Partner::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();
        $currencies = Currency::orderBy('code')->get();
        $sellers = Seller::orderBy('name')->get();
        $invoiceNumber = Sale::generateInvoiceNumber();

        return view('sales.create', compact('partners', 'warehouses', 'currencies', 'sellers', 'invoiceNumber'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_date' => 'required|date',
            'delivery_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'partner_id' => 'required|exists:partners,id',
            'seller_id' => 'required|exists:sellers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'currency_id' => 'required|exists:currencies,id',
            'sale_status' => 'required|in:Draft,PrePaid,Confirmed,Rejected',
            'payment_status' => 'required|in:Paid,Unpaid,Partial',
            'payment_method' => 'required|in:Cash,Bank',
            'payment_term' => 'nullable|string',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.unit_type' => 'nullable|string',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.tax' => 'nullable|numeric|min:0',
            'items.*.imei_numbers' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $warehouseId = $validated['warehouse_id'];
            $warehouse = Warehouse::findOrFail($warehouseId);

            $saleCurrency = Currency::find($validated['currency_id']);
            $saleCurrencyCode = $saleCurrency ? $saleCurrency->code : null;

            $subtotal = 0;
            $totalTax = 0;
            $totalDiscount = 0;
            $totalProfit = 0;
            $ownerProfit = 0;
            $allImeiNumbers = [];
            $errorMsg = [];

            // Validate items
            foreach ($request->items as $itemIndex => $item) {
                $product = Product::findOrFail($item['product_id']);

                // Check stock in warehouse
                $availableQty = $product->getQuantityInWarehouse($warehouseId);

                if ($availableQty < $item['quantity']) {
                    $errorMsg[] = "Produkti '{$product->name}' nuk ka stok të mjaftueshëm. Në stok: {$availableQty}, Kërkuar: {$item['quantity']}";
                    continue;
                }

                $quantity = $item['quantity'];
                $unitPrice = $item['unit_price'];
                $discount = $item['discount'] ?? 0;
                $tax = $item['tax'] ?? 0;

                $subtotal += ($quantity * $unitPrice);
                $totalTax += $tax;
                $totalDiscount += $discount;

                $purchasePrice = $this->getLatestPurchasePriceInCurrency($product, $warehouseId, $saleCurrencyCode);
                $itemProfit = ($unitPrice - $purchasePrice) * $quantity;
                $totalProfit += $itemProfit;
                $ownerProfit += $itemProfit * ($warehouse->profit_percentage / 100);

                // IMEI validation (existing code...)
                if (!empty($item['imei_numbers'])) {
                    $imeiArray = array_values(array_filter(array_map('trim', explode(',', $item['imei_numbers']))));
                    $imeiCount = count($imeiArray);

                    if ($imeiCount != $quantity) {
                        $errorMsg[] = "Produkti '{$product->name}': Numri i IMEI duhet të jetë {$quantity}. Ju keni {$imeiCount} IMEI.";
                        continue;
                    }

                    $uniqueImei = array_unique($imeiArray);
                    if (count($uniqueImei) != count($imeiArray)) {
                        $errorMsg[] = "Produkti '{$product->name}': Ka IMEI të dubluar në listë.";
                        continue;
                    }

                    foreach ($imeiArray as $imei) {
                        if (!preg_match('/^\d{15}$/', $imei)) {
                            $errorMsg[] = "IMEI '{$imei}' nuk është valid. IMEI duhet të jetë 15 shifra.";
                            break;
                        }

                        $existingImei = SaleItem::whereJsonContains('imei_numbers', $imei)->first();
                        if ($existingImei) {
                            $errorMsg[] = "IMEI {$imei} është shitur tashmë (Invoice #{$existingImei->sale->invoice_number}).";
                            break;
                        }

                        $purchasedImei = PurchaseItem::whereJsonContains('imei_numbers', $imei)->first();
                        if (!$purchasedImei) {
                            $errorMsg[] = "IMEI {$imei} nuk ekziston në sistem.";
                            break;
                        }

                        if (in_array($imei, $allImeiNumbers)) {
                            $errorMsg[] = "IMEI {$imei} është përdorur dy herë në këtë faturë.";
                            break;
                        }

                        $allImeiNumbers[] = $imei;
                    }
                }
            }

            if (!empty($errorMsg)) {
                DB::rollBack();
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => $errorMsg], 422);
                }
                return redirect()->back()->withErrors($errorMsg)->withInput();
            }

            $totalAmount = $subtotal - $totalDiscount + $totalTax;

            // Create Sale
            $sale = Sale::create([
                'invoice_number' => Sale::generateInvoiceNumber(),
                'invoice_date' => $validated['invoice_date'],
                'delivery_date' => $validated['delivery_date'],
                'due_date' => $validated['due_date'],
                'partner_id' => $validated['partner_id'],
                'seller_id' => $validated['seller_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'currency_id' => $validated['currency_id'],
                'sale_status' => $validated['sale_status'],
                'payment_status' => $validated['payment_status'],
                'payment_method' => $validated['payment_method'],
                'payment_term' => $validated['payment_term'],
                'subtotal' => $subtotal,
                'tax' => $totalTax,
                'discount' => $totalDiscount,
                'total_amount' => $totalAmount,
                'profit_total' => $totalProfit,
                'owner_profit' => $ownerProfit,
                'description' => $validated['description'],
                'notes' => $validated['notes'],
            ]);

            // Create Sale Items
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);

                // Check stock in warehouse
                $availableQty = $product->getQuantityInWarehouse($warehouseId);

                if ($availableQty < $item['quantity']) {
                    $errorMsg[] = "Produkti '{$product->name}' nuk ka stok të mjaftueshëm në këtë warehouse. Në stok: {$availableQty}, Kërkuar: {$item['quantity']}";
                    continue;
                }

                $quantity = $item['quantity'];
                $unitPrice = $item['unit_price'];
                $discount = $item['discount'] ?? 0;
                $tax = $item['tax'] ?? 0;
                $lineTotal = ($quantity * $unitPrice) - $discount + $tax;

                $purchasePrice = $this->getLatestPurchasePriceInCurrency($product, $warehouseId, $saleCurrencyCode);
                $itemProfit = ($unitPrice - $purchasePrice) * $quantity;
                $itemOwnerProfit = $itemProfit * ($warehouse->profit_percentage / 100);

                $imeiArray = null;
                if (!empty($item['imei_numbers'])) {
                    $imeiArray = array_values(array_filter(array_map('trim', explode(',', $item['imei_numbers']))));
                }

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'warehouse_id' => $warehouseId,
                    'category_id' => $product->category_id,
                    'brand_id' => $product->brand_id,
                    'storage' => $product->storage,
                    'ram' => $product->ram,
                    'color' => $product->color,
                    'quantity' => $quantity,
                    'unit_type' => $item['unit_type'] ?? 'Pcs',
                    'unit_price' => $unitPrice,
                    'purchase_price' => $purchasePrice,
                    'sale_price' => $unitPrice,
                    'discount' => $discount,
                    'tax' => $tax,
                    'line_total' => $lineTotal,
                    'profit_total' => $itemProfit,
                    'owner_profit' => $itemOwnerProfit,
                    'imei_numbers' => $imeiArray,
                ]);

                // Decrease product quantity if sale is confirmed
                if ($validated['sale_status'] === 'Confirmed') {
                    $currentQty = $product->getQuantityInWarehouse($warehouseId);
                    $product->warehouses()->updateExistingPivot($warehouseId, [
                        'quantity' => $currentQty - $quantity
                    ]);
                }
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Fatura u krijua me sukses!',
                    'url' => route('sales.index')
                ], 200);
            }

            return redirect()->route('sales.index')->with('success', 'Fatura u krijua me sukses!');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Sale creation error: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Gabim: ' . $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'Ka ndodhur një gabim: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $sale = Sale::with(['partner', 'warehouse', 'currency', 'seller', 'items.product', 'items.category', 'items.brand'])->findOrFail($id);
        return view('sales.show', compact('sale'));
    }

    public function edit($id)
    {
        $sale = Sale::with('items')->findOrFail($id);
        $partners = Partner::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();
        $currencies = Currency::orderBy('code')->get();
        $sellers = Seller::orderBy('name')->get();

        return view('sales.edit', compact('sale', 'partners', 'warehouses', 'currencies', 'sellers'));
    }

    public function update(Request $request, $id)
    {
        $sale = Sale::findOrFail($id);

        $validated = $request->validate([
            'invoice_date' => 'required|date',
            'delivery_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'partner_id' => 'required|exists:partners,id',
            'seller_id' => 'required|exists:sellers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'currency_id' => 'required|exists:currencies,id',
            'sale_status' => 'required|in:Draft,PrePaid,Confirmed,Rejected',
            'payment_status' => 'required|in:Paid,Unpaid,Partial',
            'payment_method' => 'required|in:Cash,Bank',
            'payment_term' => 'nullable|string',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.tax' => 'nullable|numeric|min:0',
            'items.*.imei_numbers' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $warehouseId = $validated['warehouse_id'];
            $warehouse = Warehouse::findOrFail($warehouseId);

            // Reverse old quantities if sale was confirmed
            if ($sale->sale_status === 'Confirmed') {
                foreach ($sale->items as $item) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $currentQty = $product->getQuantityInWarehouse($item->warehouse_id);
                        $product->warehouses()->updateExistingPivot($item->warehouse_id, [
                            'quantity' => $currentQty + $item->quantity
                        ]);
                    }
                }
            }

            // Determine sale currency code for conversions
            $saleCurrency = Currency::find($validated['currency_id']);
            $saleCurrencyCode = $saleCurrency ? $saleCurrency->code : null;

            $subtotal = 0;
            $totalTax = 0;
            $totalDiscount = 0;
            $totalProfit = 0;
            $ownerProfit = 0;
            $errorMsg = [];

            // Validate and calculate
            foreach ($request->items as $itemIndex => $item) {
                $product = Product::findOrFail($item['product_id']);

                $availableQty = $product->getQuantityInWarehouse($warehouseId);
                if ($availableQty < $item['quantity']) {
                    $errorMsg[] = "Produkti '{$product->name}' nuk ka stok të mjaftueshëm.";
                    continue;
                }

                $quantity = $item['quantity'];
                $unitPrice = $item['unit_price'];
                $discount = $item['discount'] ?? 0;
                $tax = $item['tax'] ?? 0;

                $subtotal += ($quantity * $unitPrice);
                $totalTax += $tax;
                $totalDiscount += $discount;

                $purchasePrice = $this->getLatestPurchasePriceInCurrency($product, $warehouseId, $saleCurrencyCode);
                $itemProfit = ($unitPrice - $purchasePrice) * $quantity;
                $totalProfit += $itemProfit;
                $ownerProfit += $itemProfit * ($warehouse->profit_percentage / 100);

                // IMEI validation
                if (!empty($item['imei_numbers'])) {
                    $imeiArray = array_values(array_filter(array_map('trim', explode(',', $item['imei_numbers']))));

                    if (count($imeiArray) != $quantity) {
                        $errorMsg[] = "Produkti '{$product->name}': IMEI duhet të jenë {$quantity}.";
                        continue;
                    }

                    // Check existing IMEI (excluding current sale)
                    foreach ($imeiArray as $imei) {
                        $existingImei = SaleItem::where('sale_id', '!=', $sale->id)
                            ->whereJsonContains('imei_numbers', $imei)
                            ->first();

                        if ($existingImei) {
                            $errorMsg[] = "IMEI {$imei} është shitur tashmë.";
                            break;
                        }
                    }
                }
            }

            if (!empty($errorMsg)) {
                DB::rollBack();
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => $errorMsg], 422);
                }
                return redirect()->back()->withErrors($errorMsg)->withInput();
            }

            $totalAmount = $subtotal - $totalDiscount + $totalTax;

            // Update Sale
            $sale->update([
                'invoice_date' => $validated['invoice_date'],
                'delivery_date' => $validated['delivery_date'],
                'due_date' => $validated['due_date'],
                'partner_id' => $validated['partner_id'],
                'seller_id' => $validated['seller_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'currency_id' => $validated['currency_id'],
                'sale_status' => $validated['sale_status'],
                'payment_status' => $validated['payment_status'],
                'payment_method' => $validated['payment_method'],
                'payment_term' => $validated['payment_term'],
                'subtotal' => $subtotal,
                'tax' => $totalTax,
                'discount' => $totalDiscount,
                'total_amount' => $totalAmount,
                'profit_total' => $totalProfit,
                'owner_profit' => $ownerProfit,
                'description' => $validated['description'],
                'notes' => $validated['notes'],
            ]);

            // Delete old items
            $sale->items()->delete();

            // Create new items
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);

                $quantity = $item['quantity'];
                $unitPrice = $item['unit_price'];
                $discount = $item['discount'] ?? 0;
                $tax = $item['tax'] ?? 0;
                $lineTotal = ($quantity * $unitPrice) - $discount + $tax;

                $purchasePrice = $this->getLatestPurchasePriceInCurrency($product, $warehouseId, $saleCurrencyCode);
                $itemProfit = ($unitPrice - $purchasePrice) * $quantity;
                $itemOwnerProfit = $itemProfit * ($warehouse->profit_percentage / 100);

                $imeiArray = null;
                if (!empty($item['imei_numbers'])) {
                    $imeiArray = array_values(array_filter(array_map('trim', explode(',', $item['imei_numbers']))));
                }

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'warehouse_id' => $warehouseId,
                    'category_id' => $product->category_id,
                    'brand_id' => $product->brand_id,
                    'storage' => $product->storage,
                    'ram' => $product->ram,
                    'color' => $product->color,
                    'quantity' => $quantity,
                    'unit_type' => $item['unit_type'] ?? 'Pcs',
                    'unit_price' => $unitPrice,
                    'purchase_price' => $purchasePrice,
                    'sale_price' => $unitPrice,
                    'discount' => $discount,
                    'tax' => $tax,
                    'line_total' => $lineTotal,
                    'profit_total' => $itemProfit,
                    'owner_profit' => $itemOwnerProfit,
                    'imei_numbers' => $imeiArray,
                ]);

                // Decrease quantity if confirmed
                if ($validated['sale_status'] === 'Confirmed') {
                    $currentQty = $product->getQuantityInWarehouse($warehouseId);
                    $product->warehouses()->updateExistingPivot($warehouseId, [
                        'quantity' => $currentQty - $quantity
                    ]);
                }
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Fatura u përditësua me sukses!',
                    'url' => route('sales.index')
                ], 200);
            }

            return redirect()->route('sales.index')->with('success', 'Fatura u përditësua me sukses!');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'Gabim: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $sale = Sale::findOrFail($id);

            // Reverse quantities if sale was confirmed
            if ($sale->sale_status === 'Confirmed') {
                foreach ($sale->items as $item) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $currentQty = $product->getQuantityInWarehouse($item->warehouse_id);
                        $product->warehouses()->updateExistingPivot($item->warehouse_id, [
                            'quantity' => $currentQty + $item->quantity
                        ]);
                    }
                }
            }

            $sale->delete();

            DB::commit();

            return redirect()->route('sales.index')->with('success', 'Fatura u fshi me sukses!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('sales.index')->with('error', 'Gabim: ' . $e->getMessage());
        }
    }

    public function searchProducts(Request $request)
    {
        $search = $request->get('q', '');
        $warehouseId = $request->get('warehouse_id');

        $query = Product::with(['category', 'brand', 'currency', 'warehouses']);

        if ($warehouseId) {
            $query->whereHas('warehouses', function ($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId)
                    ->where('quantity', '>', 0);
            });
        }

        $products = $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('storage', 'like', "%{$search}%")
                ->orWhere('ram', 'like', "%{$search}%")
                ->orWhere('color', 'like', "%{$search}%");
        })
            ->limit(20)
            ->get()
            ->map(function ($product) use ($warehouseId) {
                if ($warehouseId) {
                    $product->quantity = $product->getQuantityInWarehouse($warehouseId);
                } else {
                    $product->quantity = $product->warehouses->sum('pivot.quantity');
                }
                return $product;
            });

        return response()->json($products);
    }

    public function updatePaymentStatus(Request $request, $id)
    {
        $sale = Sale::findOrFail($id);
        $sale->update(['payment_status' => $request->payment_status]);

        return response()->json(['success' => true, 'message' => 'Payment status updated']);
    }

    /**
     * Get the latest purchase price for a product and convert it to the target currency code.
     * If purchase record has a different currency, use ExchangeRateService to convert.
     * Returns a float price in target currency (or original if conversion not possible).
     *
     * @param \App\Models\Product $product
     * @param int|null $warehouseId
     * @param string|null $targetCurrencyCode
     * @return float
     */
    private function getLatestPurchasePriceInCurrency($product, $warehouseId = null, $targetCurrencyCode = null)
    {
        // Fetch the latest PurchaseItem (warehouse-scoped first)
        $query = PurchaseItem::where('product_id', $product->id)->with(['purchase.currency']);

        if ($warehouseId) {
            $query->whereHas('purchase', function ($q) use ($warehouseId) {
                $q->where('warehouse_id', $warehouseId);
            });
        }

        $purchaseItem = $query->orderByDesc('created_at')->first();

        if (!$purchaseItem && $warehouseId) {
            $purchaseItem = PurchaseItem::where('product_id', $product->id)->orderByDesc('created_at')->first();
        }

        $rawPrice = null;
        if ($purchaseItem) {
            if (isset($purchaseItem->line_total)) {
                $rawPrice = (float) $purchaseItem->line_total;
            } elseif (isset($purchaseItem->unit_cost)) {
                $rawPrice = (float) $purchaseItem->unit_cost;
            }

            $purchaseCurrencyCode = null;
            if (!empty($purchaseItem->purchase) && !empty($purchaseItem->purchase->currency)) {
                $purchaseCurrencyCode = $purchaseItem->purchase->currency->code ?? null;
            }

            if ($rawPrice !== null && $targetCurrencyCode && $purchaseCurrencyCode && $purchaseCurrencyCode !== $targetCurrencyCode) {
                try {
                    $exchange = new ExchangeRateService();
                    $rates = $exchange->getExchangeRates();
                    if (isset($rates['data']) && is_array($rates['data'])) {
                        $rates = $rates['data'];
                    }

                    if (isset($rates[$purchaseCurrencyCode]) && $targetCurrencyCode == 'LEK') {
                        $rateFrom = $rates[$purchaseCurrencyCode]['buy'] ?? $rates[$purchaseCurrencyCode]['sell'] ?? null;

                        if ($rateFrom) {
                            $amountInLeke = $rawPrice * $rateFrom;
                            return (float) $amountInLeke;
                        }
                    }
                } catch (\Exception $e) {
                    if ($rawPrice !== null) {
                        return (float) $rawPrice;
                    }
                }
            }

            if ($rawPrice !== null) {
                return (float) $rawPrice;
            }
        }


        // Fallbacks
        if (isset($product->purchase_price)) {
            return (float) $product->purchase_price;
        }

        return 0.0;
    }

    public function dailyReport(Request $request)
    {
        $date = $request->input('date', now()->format('Y-m-d'));

        $sales = Sale::with(['warehouse', 'currency', 'items.product'])
            ->whereDate('invoice_date', $date)
            ->where('sale_status', 'Confirmed')
            ->get();

        $report = $sales->groupBy('warehouse_id')->map(function ($warehouseSales) {
            $warehouse = $warehouseSales->first()->warehouse;

            // Aggregate per-currency values for this warehouse
            $byCurrency = [];
            foreach ($warehouseSales as $s) {
                $code = $s->currency->code ?? 'LEK';
                $symbol = $s->currency->symbol ?? '';

                if (!isset($byCurrency[$code])) {
                    $byCurrency[$code] = [
                        'symbol' => $symbol,
                        'xhiro' => 0.0,
                        'fitimi_total' => 0.0,
                        'fitimi_juaj' => 0.0,
                        'cash' => 0.0,
                        'bank' => 0.0,
                    ];
                }

                $byCurrency[$code]['xhiro'] += (float) $s->total_amount;
                $byCurrency[$code]['fitimi_total'] += (float) $s->profit_total;
                $byCurrency[$code]['fitimi_juaj'] += (float) $s->owner_profit;

                if ($s->payment_method === 'Cash') {
                    $byCurrency[$code]['cash'] += (float) $s->total_amount;
                } elseif ($s->payment_method === 'Bank') {
                    $byCurrency[$code]['bank'] += (float) $s->total_amount;
                }
            }

            // Format per-currency for view
            $byCurrencyFormatted = collect($byCurrency)->map(function ($v) {
                return [
                    'symbol' => $v['symbol'],
                    'xhiro' => number_format($v['xhiro'], 2),
                    'fitimi_total' => number_format($v['fitimi_total'], 2),
                    'fitimi_juaj' => number_format($v['fitimi_juaj'], 2),
                    'cash' => number_format($v['cash'], 2),
                    'bank' => number_format($v['bank'], 2),
                ];
            })->toArray();

            $totalXhiro = array_sum(array_column($byCurrency, 'xhiro'));
            $totalFitimi = array_sum(array_column($byCurrency, 'fitimi_total'));
            $totalOwner = array_sum(array_column($byCurrency, 'fitimi_juaj'));

            return [
                'dyqani' => $warehouse->name,
                'lokacioni' => $warehouse->location,
                'perqindja_fitimit' => $warehouse->profit_percentage . '%',
                'xhiro_totale' => number_format($totalXhiro, 2),
                'fitimi_total' => number_format($totalFitimi, 2),
                'fitimi_juaj' => number_format($totalOwner, 2),
                'shitje_count' => $warehouseSales->count(),
                'by_currency' => $byCurrencyFormatted,
            ];
        });

        // Overall totals grouped by currency
        $totalsByCurrency = [];
        foreach ($sales as $s) {
            $code = $s->currency->code ?? 'UNK';
            $symbol = $s->currency->symbol ?? '';
            if (!isset($totalsByCurrency[$code])) {
                $totalsByCurrency[$code] = [
                    'symbol' => $symbol,
                    'xhiro' => 0.0,
                    'fitimi_total' => 0.0,
                    'fitimi_juaj' => 0.0,
                    'cash' => 0.0,
                    'bank' => 0.0,
                ];
            }
            $totalsByCurrency[$code]['xhiro'] += (float) $s->total_amount;
            $totalsByCurrency[$code]['fitimi_total'] += (float) $s->profit_total;
            $totalsByCurrency[$code]['fitimi_juaj'] += (float) $s->owner_profit;
            if ($s->payment_method === 'Cash') {
                $totalsByCurrency[$code]['cash'] += (float) $s->total_amount;
            } elseif ($s->payment_method === 'Bank') {
                $totalsByCurrency[$code]['bank'] += (float) $s->total_amount;
            }
        }

        $totalsFormatted = collect($totalsByCurrency)->map(function ($v) {
            return [
                'symbol' => $v['symbol'],
                'xhiro' => number_format($v['xhiro'], 2),
                'fitimi_total' => number_format($v['fitimi_total'], 2),
                'fitimi_juaj' => number_format($v['fitimi_juaj'], 2),
                'cash' => number_format($v['cash'], 2),
                'bank' => number_format($v['bank'], 2),
            ];
        })->toArray();

        $totals = [
            'by_currency' => $totalsFormatted,
            'shitje_totale' => $sales->count(),
        ];

        return view('sales.daily-report', compact('date', 'report', 'totals'));
    }
}
