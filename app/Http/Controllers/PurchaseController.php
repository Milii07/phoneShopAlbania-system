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
use Smalot\PdfParser\Parser as PdfParser;
use App\Models\Category;
use App\Models\Brand;
use thiagoalessio\TesseractOCR\TesseractOCR;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $query = Purchase::with(['partner', 'warehouse', 'currency'])
            ->latest();

        if ($request->has('warehouse_id') && $request->warehouse_id) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        $purchases = $query->paginate(15);

        $warehouses = Warehouse::orderBy('name')->get();

        return view('purchases.index', compact('purchases', 'warehouses'));
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

        $this->createImportedEntities($request);

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

            $warehouseId = $validated['warehouse_id'];

            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $attachmentPath = $request->file('attachment')->store('purchases', 'public');
            }

            $subtotal = 0;
            $totalTax = 0;
            $totalDiscount = 0;

            $allImeiNumbers = [];
            $errorMsg = [];

            // Validate IMEI and calculate totals
            foreach ($request->items as $itemIndex => $item) {
                $product = Product::find($item['product_id']);

                $quantity = $item['quantity'];
                $unitCost = $item['unit_cost'];
                $discount = $item['discount'] ?? 0;
                $tax = $item['tax'] ?? 0;

                $lineTotal = ($quantity * $unitCost) - $discount + $tax;
                $subtotal += ($quantity * $unitCost);
                $totalTax += $tax;
                $totalDiscount += $discount;

                if (!empty($item['imei_numbers'])) {
                    $imeiArray = array_values(array_filter(array_map('trim', explode(',', $item['imei_numbers']))));
                    $imeiCount = count($imeiArray);

                    if ($imeiCount != $quantity) {
                        $errorMsg[] = "Produkti #" . ($itemIndex + 1) . ": Numri i IMEI duhet të jetë i barabartë me sasinë ({$quantity}). Ju keni vendosur {$imeiCount} IMEI.";
                        continue;
                    }

                    $uniqueImei = array_unique($imeiArray);
                    if (count($uniqueImei) != count($imeiArray)) {
                        $errorMsg[] = "Produkti #" . ($itemIndex + 1) . ": Ka IMEI të dubluar. Çdo IMEI duhet të jetë unik.";
                        continue;
                    }

                    foreach ($imeiArray as $imei) {
                        if (!preg_match('/^\d{15}$/', $imei)) {
                            $errorMsg[] = "Produkti #" . ($itemIndex + 1) . ": IMEI '{$imei}' nuk është valid. IMEI duhet të jetë 15 shifra.";
                            break;
                        }
                    }

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

            // Create Purchase Items and Update Stock
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);

                $quantity = $item['quantity'];
                $unitCost = $item['unit_cost'];
                $discount = $item['discount'] ?? 0;
                $tax = $item['tax'] ?? 0;
                $lineTotal = ($quantity * $unitCost) - $discount + $tax;

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
                    'imei_numbers' => $this->parseImeiInput($item['imei_numbers'] ?? null)
                ]);

                // Update product quantity if order is received
                if ($validated['order_status'] === 'Received') {
                    // Use helper method to add stock to warehouse
                    $product->addStock($warehouseId, $quantity);
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


        $this->createImportedEntities($request);


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


            $oldWarehouseId = $purchase->warehouse_id;
            $newWarehouseId = $validated['warehouse_id'];
            $oldStatus = $purchase->order_status;
            $newStatus = $validated['order_status'];

            // Reverse old quantities if order was received
            if ($purchase->order_status === 'Received') {
                foreach ($purchase->items as $item) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        // Use helper method to reduce stock
                        $product->reduceStock($oldWarehouseId, $item->quantity);
                    }
                }
            }

            // IMEI Validation
            $allImeiNumbers = [];
            $errorMsg = [];

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

            // Handle attachment
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

            // Create new items and add stock if Received
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);

                $quantity = $item['quantity'];
                $unitCost = $item['unit_cost'];
                $discount = $item['discount'] ?? 0;
                $tax = $item['tax'] ?? 0;
                $lineTotal = ($quantity * $unitCost) - $discount + $tax;

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
                    'imei_numbers' =>  $this->parseImeiInput($item['imei_numbers'] ?? null)
                ]);

                // Update product quantity if order is received
                if ($validated['order_status'] === 'Received') {
                    // Use helper method to add stock
                    $product->addStock($newWarehouseId, $quantity);
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
            $warehouseId = $purchase->warehouse_id;

            // Reverse quantities if order was received
            if ($purchase->order_status === 'Received') {
                foreach ($purchase->items as $item) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        // Use helper method to reduce stock
                        $product->reduceStock($warehouseId, $item->quantity);
                    }
                }
            }

            // Delete attachment
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

    public function searchProducts(Request $request)
    {
        $search = $request->get('q', '');
        $warehouseId = $request->get('warehouse_id');

        $query = Product::with(['category', 'brand', 'currency'])
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('storage', 'like', "%{$search}%")
                    ->orWhere('color', 'like', "%{$search}%");
            });

        $products = $query->limit(10)->get();

        $products = $products->map(function ($product) use ($warehouseId) {
            if ($warehouseId) {
                $product->current_warehouse_quantity = $product->getQuantityInWarehouse($warehouseId);
            }
            $product->total_quantity = $product->total_quantity;
            return $product;
        });

        return response()->json($products);
    }

    public function extractFromDocument(Request $request)
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf|max:10240',
        ]);

        try {
            $parser = new PdfParser();
            $pdf    = $parser->parseFile($request->file('document')->getRealPath());
            $text   = $pdf->getText();

            $data = $this->parseInvoiceText($text);

            // Gjej Partner
            $partner = null;
            if (!empty($data['supplier']['nipt'])) {
                $partner = Partner::where('nipt', $data['supplier']['nipt'])->first();
            }
            if (!$partner && !empty($data['supplier']['name'])) {
                $name    = explode(' - ', $data['supplier']['name'])[0];
                $partner = Partner::where('name', 'like', "%{$name}%")->first();
            }

            $resolvedItems = [];
            foreach ($data['items'] as $item) {
                $parsed  = $item['_parsed'];   // të dhënat e parsimit
                $product = $this->findProduct($parsed);

                $resolvedItem                 = $item;
                $resolvedItem['product_id']   = $product?->id;
                $resolvedItem['product_found'] = (bool) $product;
                unset($resolvedItem['_parsed']); // mos e dërgo te frontend
                $resolvedItems[] = $resolvedItem;
            }
            $data['items'] = $resolvedItems;

            return response()->json([
                'success' => true,
                'data'    => $data,
                'partner' => $partner ? ['id' => $partner->id, 'name' => $partner->name] : null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gabim gjatë leximit: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function findProduct(array $parsed): ?Product
    {
        $q = Product::query();

        // Kërko me emrin bazë
        if (!empty($parsed['clean_name'])) {
            $q->where('name', 'like', '%' . $parsed['clean_name'] . '%');
        }
        // Filtro me storage nëse ekziston
        if (!empty($parsed['storage'])) {
            $q->where('storage', $parsed['storage']);
        }
        // Filtro me color nëse ekziston
        if (!empty($parsed['color'])) {
            $q->where('color', $parsed['color']);
        }

        return $q->first();
    }

    private function parseProductName(string $rawName): array
    {
        $name   = trim($rawName);
        $words  = explode(' ', $name);
        $result = [
            'brand'      => '',
            'color'      => '',
            'storage'    => '',
            'ram'        => '',
            'clean_name' => $name,
        ];

        // 1. BRAND — fjala e parë
        if (!empty($words[0])) {
            $result['brand'] = $words[0];
        }

        // 2. COLOR — lista e ngjyrave të zakonshme
        $colors = [
            'black',
            'white',
            'blue',
            'red',
            'green',
            'gold',
            'silver',
            'purple',
            'pink',
            'yellow',
            'gray',
            'grey',
            'midnight',
            'starlight',
            'graphite',
            'titanium',
            'natural',
            'pacific',
            'alpine',
            'sierra',
            'space',
            'zi',
            'bardhe',
            'blu',
            'kuqe',
            'jeshile',
            'ari',
            'argjend',
            'vjollce',
        ];
        foreach ($words as $w) {
            if (in_array(mb_strtolower($w), $colors)) {
                $result['color'] = ucfirst(mb_strtolower($w));
                break;
            }
        }

        // 3. Gjej të gjithë numrat GB
        preg_match_all('/\b(\d+)\s*GB\b/i', $name, $gbMatches);
        $gbNums = array_map('intval', $gbMatches[1]);

        // Numra pa GB — storage tipik (32/64/128/256/512)
        $storagePlain = null;
        if (preg_match('/\b(32|64|128|256|512)\b(?!\s*GB)/i', $name, $m)) {
            $storagePlain = (int) $m[1];
        }

        if (!empty($gbNums)) {
            // I madhi = storage, i vogli = RAM
            rsort($gbNums);
            $result['storage'] = $gbNums[0] . 'GB';
            if (isset($gbNums[1])) {
                $result['ram'] = $gbNums[1] . 'GB';
            }
        } elseif ($storagePlain !== null) {
            $result['storage'] = $storagePlain . 'GB';
        }

        // 4. CLEAN NAME — hiq storage, ram, color nga emri
        $clean = $name;
        if ($result['color']) {
            $clean = preg_replace('/\b' . preg_quote($result['color'], '/') . '\b/iu', '', $clean);
        }
        if (!empty($gbNums)) {
            foreach ($gbNums as $n) {
                $clean = preg_replace('/\b' . $n . '\s*GB\b/i', '', $clean);
            }
        }
        if ($storagePlain !== null && empty($gbNums)) {
            $clean = preg_replace('/\b' . $storagePlain . '\b(?!\s*GB)/i', '', $clean);
        }
        // Hiq "d" suffix nga fatura
        $clean = preg_replace('/\s+\bd\b\s*$/u', '', $clean);
        $clean = preg_replace('/\s+/', ' ', $clean);
        $result['clean_name'] = trim($clean);

        return $result;
    }

    private function parseImeiInput(mixed $value): ?array
    {
        if (empty($value)) return null;
        if (is_array($value)) {
            $arr = array_values(array_filter(array_map('trim', $value)));
            return empty($arr) ? null : $arr;
        }
        $arr = array_values(array_filter(array_map('trim', explode(',', (string) $value))));
        return empty($arr) ? null : $arr;
    }

    // ──────────────────────────────────────────────────────────────
    // PARSER KRYESOR
    // ────────────────────────────────────────────────────────────── 
    private function parseInvoiceText(string $rawText): array
    {
        $lines    = array_values(array_filter(array_map('trim', explode("\n", $rawText)), fn($l) => $l !== ''));
        $fullText = implode("\n", $lines);

        $result = [
            'supplier' => ['name' => '', 'nipt' => '', 'address' => ''],
            'invoice'  => ['number' => '', 'date' => '', 'payment_method' => 'Cash'],
            'items'    => [],
            'totals'   => ['subtotal' => 0, 'tax' => 0, 'total' => 0],
        ];

        // ── Supplier ──────────────────────────────────────────────
        $supplierLines = [];
        foreach ($lines as $line) {
            if (stripos($line, 'FATUR') !== false) break;
            $supplierLines[] = $line;
        }
        if (!empty($supplierLines)) {
            $result['supplier']['name'] = $supplierLines[0];
            if (isset($supplierLines[1]) && !preg_match('/^(NIPT|Adresa|Tel)/i', $supplierLines[1])) {
                $result['supplier']['name'] .= ' - ' . $supplierLines[1];
            }
        }
        foreach ($supplierLines as $line) {
            if (preg_match('/NIPT[:\s]*([A-Z0-9]+)/i', $line, $m)) $result['supplier']['nipt']    = trim($m[1]);
            if (preg_match('/Adresa[:\s]*(.+)/i',       $line, $m)) $result['supplier']['address'] = trim($m[1]);
        }

        // ── Invoice ───────────────────────────────────────────────
        if (preg_match('/Data[:\s]*(\d{2})-(\d{2})-(\d{4})/i', $fullText, $m))
            $result['invoice']['date'] = "{$m[3]}-{$m[2]}-{$m[1]}";
        if (preg_match('/Numri[:\s]*(\S+)/i', $fullText, $m))
            $result['invoice']['number'] = trim($m[1]);
        if (preg_match('/M[eë]nyra e pag[eë]s[eë]s[:\s]*(.+)/iu', $fullText, $m)) {
            $p = mb_strtolower(trim($m[1]));
            $result['invoice']['payment_method'] = (str_contains($p, 'bank') || str_contains($p, 'transfert')) ? 'Bank' : 'Cash';
        }

        // ── IMEI nga Shënime ──────────────────────────────────────
        $allImei = [];
        $shenimeText = '';
        $inShenime = false;

        foreach ($lines as $line) {
            if (preg_match('/Sh[eë]nime[:\s]*(.*)/iu', $line, $m)) {
                $inShenime = true;
                $shenimeText = $m[1] ?? '';
                preg_match_all('/\b\d{15}\b/', $m[1] ?? '', $sameLine);
                if (!empty($sameLine[0])) $allImei = array_merge($allImei, $sameLine[0]);
                continue;
            }

            if ($inShenime) {
                if (preg_match('/Artikulli|Vlefta\s+me|Kodi\s+Nj/iu', $line)) break;
                $shenimeText .= ' ' . $line;
            } else {
                preg_match_all('/\b\d{15}\b/', $line, $matches);
                if (!empty($matches[0])) $allImei = array_merge($allImei, $matches[0]);
            }
        }

        if ($shenimeText !== '') {
            $cleaned = preg_replace('/(\d)\.(\s|$)/', '$1 ', $shenimeText);
            preg_match_all('/\b\d{15}\b/', $cleaned, $imeiMatches);
            if (!empty($imeiMatches[0])) $allImei = array_merge($allImei, $imeiMatches[0]);
        }

        $allImei = array_values(array_unique($allImei));

        // ── Produktet ─────────────────────────────────────────────
        $tableStart = false;
        $tableLines = [];
        foreach ($lines as $line) {
            if (preg_match('/Artikulli.*Sh[eë]rbimi/iu', $line)) {
                $tableStart = true;
                continue;
            }
            if ($tableStart) {
                if (preg_match('/Nivelet e TVSH|Total pa TVSH|NSLF|NIVF/iu', $line)) break;
                $tableLines[] = $line;
            }
        }

        $rawItems = [];
        $currentNameParts = [];
        foreach ($tableLines as $line) {
            if (preg_match('/(\d{3})\s+cope\s+([\d,\.]+)\s+([\d,\.]+)\s+([\d,\.]+)\s+([\d,\.]+)\s+([\d,\.]+)/i', $line, $m)) {
                $namePart = trim(substr($line, 0, strpos($line, $m[0])));
                $namePart = trim(preg_replace('/\b(tvsh|vlefta|me|pa)\b/iu', '', $namePart));
                if ($namePart !== '') $currentNameParts[] = $namePart;

                $name = implode(' ', $currentNameParts);
                $name = trim(preg_replace('/\s+\bd\b\s*$/u', '', $name));
                $name = trim(preg_replace('/\s*(grande|piccolo|medio)\s*$/iu', '', $name));
                $name = preg_replace('/\s+/', ' ', $name);

                $rawItems[] = [
                    'product_name' => $name,
                    'kodi'         => $m[1],
                    'quantity'     => (float) str_replace(',', '', $m[2]),
                    'unit_cost'    => (float) str_replace(',', '', $m[3]),
                    'tax'          => (float) str_replace(',', '', $m[5]),
                    'line_total'   => (float) str_replace(',', '', $m[6]),
                    'discount'     => 0,
                ];
                $currentNameParts = [];
            } else {
                $clean = trim(preg_replace('/\b(tvsh|vlefta|me|pa|grande|piccolo|medio)\b/iu', '', $line));
                if ($clean !== '') $currentNameParts[] = $clean;
            }
        }

        // ── Cakto IMEI dhe parse emrin ────────────────────────────
        $imeiIdx    = 0;
        $finalItems = [];
        foreach ($rawItems as $item) {
            $qty   = (int) $item['quantity'];
            $slice = array_slice($allImei, $imeiIdx, $qty);
            $imeiIdx += $qty;

            // Parse emrin: nxjerr brand, storage, ram, color, clean_name
            $parsed = $this->parseProductName($item['product_name']);

            // Category bazuar te IMEI
            $parsed['category'] = count($slice) > 0 ? 'Telefona' : 'Aksesore';

            $finalItems[] = [
                'product_name' => $item['product_name'],  // emri i plotë nga fatura
                'clean_name'   => $parsed['clean_name'],  // emri pa storage/color/ram
                'brand'        => $parsed['brand'],
                'category'     => $parsed['category'],
                'storage'      => $parsed['storage'],
                'ram'          => $parsed['ram'],
                'color'        => $parsed['color'],
                'kodi'         => $item['kodi'],
                'quantity'     => $item['quantity'],
                'unit_cost'    => $item['unit_cost'],
                'tax'          => $item['tax'],
                'line_total'   => $item['line_total'],
                'discount'     => $item['discount'],
                'imei_numbers' => $slice,
                '_parsed'      => $parsed,  // për findProduct() — hiqet para JSON response
            ];
        }
        $result['items'] = $finalItems;

        // ── Totalet ───────────────────────────────────────────────
        if (preg_match('/Total pa TVSH\s+([\d,\.]+)/iu', $fullText, $m)) $result['totals']['subtotal'] = (float) str_replace(',', '', $m[1]);
        if (preg_match('/Total me TVSH\s+([\d,\.]+)/iu', $fullText, $m)) $result['totals']['total']    = (float) str_replace(',', '', $m[1]);
        if (preg_match('/^TVSH\s+([\d,\.]+)\s+LEK/ium',  $fullText, $m)) $result['totals']['tax']      = (float) str_replace(',', '', $m[1]);

        return $result;
    }


    private function createImportedEntities(Request $request): void
    {
        // Supplier i ri
        if (empty($request->partner_id) && $request->filled('new_supplier_name')) {
            $partner = Partner::firstOrCreate(
                ['nipt' => $request->new_supplier_nipt ?: null],
                [
                    'name'    => $request->new_supplier_name,
                    'address' => $request->new_supplier_address ?? '',
                    'type'    => 'supplier',
                ]
            );
            $request->merge(['partner_id' => $partner->id]);
        }

        // Produkte të reja
        if ($request->has('items')) {
            $items = $request->input('items');
            foreach ($items as &$item) {
                if (!empty($item['product_id']) || empty($item['new_product_name'])) {
                    continue;
                }

                // Lexo të dhënat e parsimit nga hidden inputs
                $cleanName    = $item['new_clean_name']   ?? $item['new_product_name'];
                $brandName    = $item['new_brand']         ?? '';
                $categoryName = $item['new_category']      ?? '';
                $storage      = $item['new_storage']       ?? null;
                $ram          = $item['new_ram']           ?? null;
                $color        = $item['new_color']         ?? null;
                $hasImei      = !empty($item['imei_numbers']);

                // Category — nëse nuk ka nga hidden input, vendos nga IMEI
                if (empty($categoryName)) {
                    $categoryName = $hasImei ? 'Telefona' : 'Aksesore';
                }
                $category = Category::firstOrCreate(['name' => $categoryName]);

                // Brand
                $brand = null;
                if (!empty($brandName)) {
                    $brand = Brand::firstOrCreate(['name' => $brandName]);
                }

                // Product — kërko fillimisht nëse ekziston me këto atribute
                $product = Product::where('name', $cleanName)
                    ->when($brand,   fn($q) => $q->where('brand_id', $brand->id))
                    ->when($storage, fn($q) => $q->where('storage',  $storage))
                    ->when($color,   fn($q) => $q->where('color',    $color))
                    ->first();

                if (!$product) {
                    $product = Product::create([
                        'name'        => $cleanName,
                        'brand_id'    => $brand?->id,
                        'category_id' => $category->id,
                        'storage'     => $storage  ?: null,
                        'ram'         => $ram      ?: null,
                        'color'       => $color    ?: null,
                        'unit_price'       => $item['unit_cost'] ?? 0,
                    ]);
                }

                $item['product_id'] = $product->id;
            }
            unset($item);
            $request->merge(['items' => $items]);
        }
    }

    private function normalizeImei(string $raw): string
    {
        $digits = preg_replace('/\D/', '', $raw);
        $len    = strlen($digits);
        if ($len === 15) return $digits;
        if ($len === 16) return substr($digits, 0, 15); // hiq shifren shtesë
        return $digits; // 14 = OCR humbi 1, kthe si është
    }

    private function parseFormat2(string $rawText): array
    {
        $lines    = array_values(array_filter(array_map('trim', explode("\n", $rawText)), fn($l) => $l !== ''));
        $fullText = implode("\n", $lines);

        $result = [
            'supplier' => ['name' => '', 'nipt' => '', 'address' => ''],
            'invoice'  => ['number' => '', 'date' => '', 'payment_method' => 'Cash'],
            'items'    => [],
            'totals'   => ['subtotal' => 0, 'tax' => 0, 'total' => 0],
        ];

        // ── Supplier ──────────────────────────────────────────────
        foreach ($lines as $line) {
            if (preg_match('/EMRI\s+[I|]+\s+SHITESIT\s+(.*)/iu', $line, $m)) {
                $name = preg_replace('/\s*Data\s*:.*/i', '', $m[1]);
                $name = preg_replace('/Authorised\s+ReSeller/i', '', $name);
                $result['supplier']['name'] = trim($name) ?: 'Shitësi';
                break;
            }
        }
        foreach ($lines as $line) {
            if (preg_match('/^NIPT\s*[:\|]\s*([A-Z0-9]{5,})/i', $line, $m)) {
                $result['supplier']['nipt'] = trim($m[1]);
                break;
            }
        }
        foreach ($lines as $line) {
            if (preg_match('/^Adresa\s*:\s*(.+)/i', $line, $m) && !empty(trim($m[1]))) {
                $result['supplier']['address'] = trim($m[1]);
                break;
            }
        }

        // ── Invoice ───────────────────────────────────────────────
        if (preg_match('/Data\s*:\s*(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/i', $fullText, $m)) {
            $result['invoice']['date'] = "{$m[3]}-" . str_pad($m[2], 2, '0', STR_PAD_LEFT) . '-' . str_pad($m[1], 2, '0', STR_PAD_LEFT);
        }
        if (preg_match('/Nr\.?\s*Fatures?\s*[:\|]\s*(\d+)/i', $fullText, $m)) {
            $result['invoice']['number'] = trim($m[1]);
        }

        // ── Tabela: lexo çdo rresht ───────────────────────────────
        $inTable  = false;
        $rawItems = [];

        foreach ($lines as $line) {
            if (preg_match('/PERSHKRIM/iu', $line)) {
                $inTable = true;
                continue;
            }
            if ($inTable && preg_match('/TOTALI?\s*:|DEBIA|BLERESI|SHITESI/iu', $line)) {
                $inTable = false;
            }
            if (!$inTable) continue;

            // Pastro artefakte OCR: [], |, brackets, tiret
            $clean = preg_replace('/[\[\]|]/', ' ', $line);
            $clean = preg_replace('/\s+/', ' ', trim($clean));

            // Gjej IMEI (13-16 shifra, zakonisht fillon me 3)
            if (!preg_match('/\b(3\d{12,15})\b/', $clean, $imeiM)) {
                if (!preg_match('/\b(\d{14,16})\b/', $clean, $imeiM)) continue;
            }
            $imei = $this->normalizeImei($imeiM[1]);

            // Gjej emrin e produktit — para IMEI
            $beforeImei  = trim(substr($clean, 0, strpos($clean, $imeiM[1])));
            $productName = trim(preg_replace('/^\d+\s*/', '', $beforeImei)); // hiq nr rreshti
            $productName = preg_replace('/^[a-z]{1,2}(?=[A-Z])/u', '', $productName); // hiq "j" artefakte OCR
            $productName = trim($productName);
            if (empty($productName)) continue;

            // Gjej çmim dhe total — pas IMEI
            $afterImei = trim(substr($clean, strpos($clean, $imeiM[1]) + strlen($imeiM[1])));
            $afterImei = preg_replace('/\b(CO[FRP]|cope|pcs|box|st)\b[,\s]*/i', '', $afterImei);
            preg_match_all('/([\d,\.]+)/', $afterImei, $nums);
            $numList = array_values(array_filter($nums[1], fn($n) => (float) str_replace(',', '', $n) > 0));

            $qty   = 1;
            $price = 0.0;
            $total = 0.0;
            if (count($numList) >= 3) {
                $qty   = (float) str_replace(',', '', $numList[0]);
                $price = (float) str_replace(',', '', $numList[1]);
                $total = (float) str_replace(',', '', $numList[2]);
            } elseif (count($numList) >= 2) {
                $price = (float) str_replace(',', '', $numList[0]);
                $total = (float) str_replace(',', '', $numList[1]);
            } elseif (count($numList) === 1) {
                $price = $total = (float) str_replace(',', '', $numList[0]);
            }
            if ($qty < 1) $qty = 1;

            $parsed             = $this->parseProductName($productName);
            $parsed['category'] = 'Telefona';

            $rawItems[] = [
                'product_name' => $productName,
                'clean_name'   => $parsed['clean_name'],
                'brand'        => $parsed['brand'],
                'category'     => 'Telefona',
                'storage'      => $parsed['storage'],
                'ram'          => $parsed['ram'],
                'color'        => $parsed['color'],
                'quantity'     => $qty,
                'unit_cost'    => $price,
                'tax'          => 0.0,
                'line_total'   => $total ?: ($qty * $price),
                'discount'     => 0,
                'imei_numbers' => $imei ? [$imei] : [],
                '_parsed'      => $parsed,
            ];
        }

        // ── Grupo rreshtat me produkt+çmim të njëjtë ─────────────
        $grouped = [];
        foreach ($rawItems as $item) {
            $key = mb_strtolower(trim($item['product_name'])) . '|' . $item['unit_cost'];
            if (isset($grouped[$key])) {
                $grouped[$key]['quantity']   += 1;
                $grouped[$key]['line_total'] += $item['unit_cost'];
                if (!empty($item['imei_numbers'][0])) {
                    $grouped[$key]['imei_numbers'][] = $item['imei_numbers'][0];
                }
            } else {
                $grouped[$key] = $item;
            }
        }
        $result['items'] = array_values($grouped);

        // ── Totalet ───────────────────────────────────────────────
        if (preg_match('/TOTALI?\s*[:\|]?\s*([\d,\.]+)/iu', $fullText, $m))
            $result['totals']['total'] = (float) str_replace(',', '', $m[1]);
        if (preg_match('/DEBIA TOTAL LEK\s*[:\|]?\s*([\d,\.]+)/iu', $fullText, $m))
            $result['totals']['subtotal'] = (float) str_replace(',', '', $m[1]);

        return $result;
    }

    // ──────────────────────────────────────────────────────────────
    // SHKALLËZO IMAZHIN 3x — OCR lexon shumë më mirë imazhe të mëdha
    // Përdor GD (gjithmonë i disponueshëm në PHP) ose Imagick
    // ──────────────────────────────────────────────────────────────
    private function scaleImageForOcr(string $filePath): string
    {
        $scale = 3;

        // Provo me GD
        if (extension_loaded('gd')) {
            $info = @getimagesize($filePath);
            if (!$info) return $filePath;

            [$w, $h, $type] = [$info[0], $info[1], $info[2]];
            $newW = $w * $scale;
            $newH = $h * $scale;

            $src = match ($type) {
                IMAGETYPE_JPEG => imagecreatefromjpeg($filePath),
                IMAGETYPE_PNG  => imagecreatefrompng($filePath),
                default        => null,
            };
            if (!$src) return $filePath;

            $dst = imagecreatetruecolor($newW, $newH);

            // Mbi bardhë — për imazhe me transparencë
            $white = imagecolorallocate($dst, 255, 255, 255);
            imagefill($dst, 0, 0, $white);

            // Kopjo dhe shkallëzo me interpolim kubik
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $w, $h);

            // Shto kontrast — ndihmon OCR
            imagefilter($dst, IMG_FILTER_CONTRAST, -20);
            imagefilter($dst, IMG_FILTER_SHARPEN);

            $tmpPath = sys_get_temp_dir() . '/ocr_scaled_' . uniqid() . '.png';
            imagepng($dst, $tmpPath);
            imagedestroy($src);
            imagedestroy($dst);

            return $tmpPath;
        }

        // Provo me Imagick
        if (extension_loaded('imagick')) {
            try {
                $img = new \Imagick($filePath);
                $img->resizeImage(
                    $img->getImageWidth()  * $scale,
                    $img->getImageHeight() * $scale,
                    \Imagick::FILTER_LANCZOS,
                    1
                );
                $img->sharpenImage(0, 1);
                $img->normalizeImage();
                $img->setImageFormat('png');

                $tmpPath = sys_get_temp_dir() . '/ocr_scaled_' . uniqid() . '.png';
                $img->writeImage($tmpPath);
                $img->destroy();

                return $tmpPath;
            } catch (\Exception $e) {
                // Fallback — kthe origjinalin
            }
        }

        // Pa preprocessing — kthe direkt
        return $filePath;
    }

    // ──────────────────────────────────────────────────────────────
    // OCR — nxjerr tekst nga imazhi
    // composer require thiagoalessio/tesseract_ocr
    // apt-get install tesseract-ocr tesseract-ocr-eng
    // ──────────────────────────────────────────────────────────────
    private function ocrImage(string $filePath): string
    {
        // Shkallëzo imazhin 3x para OCR për rezultat më të mirë
        // (kërkon: apt-get install imagemagick  ose  php-gd)
        //$scaledPath = $this->scaleImageForOcr($filePath);

        $scaledPath = $filePath; // për testim pa shkallëzim
        try {
            $ocr = new TesseractOCR($scaledPath);
            $text = $ocr
                ->executable('C:\Program Files (x86)\Tesseract-OCR\tesseract.exe')
                ->lang('eng')
                ->psm(60)       // Assume a single uniform block of text
                ->oem(3)       // Default OCR Engine Mode
                ->run();
        } finally {
            // Fshi skedarin e përkohshëm nëse u shkallëzua
            if ($scaledPath !== $filePath && file_exists($scaledPath)) {
                unlink($scaledPath);
            }
        }

        if (empty(trim($text ?? ''))) {
            throw new \Exception(
                'OCR dështoi. Sigurohu: ' .
                    'composer require thiagoalessio/tesseract_ocr  &&  ' .
                    'apt-get install tesseract-ocr tesseract-ocr-eng'
            );
        }

        dd('text', $text);

        return $text;
    }

    private function resolveAndRespond(array $data): \Illuminate\Http\JsonResponse
    {
        // Gjej Partner
        $partner = null;
        if (!empty($data['supplier']['nipt'])) {
            $partner = Partner::where('nipt', $data['supplier']['nipt'])->first();
        }
        if (!$partner && !empty($data['supplier']['name'])) {
            $name    = explode(' - ', $data['supplier']['name'])[0];
            $partner = Partner::where('name', 'like', "%{$name}%")->first();
        }

        // Gjej produkte
        $resolvedItems = [];
        foreach ($data['items'] as $item) {
            $parsed  = $item['_parsed'] ?? $this->parseProductName($item['product_name']);
            $product = $this->findProduct($parsed);

            $resolvedItem                  = $item;
            $resolvedItem['product_id']    = $product?->id;
            $resolvedItem['product_found'] = (bool) $product;
            unset($resolvedItem['_parsed']);
            $resolvedItems[] = $resolvedItem;
        }
        $data['items'] = $resolvedItems;

        return response()->json([
            'success' => true,
            'data'    => $data,
            'partner' => $partner ? ['id' => $partner->id, 'name' => $partner->name] : null,
        ]);
    }

    public function extractImage(Request $request)
    {
        $request->validate([
            'document' => 'required|file|mimes:jpg,jpeg,png|max:10240',
        ]);

        try {
            $text = $this->ocrImage($request->file('document')->getRealPath());

            // Auto-detect format nga teksti OCR
            $data = preg_match('/PERSHKRIM/iu', $text)
                ? $this->parseFormat2($text)
                : $this->parseInvoiceText($text);

            return $this->resolveAndRespond($data);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gabim OCR: ' . $e->getMessage()], 500);
        }
    }

    public function extractPdf(Request $request)
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf|max:10240',
        ]);

        try {
            $parser = new PdfParser();
            $pdf    = $parser->parseFile($request->file('document')->getRealPath());
            $text   = $pdf->getText();
            $data   = $this->parseInvoiceText($text);

            return $this->resolveAndRespond($data);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gabim PDF: ' . $e->getMessage()], 500);
        }
    }
}
