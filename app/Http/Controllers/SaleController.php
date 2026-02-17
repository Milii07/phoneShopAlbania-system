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
use App\Models\SellerBonus;
use App\Services\ExchangeRateService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with(['partner', 'warehouse', 'currency', 'seller']);

        if ($request->has('status') && $request->status != 'All') {
            $query->whereRaw('LOWER(sale_status) = ?', [strtolower($request->status)]);
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
            'invoice_date'              => 'required|date',
            'delivery_date'             => 'nullable|date',
            'due_date'                  => 'nullable|date',
            'partner_id'                => 'required|exists:partners,id',
            'seller_id'                 => 'required|exists:sellers,id',
            'warehouse_id'              => 'required|exists:warehouses,id',
            'currency_id'               => 'required|exists:currencies,id',
            'sale_status'               => 'required|in:Draft,PrePaid,Confirmed,Rejected',
            'payment_status'            => 'required|in:Paid,Unpaid,Partial',
            'payment_method'            => 'required|in:Cash,Bank',
            'payment_term'              => 'nullable|string',
            'description'               => 'nullable|string',
            'notes'                     => 'nullable|string',
            'items'                     => 'required|array|min:1',
            'items.*.product_id'        => 'required|exists:products,id',
            'items.*.quantity'          => 'required|integer|min:1',
            'items.*.unit_price'        => 'required|numeric|min:0',
            'items.*.unit_type'         => 'nullable|string',
            'items.*.discount'          => 'nullable|numeric|min:0',
            'items.*.tax'               => 'nullable|numeric|min:0',
            'items.*.imei_numbers'      => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $warehouseId     = $validated['warehouse_id'];
            $warehouse       = Warehouse::findOrFail($warehouseId);
            $saleCurrency    = Currency::find($validated['currency_id']);
            $saleCurrencyCode = $saleCurrency ? $saleCurrency->code : null;

            $subtotal      = 0;
            $totalTax      = 0;
            $totalDiscount = 0;
            $totalProfit   = 0;
            $ownerProfit   = 0;
            $allImeiNumbers = [];
            $errorMsg      = [];

            foreach ($request->items as $itemIndex => $item) {
                $product      = Product::findOrFail($item['product_id']);
                $availableQty = $product->getQuantityInWarehouse($warehouseId);

                if ($availableQty < $item['quantity']) {
                    $errorMsg[] = "Produkti '{$product->name}' nuk ka stok të mjaftueshëm. Në stok: {$availableQty}, Kërkuar: {$item['quantity']}";
                    continue;
                }

                $quantity  = $item['quantity'];
                $unitPrice = $item['unit_price'];
                $discount  = $item['discount'] ?? 0;
                $tax       = $item['tax'] ?? 0;

                $subtotal      += ($quantity * $unitPrice);
                $totalTax      += $tax;
                $totalDiscount += $discount;

                $purchasePrice  = $this->getLatestPurchasePriceInCurrency($product, $warehouseId, $saleCurrencyCode);
                $itemProfit     = ($unitPrice - $purchasePrice) * $quantity;
                $totalProfit   += $itemProfit;
                $ownerProfit   += $itemProfit * ($warehouse->profit_percentage / 100);

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

            $sale = Sale::create([
                'invoice_number' => Sale::generateInvoiceNumber(),
                'invoice_date'   => $validated['invoice_date'],
                'delivery_date'  => $validated['delivery_date'],
                'due_date'       => $validated['due_date'],
                'partner_id'     => $validated['partner_id'],
                'seller_id'      => $validated['seller_id'],
                'warehouse_id'   => $validated['warehouse_id'],
                'currency_id'    => $validated['currency_id'],
                'sale_status'    => $validated['sale_status'],
                'payment_status' => $validated['payment_status'],
                'payment_method' => $validated['payment_method'],
                'payment_term'   => $validated['payment_term'],
                'subtotal'       => $subtotal,
                'tax'            => $totalTax,
                'discount'       => $totalDiscount,
                'total_amount'   => $totalAmount,
                'profit_total'   => $totalProfit,
                'owner_profit'   => $ownerProfit,
                'description'    => $validated['description'],
                'notes'          => $validated['notes'],
            ]);

            foreach ($request->items as $item) {
                $product      = Product::findOrFail($item['product_id']);
                $availableQty = $product->getQuantityInWarehouse($warehouseId);

                if ($availableQty < $item['quantity']) {
                    $errorMsg[] = "Produkti '{$product->name}' nuk ka stok të mjaftueshëm në këtë warehouse. Në stok: {$availableQty}, Kërkuar: {$item['quantity']}";
                    continue;
                }

                $quantity      = $item['quantity'];
                $unitPrice     = $item['unit_price'];
                $discount      = $item['discount'] ?? 0;
                $tax           = $item['tax'] ?? 0;
                $lineTotal     = ($quantity * $unitPrice) - $discount + $tax;
                $purchasePrice = $this->getLatestPurchasePriceInCurrency($product, $warehouseId, $saleCurrencyCode);
                $itemProfit    = ($unitPrice - $purchasePrice) * $quantity;
                $itemOwnerProfit = $itemProfit * ($warehouse->profit_percentage / 100);

                $imeiArray = null;
                if (!empty($item['imei_numbers'])) {
                    $imeiArray = array_values(array_filter(array_map('trim', explode(',', $item['imei_numbers']))));
                }

                SaleItem::create([
                    'sale_id'        => $sale->id,
                    'product_id'     => $product->id,
                    'product_name'   => $product->name,
                    'warehouse_id'   => $warehouseId,
                    'category_id'    => $product->category_id,
                    'brand_id'       => $product->brand_id,
                    'storage'        => $product->storage,
                    'ram'            => $product->ram,
                    'color'          => $product->color,
                    'quantity'       => $quantity,
                    'unit_type'      => $item['unit_type'] ?? 'Pcs',
                    'unit_price'     => $unitPrice,
                    'purchase_price' => $purchasePrice,
                    'sale_price'     => $unitPrice,
                    'discount'       => $discount,
                    'tax'            => $tax,
                    'line_total'     => $lineTotal,
                    'profit_total'   => $itemProfit,
                    'owner_profit'   => $itemOwnerProfit,
                    'imei_numbers'   => $imeiArray,
                ]);

                if ($validated['sale_status'] === 'Confirmed') {
                    $currentQty = $product->getQuantityInWarehouse($warehouseId);
                    $product->warehouses()->updateExistingPivot($warehouseId, [
                        'quantity' => $currentQty - $quantity
                    ]);
                }
            }

            // Update or create seller bonus
            try {
                $sale->load('items.product.category');

                $invoiceDate = Carbon::parse($sale->invoice_date);
                $periodStart = $invoiceDate->copy()->startOfMonth()->toDateString();
                $periodEnd   = $invoiceDate->copy()->endOfMonth()->toDateString();

                $phoneTotal     = 0.0;
                $accessoryTotal = 0.0;

                foreach ($sale->items as $item) {
                    $line         = (float) ($item->line_total ?? 0);
                    $categoryName = optional($item->product->category)->name ?? '';

                    if ($categoryName && (stripos($categoryName, 'phone') !== false || stripos($categoryName, 'telefon') !== false)) {
                        $phoneTotal += $line;
                    } else {
                        $accessoryTotal += $line;
                    }
                }

                $bonus = SellerBonus::firstOrNew([
                    'seller_id'    => $sale->seller_id,
                    'period_start' => $periodStart,
                    'period_end'   => $periodEnd,
                ]);

                $bonus->phone_sales_total      = ($bonus->phone_sales_total ?? 0) + $phoneTotal;
                $bonus->accessory_sales_total  = ($bonus->accessory_sales_total ?? 0) + $accessoryTotal;
                $bonus->total_sales_count      = ($bonus->total_sales_count ?? 0) + 1;
                $bonus->phone_bonus_percentage = $bonus->phone_bonus_percentage ?? config('seller_bonus.phone_percentage', 0);
                $bonus->accessory_bonus_percentage = $bonus->accessory_bonus_percentage ?? config('seller_bonus.accessory_percentage', 0);
                $bonus->phone_bonus_amount     = ($bonus->phone_sales_total) * (($bonus->phone_bonus_percentage ?? 0) / 100);
                $bonus->accessory_bonus_amount = ($bonus->accessory_sales_total) * (($bonus->accessory_bonus_percentage ?? 0) / 100);
                $bonus->total_bonus            = ($bonus->phone_bonus_amount ?? 0) + ($bonus->accessory_bonus_amount ?? 0);

                $bonus->save();
            } catch (\Exception $e) {
                Log::warning('Failed to update seller bonus for sale ' . ($sale->id ?? 'unknown') . ': ' . $e->getMessage());
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Fatura u krijua me sukses!',
                    'url'     => route('sales.index')
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
        $partners   = Partner::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();
        $currencies = Currency::orderBy('code')->get();
        $sellers    = Seller::orderBy('name')->get();

        return view('sales.edit', compact('sale', 'partners', 'warehouses', 'currencies', 'sellers'));
    }

    public function update(Request $request, $id)
    {
        $sale = Sale::findOrFail($id);

        $validated = $request->validate([
            'invoice_date'          => 'required|date',
            'delivery_date'         => 'nullable|date',
            'due_date'              => 'nullable|date',
            'partner_id'            => 'required|exists:partners,id',
            'seller_id'             => 'required|exists:sellers,id',
            'warehouse_id'          => 'required|exists:warehouses,id',
            'currency_id'           => 'required|exists:currencies,id',
            'sale_status'           => 'required|in:Draft,PrePaid,Confirmed,Rejected',
            'payment_status'        => 'required|in:Paid,Unpaid,Partial',
            'payment_method'        => 'required|in:Cash,Bank',
            'purchase_location'     => 'required|in:shop,online',
            'payment_term'          => 'nullable|string',
            'description'           => 'nullable|string',
            'notes'                 => 'nullable|string',
            'items'                 => 'required|array|min:1',
            'items.*.product_id'    => 'required|exists:products,id',
            'items.*.quantity'      => 'required|integer|min:1',
            'items.*.unit_price'    => 'required|numeric|min:0',
            'items.*.discount'      => 'nullable|numeric|min:0',
            'items.*.tax'           => 'nullable|numeric|min:0',
            'items.*.imei_numbers'  => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $warehouseId = $validated['warehouse_id'];
            $warehouse   = Warehouse::findOrFail($warehouseId);

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

            $saleCurrency    = Currency::find($validated['currency_id']);
            $saleCurrencyCode = $saleCurrency ? $saleCurrency->code : null;

            $subtotal      = 0;
            $totalTax      = 0;
            $totalDiscount = 0;
            $totalProfit   = 0;
            $ownerProfit   = 0;
            $errorMsg      = [];

            foreach ($request->items as $itemIndex => $item) {
                $product      = Product::findOrFail($item['product_id']);
                $availableQty = $product->getQuantityInWarehouse($warehouseId);

                if ($availableQty < $item['quantity']) {
                    $errorMsg[] = "Produkti '{$product->name}' nuk ka stok të mjaftueshëm.";
                    continue;
                }

                $quantity  = $item['quantity'];
                $unitPrice = $item['unit_price'];
                $discount  = $item['discount'] ?? 0;
                $tax       = $item['tax'] ?? 0;

                $subtotal      += ($quantity * $unitPrice);
                $totalTax      += $tax;
                $totalDiscount += $discount;

                $purchasePrice = $this->getLatestPurchasePriceInCurrency($product, $warehouseId, $saleCurrencyCode);
                $itemProfit    = ($unitPrice - $purchasePrice) * $quantity;
                $totalProfit  += $itemProfit;
                $ownerProfit  += $itemProfit * ($warehouse->profit_percentage / 100);

                if (!empty($item['imei_numbers'])) {
                    $imeiArray = array_values(array_filter(array_map('trim', explode(',', $item['imei_numbers']))));

                    if (count($imeiArray) != $quantity) {
                        $errorMsg[] = "Produkti '{$product->name}': IMEI duhet të jenë {$quantity}.";
                        continue;
                    }

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

            $sale->update([
                'invoice_date'      => $validated['invoice_date'],
                'delivery_date'     => $validated['delivery_date'],
                'due_date'          => $validated['due_date'],
                'partner_id'        => $validated['partner_id'],
                'seller_id'         => $validated['seller_id'],
                'warehouse_id'      => $validated['warehouse_id'],
                'currency_id'       => $validated['currency_id'],
                'sale_status'       => $validated['sale_status'],
                'payment_status'    => $validated['payment_status'],
                'payment_method'    => $validated['payment_method'],
                'purchase_location' => $validated['purchase_location'],
                'payment_term'      => $validated['payment_term'],
                'subtotal'          => $subtotal,
                'tax'               => $totalTax,
                'discount'          => $totalDiscount,
                'total_amount'      => $totalAmount,
                'profit_total'      => $totalProfit,
                'owner_profit'      => $ownerProfit,
                'description'       => $validated['description'],
                'notes'             => $validated['notes'],
            ]);

            $sale->items()->delete();

            foreach ($request->items as $item) {
                $product   = Product::findOrFail($item['product_id']);
                $quantity  = $item['quantity'];
                $unitPrice = $item['unit_price'];
                $discount  = $item['discount'] ?? 0;
                $tax       = $item['tax'] ?? 0;
                $lineTotal = ($quantity * $unitPrice) - $discount + $tax;

                $purchasePrice   = $this->getLatestPurchasePriceInCurrency($product, $warehouseId, $saleCurrencyCode);
                $itemProfit      = ($unitPrice - $purchasePrice) * $quantity;
                $itemOwnerProfit = $itemProfit * ($warehouse->profit_percentage / 100);

                $imeiArray = null;
                if (!empty($item['imei_numbers'])) {
                    $imeiArray = array_values(array_filter(array_map('trim', explode(',', $item['imei_numbers']))));
                }

                SaleItem::create([
                    'sale_id'        => $sale->id,
                    'product_id'     => $product->id,
                    'product_name'   => $product->name,
                    'warehouse_id'   => $warehouseId,
                    'category_id'    => $product->category_id,
                    'brand_id'       => $product->brand_id,
                    'storage'        => $product->storage,
                    'ram'            => $product->ram,
                    'color'          => $product->color,
                    'quantity'       => $quantity,
                    'unit_type'      => $item['unit_type'] ?? 'Pcs',
                    'unit_price'     => $unitPrice,
                    'purchase_price' => $purchasePrice,
                    'sale_price'     => $unitPrice,
                    'discount'       => $discount,
                    'tax'            => $tax,
                    'line_total'     => $lineTotal,
                    'profit_total'   => $itemProfit,
                    'owner_profit'   => $itemOwnerProfit,
                    'imei_numbers'   => $imeiArray,
                ]);

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
                    'url'     => route('sales.index')
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
        $search      = $request->get('q', '');
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

    /**
     * Search product by IMEI number.
     * Returns full product details so the frontend can pre-fill the product row
     * and automatically populate the IMEI field.
     *
     * Route (add to routes/web.php or routes/api.php):
     *   GET /sales-api/search-by-imei  → [SaleController::class, 'searchByImei']
     */
    public function searchByImei(Request $request)
    {
        $imei        = trim($request->get('imei', ''));
        $warehouseId = $request->get('warehouse_id');

        if (empty($imei)) {
            return response()->json(['error' => 'IMEI mungon'], 422);
        }

        // Basic format check
        if (!preg_match('/^\d{15}$/', $imei)) {
            return response()->json([
                'error' => 'IMEI duhet të jetë 15 shifra numerike'
            ], 422);
        }

        // Find PurchaseItem that contains this IMEI
        $purchaseItem = PurchaseItem::with([
            'product.category',
            'product.brand',
            'product.currency',
            'product.warehouses',
        ])->whereJsonContains('imei_numbers', $imei)->first();

        if (!$purchaseItem) {
            return response()->json([
                'error' => "IMEI {$imei} nuk ekziston në sistem"
            ], 404);
        }

        // Check if this IMEI has already been sold
        $existingSale = SaleItem::with('sale')->whereJsonContains('imei_numbers', $imei)->first();
        if ($existingSale) {
            $invoiceNo = $existingSale->sale->invoice_number ?? 'unknown';
            return response()->json([
                'error' => "IMEI {$imei} është shitur tashmë (Fatura #{$invoiceNo})"
            ], 409);
        }

        $product = $purchaseItem->product;

        if (!$product) {
            return response()->json(['error' => 'Produkti nuk u gjet'], 404);
        }

        // Attach stock quantity
        if ($warehouseId) {
            $product->quantity = $product->getQuantityInWarehouse($warehouseId);
        } else {
            $product->quantity = $product->warehouses->sum('pivot.quantity');
        }

        // Pass found IMEI back so frontend can pre-fill the IMEI textarea
        $product->found_imei = $imei;

        return response()->json($product);
    }

    public function updatePaymentStatus(Request $request, $id)
    {
        $sale      = Sale::with('onlineOrder')->findOrFail($id);
        $newStatus = $request->payment_status;

        $sale->update(['payment_status' => $newStatus]);

        try {
            if ($sale->onlineOrder) {
                if ($newStatus === 'Paid') {
                    $sale->onlineOrder->update([
                        'is_paid'               => true,
                        'payment_received_date' => now(),
                    ]);
                } else {
                    $sale->onlineOrder->update([
                        'is_paid'               => false,
                        'payment_received_date' => null,
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to sync online order payment status for sale ' . $sale->id . ': ' . $e->getMessage());
        }

        return response()->json(['success' => true, 'message' => 'Payment status updated']);
    }

    /**
     * Get the latest purchase price for a product and convert it to the target currency.
     */
    private function getLatestPurchasePriceInCurrency($product, $warehouseId = null, $targetCurrencyCode = null)
    {
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
                    $rates    = $exchange->getExchangeRates();
                    if (isset($rates['data']) && is_array($rates['data'])) {
                        $rates = $rates['data'];
                    }

                    if (isset($rates[$purchaseCurrencyCode]) && $targetCurrencyCode == 'LEK') {
                        $rateFrom = $rates[$purchaseCurrencyCode]['buy'] ?? $rates[$purchaseCurrencyCode]['sell'] ?? null;

                        if ($rateFrom) {
                            return (float) ($rawPrice * $rateFrom);
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

        if (isset($product->purchase_price)) {
            return (float) $product->purchase_price;
        }

        return 0.0;
    }

    public function dailyReport(Request $request)
    {
        $period = $request->get('period', 'today');

        switch ($period) {
            case 'yesterday':
                $dateFrom = Carbon::yesterday()->startOfDay();
                $dateTo   = Carbon::yesterday()->endOfDay();
                break;
            case 'this_week':
                $dateFrom = Carbon::now()->startOfWeek(Carbon::MONDAY);
                $dateTo   = Carbon::now()->endOfDay();
                break;
            case 'last_week':
                $dateFrom = Carbon::now()->subWeek()->startOfWeek(Carbon::MONDAY);
                $dateTo   = Carbon::now()->subWeek()->endOfWeek(Carbon::SUNDAY);
                break;
            case 'this_month':
                $dateFrom = Carbon::now()->startOfMonth();
                $dateTo   = Carbon::now()->endOfDay();
                break;
            case 'last_month':
                $dateFrom = Carbon::now()->subMonth()->startOfMonth();
                $dateTo   = Carbon::now()->subMonth()->endOfMonth();
                break;
            case 'custom':
                $dateFrom = Carbon::parse($request->get('date_from'))->startOfDay();
                $dateTo   = Carbon::parse($request->get('date_to'))->endOfDay();
                break;
            case 'today':
            default:
                $period   = 'today';
                $dateFrom = Carbon::today()->startOfDay();
                $dateTo   = Carbon::today()->endOfDay();
                break;
        }

        $sales = Sale::with([
            'items.product',
            'items.category',
            'warehouse',
            'currency',
            'partner'
        ])
            ->whereBetween('invoice_date', [
                $dateFrom->format('Y-m-d H:i:s'),
                $dateTo->format('Y-m-d H:i:s')
            ])
            ->get();

        $reportData = [];

        foreach ($sales->groupBy('warehouse_id') as $warehouseId => $warehouseSales) {
            $warehouse = $warehouseSales->first()->warehouse;
            if (!$warehouse) continue;

            $byCurrency = [];

            foreach ($warehouseSales as $sale) {
                $currencyCode   = $sale->currency->code   ?? 'ALL';
                $currencySymbol = $sale->currency->symbol ?? 'L';

                if (!isset($byCurrency[$currencyCode])) {
                    $byCurrency[$currencyCode] = [
                        'symbol'       => $currencySymbol,
                        'xhiro'        => 0,
                        'fitimi_total' => 0,
                        'fitimi_juaj'  => 0,
                        'cash'         => 0,
                        'bank'         => 0,
                    ];
                }

                $byCurrency[$currencyCode]['xhiro'] += $sale->total_amount;

                foreach ($sale->items as $item) {
                    $profit = ($item->unit_price - $item->purchase_price) * $item->quantity;
                    $byCurrency[$currencyCode]['fitimi_total'] += $profit;
                }

                if (strtolower($sale->payment_method ?? '') === 'cash') {
                    $byCurrency[$currencyCode]['cash'] += $sale->total_amount;
                } else {
                    $byCurrency[$currencyCode]['bank'] += $sale->total_amount;
                }
            }

            $profitPercentage = $warehouse->profit_percentage ?? 100;

            foreach ($byCurrency as $code => &$c) {
                $c['fitimi_juaj']  = $c['fitimi_total'] * ($profitPercentage / 100);
                $c['xhiro']        = number_format($c['xhiro'], 2);
                $c['fitimi_total'] = number_format($c['fitimi_total'], 2);
                $c['fitimi_juaj']  = number_format($c['fitimi_juaj'], 2);
                $c['cash']         = number_format($c['cash'], 2);
                $c['bank']         = number_format($c['bank'], 2);
            }

            $reportData[] = [
                'dyqani'            => $warehouse->name,
                'lokacioni'         => $warehouse->location ?? 'N/A',
                'perqindja_fitimit' => $profitPercentage . '%',
                'shitje_count'      => $warehouseSales->count(),
                'by_currency'       => $byCurrency,
            ];
        }

        $report = collect($reportData);

        $totalsByCurrency = [];

        foreach ($sales as $sale) {
            $currencyCode   = $sale->currency->code   ?? 'ALL';
            $currencySymbol = $sale->currency->symbol ?? 'L';

            if (!isset($totalsByCurrency[$currencyCode])) {
                $totalsByCurrency[$currencyCode] = [
                    'symbol'       => $currencySymbol,
                    'xhiro'        => 0,
                    'fitimi_total' => 0,
                    'fitimi_juaj'  => 0,
                ];
            }

            $totalsByCurrency[$currencyCode]['xhiro'] += $sale->total_amount;
            $profitPercentage = $sale->warehouse->profit_percentage ?? 100;

            foreach ($sale->items as $item) {
                $profit = ($item->unit_price - $item->purchase_price) * $item->quantity;
                $totalsByCurrency[$currencyCode]['fitimi_total'] += $profit;
                $totalsByCurrency[$currencyCode]['fitimi_juaj']  += $profit * ($profitPercentage / 100);
            }
        }

        foreach ($totalsByCurrency as $code => &$c) {
            $c['xhiro']        = number_format($c['xhiro'], 2);
            $c['fitimi_total'] = number_format($c['fitimi_total'], 2);
            $c['fitimi_juaj']  = number_format($c['fitimi_juaj'], 2);
        }

        $totals = [
            'by_currency'   => $totalsByCurrency,
            'shitje_totale' => $sales->count(),
        ];

        return view('sales.daily-report', compact(
            'report',
            'totals',
            'period',
            'dateFrom',
            'dateTo'
        ));
    }
}
