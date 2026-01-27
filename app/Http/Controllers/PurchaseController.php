<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Product;
use App\Models\Partner;
use App\Models\Warehouse;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PurchaseController extends Controller
{
    public function index()
    {
        $purchases = Purchase::with(['partner', 'warehouse', 'currency'])
            ->latest()
            ->paginate(15);

        return view('purchases.index', compact('purchases'));
    }

    public function create()
    {
        $partners = Partner::all();
        $warehouses = Warehouse::all();
        $currencies = Currency::all();

        $purchaseNumber = Purchase::generatePurchaseNumber();

        return view('purchases.create', compact('partners', 'warehouses', 'currencies', 'purchaseNumber'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_date' => 'required|date',
            'due_date' => 'nullable|date',
            'partner_id' => 'required|exists:partners,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'currency_id' => 'required|exists:currencies,id',
            'order_status' => 'required|in:Received,Pending,Cancelled',
            'payment_status' => 'required|in:Paid,Unpaid,Partial',
            'payment_method' => 'required|in:Cash,Bank',
            'notes' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.tax' => 'nullable|numeric|min:0',
            'items.*.imei_numbers' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Handle file upload
            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $attachmentPath = $request->file('attachment')->store('purchases', 'public');
            }

            // Calculate totals
            $subtotal = 0;
            $totalTax = 0;
            $totalDiscount = 0;

            $allImeiNumbers = [];
            $errorMsg = [];

            // First pass: validate all items and calculate totals
            foreach ($request->items as $itemIndex => $item) {
                $quantity = $item['quantity'];
                $unitCost = $item['unit_cost'];
                $discount = $item['discount'] ?? 0;
                $tax = $item['tax'] ?? 0;

                $lineTotal = ($quantity * $unitCost) - $discount + $tax;
                $subtotal += ($quantity * $unitCost);
                $totalTax += $tax;
                $totalDiscount += $discount;

                // IMEI validation only if provided
                if (!empty($item['imei_numbers'])) {
                    $imeiArray = array_values(array_filter(array_map('trim', explode(',', $item['imei_numbers']))));
                    $imeiCount = count($imeiArray);

                    // Validate count matches quantity
                    if ($imeiCount != $quantity) {
                        $errorMsg[] = "Produkti #" . ($itemIndex + 1) . ": Numri i IMEI duhet të jetë i barabartë me sasinë ({$quantity}). Ju keni vendosur {$imeiCount} IMEI.";
                        continue;
                    }

                    // Check for duplicate IMEI within this item
                    $uniqueImei = array_unique($imeiArray);
                    if (count($uniqueImei) != count($imeiArray)) {
                        $errorMsg[] = "Produkti #" . ($itemIndex + 1) . ": Ka IMEI të dubluar. Çdo IMEI duhet të jetë unik.";
                        continue;
                    }

                    // Validate IMEI format (15 digits)
                    foreach ($imeiArray as $imei) {
                        if (!preg_match('/^\d{15}$/', $imei)) {
                            $errorMsg[] = "Produkti #" . ($itemIndex + 1) . ": IMEI '{$imei}' nuk është valid. IMEI duhet të jetë 15 shifra.";
                            break;
                        }
                    }

                    // Check if IMEI already exists in database
                    foreach ($imeiArray as $imei) {
                        $existingImei = PurchaseItem::whereJsonContains('imei_numbers', $imei)->first();
                        if ($existingImei) {
                            $errorMsg[] = "IMEI {$imei} ekziston tashmë në sistem (Purchase #{$existingImei->purchase->purchase_number}).";
                            break;
                        }

                        if (in_array($imei, $allImeiNumbers)) {
                            $errorMsg[] = "IMEI {$imei} është përdorur më shumë se një herë në këtë blerje.";
                            break;
                        }

                        $allImeiNumbers[] = $imei;
                    }
                }
            }

            // If there are validation errors, return them
            if (!empty($errorMsg)) {
                DB::rollBack();
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => $errorMsg], 422);
                } else {
                    return redirect()->back()
                        ->with('error', implode('<br>', $errorMsg))
                        ->withInput();
                }
            }

            $totalAmount = $subtotal - $totalDiscount + $totalTax;

            // Create Purchase
            $purchase = Purchase::create([
                'purchase_number' => Purchase::generatePurchaseNumber(),
                'purchase_date' => $validated['purchase_date'],
                'due_date' => $validated['due_date'],
                'partner_id' => $validated['partner_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'currency_id' => $validated['currency_id'],
                'order_status' => $validated['order_status'],
                'payment_status' => $validated['payment_status'],
                'payment_method' => $validated['payment_method'],
                'subtotal' => $subtotal,
                'tax' => $totalTax,
                'discount' => $totalDiscount,
                'total_amount' => $totalAmount,
                'notes' => $validated['notes'],
                'attachment' => $attachmentPath,
            ]);

            // Create Purchase Items and Update Product Quantities
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);

                $quantity = $item['quantity'];
                $unitCost = $item['unit_cost'];
                $discount = $item['discount'] ?? 0;
                $tax = $item['tax'] ?? 0;
                $lineTotal = ($quantity * $unitCost) - $discount + $tax;

                // Process IMEI if provided
                $imeiArray = null;
                if (!empty($item['imei_numbers'])) {
                    $imeiArray = array_values(array_filter(array_map('trim', explode(',', $item['imei_numbers']))));
                }

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'storage' => $product->storage,
                    'ram' => $product->ram,
                    'color' => $product->color,
                    'quantity' => $quantity,
                    'unit_type' => $item['unit_type'] ?? 'Pcs',
                    'unit_cost' => $unitCost,
                    'discount' => $discount,
                    'tax' => $tax,
                    'line_total' => $lineTotal,
                    'imei_numbers' => $imeiArray
                ]);

                // Update product quantity if order is received
                if ($validated['order_status'] === 'Received') {
                    $product->increment('quantity', $quantity);
                }
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Purchase created successfully',
                    'url' => route('purchases.index')
                ], 200);
            } else {
                return redirect()->route('purchases.index')
                    ->with('success', 'Blerja u krijua me sukses!');
            }
        } catch (\Exception $e) {
            DB::rollBack();

            // Delete uploaded file if exists
            if (isset($attachmentPath) && $attachmentPath) {
                Storage::disk('public')->delete($attachmentPath);
            }

            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return redirect()->back()
                ->with('error', 'Ka ndodhur një gabim: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $purchase = Purchase::with(['partner', 'warehouse', 'currency', 'items.product'])
            ->findOrFail($id);

        return view('purchases.show', compact('purchase'));
    }

    public function edit($id)
    {
        $purchase = Purchase::with('items')->findOrFail($id);
        $partners = Partner::all();
        $warehouses = Warehouse::all();
        $currencies = Currency::all();
        $products = Product::with(['category', 'brand'])->get();

        return view('purchases.edit', compact('purchase', 'partners', 'warehouses', 'currencies', 'products'));
    }

    public function update(Request $request, $id)
    {
        $purchase = Purchase::findOrFail($id);

        $validated = $request->validate([
            'purchase_date' => 'required|date',
            'due_date' => 'nullable|date',
            'partner_id' => 'required|exists:partners,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'currency_id' => 'required|exists:currencies,id',
            'order_status' => 'required|in:Received,Pending,Cancelled',
            'payment_status' => 'required|in:Paid,Unpaid,Partial',
            'payment_method' => 'required|in:Cash,Bank',
            'notes' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.tax' => 'nullable|numeric|min:0',
            'items.*.imei_numbers' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Reverse old quantities if order was received
            if ($purchase->order_status === 'Received') {
                foreach ($purchase->items as $item) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->decrement('quantity', $item->quantity);
                    }
                }
            }

            $allImeiNumbers = [];
            $errorMsg = [];

            // Validate IMEI for items that have them
            foreach ($request->items as $itemIndex => $item) {
                if (!empty($item['imei_numbers'])) {
                    $imeiArray = array_values(array_filter(array_map('trim', explode(',', $item['imei_numbers']))));
                    $imeiCount = count($imeiArray);
                    $quantity = $item['quantity'];

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
                            $errorMsg[] = "Produkti #" . ($itemIndex + 1) . ": IMEI '{$imei}' nuk është valid.";
                            break;
                        }
                    }

                    // Check existing IMEI (excluding current purchase)
                    $existingImeis = PurchaseItem::where('purchase_id', '!=', $purchase->id)
                        ->get()
                        ->pluck('imei_numbers')
                        ->flatten()
                        ->filter()
                        ->toArray();

                    $conflictingImeis = array_intersect($imeiArray, $existingImeis);

                    if (!empty($conflictingImeis)) {
                        $errorMsg[] = 'IMEI ' . reset($conflictingImeis) . ' ekziston tashmë në sistem.';
                        break;
                    }

                    $allImeiNumbers = array_merge($allImeiNumbers, $imeiArray);
                }
            }

            if (!empty($errorMsg)) {
                DB::rollBack();
                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => $errorMsg], 422);
                } else {
                    return redirect()->back()
                        ->with('error', implode('<br>', $errorMsg))
                        ->withInput();
                }
            }

            // Handle file upload
            if ($request->hasFile('attachment')) {
                if ($purchase->attachment) {
                    Storage::disk('public')->delete($purchase->attachment);
                }
                $attachmentPath = $request->file('attachment')->store('purchases', 'public');
            } else {
                $attachmentPath = $purchase->attachment;
            }

            // Calculate totals
            $subtotal = 0;
            $totalTax = 0;
            $totalDiscount = 0;

            foreach ($request->items as $item) {
                $quantity = $item['quantity'];
                $unitCost = $item['unit_cost'];
                $discount = $item['discount'] ?? 0;
                $tax = $item['tax'] ?? 0;

                $subtotal += ($quantity * $unitCost);
                $totalTax += $tax;
                $totalDiscount += $discount;
            }

            $totalAmount = $subtotal - $totalDiscount + $totalTax;

            // Update Purchase
            $purchase->update([
                'purchase_date' => $validated['purchase_date'],
                'due_date' => $validated['due_date'],
                'partner_id' => $validated['partner_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'currency_id' => $validated['currency_id'],
                'order_status' => $validated['order_status'],
                'payment_status' => $validated['payment_status'],
                'payment_method' => $validated['payment_method'],
                'subtotal' => $subtotal,
                'tax' => $totalTax,
                'discount' => $totalDiscount,
                'total_amount' => $totalAmount,
                'notes' => $validated['notes'],
                'attachment' => $attachmentPath,
            ]);

            // Delete old items
            $purchase->items()->delete();

            // Create new items and update quantities
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);

                $quantity = $item['quantity'];
                $unitCost = $item['unit_cost'];
                $discount = $item['discount'] ?? 0;
                $tax = $item['tax'] ?? 0;
                $lineTotal = ($quantity * $unitCost) - $discount + $tax;

                // Process IMEI if provided
                $imeiArray = null;
                if (!empty($item['imei_numbers'])) {
                    $imeiArray = array_values(array_filter(array_map('trim', explode(',', $item['imei_numbers']))));
                }

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'storage' => $product->storage,
                    'ram' => $product->ram,
                    'color' => $product->color,
                    'quantity' => $quantity,
                    'unit_type' => $item['unit_type'] ?? 'Pcs',
                    'unit_cost' => $unitCost,
                    'discount' => $discount,
                    'tax' => $tax,
                    'line_total' => $lineTotal,
                    'imei_numbers' => $imeiArray
                ]);

                // Update product quantity if order is received
                if ($validated['order_status'] === 'Received') {
                    $product->increment('quantity', $quantity);
                }
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Purchase updated successfully',
                    'url' => route('purchases.index')
                ], 200);
            } else {
                return redirect()->route('purchases.index')
                    ->with('success', 'Blerja u përditësua me sukses!');
            }
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return redirect()->back()
                ->with('error', 'Ka ndodhur një gabim: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $purchase = Purchase::findOrFail($id);

            // Reverse quantities if order was received
            if ($purchase->order_status === 'Received') {
                foreach ($purchase->items as $item) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->decrement('quantity', $item->quantity);
                    }
                }
            }

            // Delete attachment file
            if ($purchase->attachment) {
                Storage::disk('public')->delete($purchase->attachment);
            }

            $purchase->delete();

            DB::commit();

            return redirect()->route('purchases.index')
                ->with('success', 'Blerja u fshi me sukses!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('purchases.index')
                ->with('error', 'Ka ndodhur një gabim: ' . $e->getMessage());
        }
    }

    // API Methods
    public function searchProducts(Request $request)
    {
        $search = $request->get('q', '');

        $products = Product::with(['category', 'brand', 'currency'])
            ->where('name', 'like', "%{$search}%")
            ->orWhere('storage', 'like', "%{$search}%")
            ->orWhere('color', 'like', "%{$search}%")
            ->limit(10)
            ->get();


        return response()->json($products);
    }
}
