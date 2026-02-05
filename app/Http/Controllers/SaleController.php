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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with(['partner', 'warehouse', 'currency', 'seller']);

        // Filter by status
        if ($request->has('status') && $request->status != 'All') {
            $query->where('sale_status', $request->status);
        }

        // Filter by payment status
        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by date
        if ($request->has('date')) {
            $query->whereDate('invoice_date', $request->date);
        }

        // Filter by client
        if ($request->has('partner_id')) {
            $query->where('partner_id', $request->partner_id);
        }

        // Filter by warehouse
        if ($request->has('warehouse_id') && $request->warehouse_id) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // Search by invoice number
        if ($request->has('search')) {
            $query->where('invoice_number', 'like', '%' . $request->search . '%');
        }

        $sales = $query->latest()->paginate(15);
        $partners = Partner::all();
        $warehouses = Warehouse::orderBy('name')->get();

        return view('sales.index', compact('sales', 'partners', 'warehouses'));
    }

    public function create()
    {
        $partners = Partner::all();
        $warehouses = Warehouse::all();
        $currencies = Currency::all();
        $sellers = Seller::all();
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
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.tax' => 'nullable|numeric|min:0',
            'items.*.imei_numbers' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $subtotal = 0;
            $totalTax = 0;
            $totalDiscount = 0;
            $allImeiNumbers = [];
            $errorMsg = [];

            // Validate items and calculate totals
            foreach ($request->items as $itemIndex => $item) {
                $product = Product::find($item['product_id']);

                // Check stock availability
                if ($product->quantity < $item['quantity']) {
                    $errorMsg[] = "Produkti '{$product->name}' nuk ka stok të mjaftueshëm. Në stok: {$product->quantity}, Kërkuar: {$item['quantity']}";
                    continue;
                }

                $quantity = $item['quantity'];
                $unitPrice = $item['unit_price'];
                $discount = $item['discount'] ?? 0;
                $tax = $item['tax'] ?? 0;

                $subtotal += ($quantity * $unitPrice);
                $totalTax += $tax;
                $totalDiscount += $discount;

                // IMEI validation if provided
                if (!empty($item['imei_numbers'])) {
                    $imeiArray = array_values(array_filter(array_map('trim', explode(',', $item['imei_numbers']))));
                    $imeiCount = count($imeiArray);

                    if ($imeiCount != $quantity) {
                        $errorMsg[] = "Produkti #" . ($itemIndex + 1) . ": Numri i IMEI duhet të jetë i barabartë me sasinë ({$quantity}). Ju keni vendosur {$imeiCount} IMEI.";
                        continue;
                    }

                    $uniqueImei = array_unique($imeiArray);
                    if (count($uniqueImei) != count($imeiArray)) {
                        $errorMsg[] = "Produkti #" . ($itemIndex + 1) . ": Ka IMEI të dubluar.";
                        continue;
                    }

                    foreach ($imeiArray as $imei) {
                        if (!preg_match('/^\d{15}$/', $imei)) {
                            $errorMsg[] = "Produkti #" . ($itemIndex + 1) . ": IMEI '{$imei}' nuk është valid. IMEI duhet të jetë 15 shifra.";
                            break;
                        }
                    }

                    // Check if IMEI is already sold
                    foreach ($imeiArray as $imei) {
                        $existingImei = SaleItem::whereJsonContains('imei_numbers', $imei)->first();
                        if ($existingImei) {
                            $errorMsg[] = "IMEI {$imei} është shitur tashmë (Invoice #{$existingImei->sale->invoice_number}).";
                            break;
                        }

                        // Check if IMEI exists in purchases
                        $purchasedImei = PurchaseItem::whereJsonContains('imei_numbers', $imei)->first();
                        if (!$purchasedImei) {
                            $errorMsg[] = "IMEI {$imei} nuk ekziston në sistem. Duhet të blihet më parë.";
                            break;
                        }

                        if (in_array($imei, $allImeiNumbers)) {
                            $errorMsg[] = "IMEI {$imei} është përdorur më shumë se një herë në këtë faturë.";
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
                return redirect()->back()->with('error', implode('<br>', $errorMsg))->withInput();
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
                'description' => $validated['description'],
                'notes' => $validated['notes'],
            ]);

            // Create Sale Items
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);

                $quantity = $item['quantity'];
                $unitPrice = $item['unit_price'];
                $discount = $item['discount'] ?? 0;
                $tax = $item['tax'] ?? 0;
                $lineTotal = ($quantity * $unitPrice) - $discount + $tax;


                $imeiArray = null;
                if (!empty($item['imei_numbers'])) {
                    $imeiArray = array_values(array_filter(array_map('trim', explode(',', $item['imei_numbers']))));
                }

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'warehouse_id' => $product->warehouse_id,
                    'category_id' => $product->category_id,
                    'brand_id' => $product->brand_id,
                    'storage' => $product->storage,
                    'ram' => $product->ram,
                    'color' => $product->color,
                    'quantity' => $quantity,
                    'unit_type' => $item['unit_type'] ?? 'Pcs',
                    'unit_price' => $unitPrice,
                    'purchase_price' => $product->price,     // E RE - Çmimi i blerjes
                    'sale_price' => $unitPrice,              // E RE - Çmimi i shitjes
                    'discount' => $discount,
                    'tax' => $tax,
                    'line_total' => $lineTotal,
                    'imei_numbers' => $imeiArray,
                ]);

                // Decrease product quantity if sale is confirmed
                if ($validated['sale_status'] === 'Confirmed') {
                    $product->decrement('quantity', $quantity);
                }
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Invoice created successfully',
                    'url' => route('sales.index')
                ], 200);
            }

            return redirect()->route('sales.index')->with('success', 'Fatura u krijua me sukses!');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'Ka ndodhur një gabim: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $sale = Sale::with(['partner', 'warehouse', 'currency', 'items'])->findOrFail($id);
        return view('sales.show', compact('sale'));
    }

    public function edit($id)
    {
        $sale = Sale::with('items')->findOrFail($id);
        $partners = Partner::all();
        $warehouses = Warehouse::all();
        $currencies = Currency::all();
        $sellers = Seller::all();

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

            // Reverse old quantities if sale was confirmed
            if ($sale->sale_status === 'Confirmed') {
                foreach ($sale->items as $item) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->increment('quantity', $item->quantity);
                    }
                }
            }

            $subtotal = 0;
            $totalTax = 0;
            $totalDiscount = 0;
            $allImeiNumbers = [];
            $errorMsg = [];

            // Validate items
            foreach ($request->items as $itemIndex => $item) {
                $product = Product::find($item['product_id']);

                // Check stock
                if ($product->quantity < $item['quantity']) {
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

                // IMEI validation
                if (!empty($item['imei_numbers'])) {
                    $imeiArray = array_values(array_filter(array_map('trim', explode(',', $item['imei_numbers']))));
                    $imeiCount = count($imeiArray);

                    if ($imeiCount != $quantity) {
                        $errorMsg[] = "Produkti #" . ($itemIndex + 1) . ": Numri i IMEI duhet të jetë i barabartë me sasinë.";
                        continue;
                    }

                    $uniqueImei = array_unique($imeiArray);
                    if (count($uniqueImei) != count($imeiArray)) {
                        $errorMsg[] = "Produkti #" . ($itemIndex + 1) . ": Ka IMEI të dubluar.";
                        continue;
                    }

                    foreach ($imeiArray as $imei) {
                        if (!preg_match('/^\d{15}$/', $imei)) {
                            $errorMsg[] = "Produkti #" . ($itemIndex + 1) . ": IMEI '{$imei}' nuk është valid.";
                            break;
                        }
                    }

                    // Check existing IMEI (excluding current sale)
                    $existingImeis = SaleItem::where('sale_id', '!=', $sale->id)
                        ->get()
                        ->pluck('imei_numbers')
                        ->flatten()
                        ->filter()
                        ->toArray();

                    $conflictingImeis = array_intersect($imeiArray, $existingImeis);
                    if (!empty($conflictingImeis)) {
                        $errorMsg[] = 'IMEI ' . reset($conflictingImeis) . ' është shitur tashmë.';
                        break;
                    }

                    $allImeiNumbers = array_merge($allImeiNumbers, $imeiArray);
                }
            }

            if (!empty($errorMsg)) {
                DB::rollBack();
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => $errorMsg], 422);
                }
                return redirect()->back()->with('error', implode('<br>', $errorMsg))->withInput();
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
                'description' => $validated['description'],
                'notes' => $validated['notes'],
            ]);

            // Delete old items
            $sale->items()->delete();

            // Create new items
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);

                $quantity = $item['quantity'];
                $unitPrice = $item['unit_price'];
                $discount = $item['discount'] ?? 0;
                $tax = $item['tax'] ?? 0;
                $lineTotal = ($quantity * $unitPrice) - $discount + $tax;

                $imeiArray = null;
                if (!empty($item['imei_numbers'])) {
                    $imeiArray = array_values(array_filter(array_map('trim', explode(',', $item['imei_numbers']))));
                }

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'warehouse_id' => $product->warehouse_id,
                    'category_id' => $product->category_id,
                    'brand_id' => $product->brand_id,
                    'storage' => $product->storage,
                    'ram' => $product->ram,
                    'color' => $product->color,
                    'quantity' => $quantity,
                    'unit_type' => $item['unit_type'] ?? 'Pcs',
                    'unit_price' => $unitPrice,
                    'purchase_price' => $product->price,     // E RE - Çmimi i blerjes
                    'sale_price' => $unitPrice,              // E RE - Çmimi i shitjes
                    'discount' => $discount,
                    'tax' => $tax,
                    'line_total' => $lineTotal,
                    'imei_numbers' => $imeiArray,
                ]);
                // Decrease quantity if confirmed
                if ($validated['sale_status'] === 'Confirmed') {
                    $product->decrement('quantity', $quantity);
                }
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Invoice updated successfully',
                    'url' => route('sales.index')
                ], 200);
            }

            return redirect()->route('sales.index')->with('success', 'Fatura u përditësua me sukses!');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'Ka ndodhur një gabim: ' . $e->getMessage())->withInput();
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
                        $product->increment('quantity', $item->quantity);
                    }
                }
            }

            $sale->delete();

            DB::commit();

            return redirect()->route('sales.index')->with('success', 'Fatura u fshi me sukses!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('sales.index')->with('error', 'Ka ndodhur një gabim: ' . $e->getMessage());
        }
    }

    // API Methods
    public function searchProducts(Request $request)
    {
        $search = $request->get('q', '');

        $products = Product::with(['category', 'brand', 'currency', 'warehouse'])
            ->where('quantity', '>', 0) // Only products in stock
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('storage', 'like', "%{$search}%")
                    ->orWhere('color', 'like', "%{$search}%");
            })
            ->limit(10)
            ->get();

        return response()->json($products);
    }

    public function updatePaymentStatus(Request $request, $id)
    {
        $sale = Sale::findOrFail($id);
        $sale->update(['payment_status' => $request->payment_status]);

        return response()->json(['success' => true, 'message' => 'Payment status updated']);
    }

    // RAPORTI DITOR I SHITJEVE
    public function dailyReport(Request $request)
    {
        $date = $request->input('date', now()->format('Y-m-d'));

        // Load confirmed sales for the requested date including items and relations
        $sales = Sale::with(['warehouse', 'currency', 'items'])
            ->whereDate('invoice_date', $date)
            ->where('sale_status', 'Confirmed')
            ->get();

        $sales->each(function ($sale) {
            $calculatedProfit = 0;

            foreach ($sale->items as $item) {
                $saleUnit = data_get($item, 'sale_price', data_get($item, 'unit_price', 0));
                $purchaseUnit = data_get($item, 'purchase_price', 0);
                $quantity = data_get($item, 'quantity', 0);

                $itemProfit = ($saleUnit - $purchaseUnit) * $quantity;

                $item->calculated_profit = $itemProfit;

                $calculatedProfit += $itemProfit;
            }

            $sale->calculated_profit_total = $calculatedProfit;

            $warehousePct = optional($sale->warehouse)->profit_percentage ?? 0;
            $sale->calculated_owner_profit = $calculatedProfit * ($warehousePct / 100);
        });

        $report = $sales->groupBy('warehouse_id')->map(function ($warehouseSales) {
            $warehouse = $warehouseSales->first()->warehouse;

            // aggregate using the computed fields instead of relying on stored columns
            $xhiro_totale = $warehouseSales->sum('total_amount');
            $fitimi_total = $warehouseSales->sum('calculated_profit_total');
            $fitimi_juaj = $warehouseSales->sum('calculated_owner_profit');

            return [
                'dyqani' => $warehouse->name,
                'lokacioni' => $warehouse->location,
                'perqindja_fitimit' => ($warehouse->profit_percentage ?? 0) . '%',
                'xhiro_totale' => number_format($xhiro_totale, 2),
                'fitimi_total' => number_format($fitimi_total, 2),
                'fitimi_juaj' => number_format($fitimi_juaj, 2),
                'shitje_count' => $warehouseSales->count(),

                // Ndarje sipas monedhës
                'xhiro_euro' => number_format(
                    $warehouseSales->filter(function ($s) {
                        return optional($s->currency)->code === 'EUR';
                    })->sum('total_amount'),
                    2
                ),
                'xhiro_leke' => number_format(
                    $warehouseSales->filter(function ($s) {
                        return optional($s->currency)->code === 'ALL';
                    })->sum('total_amount'),
                    2
                ),

                // Ndarje sipas mënyrës së pagesës
                'pagesa_cash' => number_format(
                    $warehouseSales->where('payment_method', 'Cash')->sum('total_amount'),
                    2
                ),
                'pagesa_banke' => number_format(
                    $warehouseSales->where('payment_method', 'Bank')->sum('total_amount'),
                    2
                ),
            ];
        });

        $totals = [
            'xhiro_totale' => number_format($sales->sum('total_amount'), 2),
            'fitimi_total' => number_format($sales->sum('calculated_profit_total'), 2),
            'fitimi_juaj' => number_format($sales->sum('calculated_owner_profit'), 2),
            'shitje_totale' => $sales->count(),
        ];

        return view('sales.daily-report', compact('date', 'report', 'totals'));
    }
}
