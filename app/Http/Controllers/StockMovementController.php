<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\Product;
use App\Models\Category;
use App\Models\PurchaseItem;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade as DompdfFacade;
use Dompdf\Dompdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        // List products (stock) using Product module only.
        $warehouses = Warehouse::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        $query = Product::with(['category', 'brand', 'warehouses'])->orderBy('name');

        // Filter by warehouse: only products that are linked to that warehouse
        if ($request->filled('warehouse_id')) {
            $query->whereHas('warehouses', function ($q) use ($request) {
                $q->where('warehouse_id', $request->warehouse_id);
            });
        }

        // Filter by category (accepts category_id)
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Search by product name, storage, color or IMEI (term 'q')
        if ($request->filled('q')) {
            $term = trim($request->q);
            $query->where(function ($qq) use ($term) {
                $qq->where('name', 'like', "%{$term}%")
                    ->orWhere('storage', 'like', "%{$term}%")
                    ->orWhere('color', 'like', "%{$term}%");
            });

            // also include products that have the searched IMEI in purchase/sale items
            $piIds = PurchaseItem::where('imei_numbers', 'like', "%{$term}%")->pluck('product_id')->filter()->unique()->values()->all();
            $siIds = SaleItem::where('imei_numbers', 'like', "%{$term}%")->pluck('product_id')->filter()->unique()->values()->all();
            $mergeIds = array_values(array_unique(array_merge($piIds, $siIds)));
            if (!empty($mergeIds)) {
                $query->orWhereIn('id', $mergeIds);
            }
        }

        // Pagination for products stock list
        $products = $query->paginate(50)->appends($request->query());

        // Basic stats based on the filtered set
        $statsQuery = Product::query();
        if ($request->filled('warehouse_id')) {
            $statsQuery->whereHas('warehouses', function ($q) use ($request) {
                $q->where('warehouse_id', $request->warehouse_id);
            });
        }
        if ($request->filled('category_id')) {
            $statsQuery->where('category_id', $request->category_id);
        }

        // apply the same quick search to statsQuery so totals reflect the search
        if ($request->filled('q')) {
            $term = trim($request->q);
            $statsQuery->where(function ($qq) use ($term) {
                $qq->where('name', 'like', "%{$term}%")
                    ->orWhere('storage', 'like', "%{$term}%")
                    ->orWhere('color', 'like', "%{$term}%");
            });

            $piIds = PurchaseItem::where('imei_numbers', 'like', "%{$term}%")->pluck('product_id')->filter()->unique()->values()->all();
            $siIds = SaleItem::where('imei_numbers', 'like', "%{$term}%")->pluck('product_id')->filter()->unique()->values()->all();
            $mergeIds = array_values(array_unique(array_merge($piIds, $siIds)));
            if (!empty($mergeIds)) {
                $statsQuery->orWhereIn('id', $mergeIds);
            }
        }

        $totalProducts = $statsQuery->count();
        // compute total stock across the filtered products
        // Sum warehouse pivot quantities to compute total stock across filtered products
        $totalStock = $statsQuery->with('warehouses')->get()->sum(function ($p) {
            return $p->warehouses->sum(function ($w) {
                return isset($w->pivot->quantity) ? (int) $w->pivot->quantity : 0;
            });
        });

        $stats = [
            'total_products' => $totalProducts,
            'total_stock' => $totalStock,
        ];

        // Compute unsold IMEIs per product (based on PurchaseItem vs SaleItem)
        $productItems = collect($products->items());
        $productIds = $productItems->pluck('id')->unique()->filter()->values()->all();

        $purchased = PurchaseItem::whereIn('product_id', $productIds)->get();
        $sold = SaleItem::whereIn('product_id', $productIds)->get();

        $purchasedByProduct = [];
        foreach ($purchased as $pi) {
            $pid = $pi->product_id;
            $arr = [];
            if (!empty($pi->imei_numbers)) {
                if (is_array($pi->imei_numbers)) {
                    $arr = $pi->imei_numbers;
                } else {
                    $decoded = json_decode($pi->imei_numbers, true);
                    if (is_array($decoded)) {
                        $arr = $decoded;
                    } else {
                        // try comma separated
                        $arr = array_map('trim', explode(',', $pi->imei_numbers));
                    }
                }
            }
            if (!isset($purchasedByProduct[$pid])) $purchasedByProduct[$pid] = [];
            $purchasedByProduct[$pid] = array_merge($purchasedByProduct[$pid], $arr);
        }



        $soldByProduct = [];
        foreach ($sold as $si) {
            $pid = $si->product_id;
            $arr = [];
            if (!empty($si->imei_numbers)) {
                if (is_array($si->imei_numbers)) {
                    $arr = $si->imei_numbers;
                } else {
                    $decoded = json_decode($si->imei_numbers, true);
                    if (is_array($decoded)) {
                        $arr = $decoded;
                    } else {
                        $arr = array_map('trim', explode(',', $si->imei_numbers));
                    }
                }
            }
            if (!isset($soldByProduct[$pid])) $soldByProduct[$pid] = [];
            $soldByProduct[$pid] = array_merge($soldByProduct[$pid], $arr);
        }

        // Attach unsold_imeis to each product in the current page
        foreach ($productItems as $prod) {
            $pid = $prod->id;
            $purchasedList = array_unique(array_filter($purchasedByProduct[$pid] ?? []));
            $soldList = array_unique(array_filter($soldByProduct[$pid] ?? []));
            $unsold = array_values(array_diff($purchasedList, $soldList));
            // attach to model instance so view can access
            $prod->unsold_imeis = $unsold;
        }

        return view('stock-movements.index', compact('products', 'warehouses', 'categories', 'stats'));
    }

    // Other actions removed — this controller now only exposes the index view

    /**
     * Export stock as PDF using Dompdf (requires barryvdh/laravel-dompdf)
     */
    public function exportPdf(Request $request)
    {
        $query = Product::with(['category', 'brand', 'warehouses'])->orderBy('name');
        if ($request->filled('warehouse_id')) {
            $query->whereHas('warehouses', function ($q) use ($request) {
                $q->where('warehouse_id', $request->warehouse_id);
            });
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Respect pagination parameters so exports can export the current page only
        $perPage = (int) $request->input('per_page', 50);
        $page = (int) $request->input('page', 1);
        $productsPaginator = $query->paginate($perPage, ['*'], 'page', $page);
        $products = collect($productsPaginator->items());

        $productIds = $products->pluck('id')->filter()->values()->all();
        $purchased = PurchaseItem::whereIn('product_id', $productIds)->get();
        $sold = SaleItem::whereIn('product_id', $productIds)->get();

        $purchasedByProduct = [];
        foreach ($purchased as $pi) {
            $pid = $pi->product_id;
            $arr = [];
            if (!empty($pi->imei_numbers)) {
                $arr = is_array($pi->imei_numbers) ? $pi->imei_numbers : (json_decode($pi->imei_numbers, true) ?: array_map('trim', explode(',', $pi->imei_numbers)));
            }
            $purchasedByProduct[$pid] = array_merge($purchasedByProduct[$pid] ?? [], $arr);
        }

        $soldByProduct = [];
        foreach ($sold as $si) {
            $pid = $si->product_id;
            $arr = [];
            if (!empty($si->imei_numbers)) {
                $arr = is_array($si->imei_numbers) ? $si->imei_numbers : (json_decode($si->imei_numbers, true) ?: array_map('trim', explode(',', $si->imei_numbers)));
            }
            $soldByProduct[$pid] = array_merge($soldByProduct[$pid] ?? [], $arr);
        }

        foreach ($products as $prod) {
            $pid = $prod->id;
            $purchasedList = array_unique(array_filter($purchasedByProduct[$pid] ?? []));
            $soldList = array_unique(array_filter($soldByProduct[$pid] ?? []));
            $prod->unsold_imeis = array_values(array_diff($purchasedList, $soldList));
        }

        $data = ['products' => $products];

        $filename = 'stock_' . Str::slug(now()->toDateString());
        if ($page > 1) {
            $filename .= '_page_' . $page;
        }
        $filename .= '.pdf';

        // Prefer the Laravel Dompdf wrapper when available
        if (class_exists(DompdfFacade::class)) {
            $pdf = DompdfFacade::loadView('stock-movements.exports.pdf', $data);
            // use A4 landscape for better table fit
            try {
                $pdf->setPaper('a4', 'landscape');
            } catch (\Throwable $e) {
                // ignore if the wrapper doesn't support setPaper
            }
            return $pdf->download($filename);
        }

        // If the Laravel wrapper is not installed but Dompdf exists, use it directly
        if (class_exists(Dompdf::class)) {
            $html = view('stock-movements.exports.pdf', $data)->render();
            $dompdf = new Dompdf();
            // enable HTML5 parser (if available)
            if (method_exists($dompdf, 'set_option')) {
                try {
                    $dompdf->set_option('isHtml5ParserEnabled', true);
                } catch (\Throwable $e) {
                }
            }
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            return response($dompdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]);
        }

        // Fall back to returning an HTML preview when no PDF library is available
        return view('stock-movements.exports.pdf', $data);
    }

    /**
     * Export stock as XLSX using PhpSpreadsheet (requires phpoffice/phpspreadsheet)
     */
    public function exportXlsx(Request $request)
    {
        $query = Product::with(['category', 'brand', 'warehouses'])->orderBy('name');
        if ($request->filled('warehouse_id')) {
            $query->whereHas('warehouses', function ($q) use ($request) {
                $q->where('warehouse_id', $request->warehouse_id);
            });
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Respect pagination parameters so exports can export the current page only
        $perPage = (int) $request->input('per_page', 50);
        $page = (int) $request->input('page', 1);
        $productsPaginator = $query->paginate($perPage, ['*'], 'page', $page);
        $products = collect($productsPaginator->items());

        $productIds = $products->pluck('id')->filter()->values()->all();
        $purchased = PurchaseItem::whereIn('product_id', $productIds)->get();
        $sold = SaleItem::whereIn('product_id', $productIds)->get();

        $purchasedByProduct = [];
        foreach ($purchased as $pi) {
            $pid = $pi->product_id;
            $arr = [];
            if (!empty($pi->imei_numbers)) {
                $arr = is_array($pi->imei_numbers) ? $pi->imei_numbers : (json_decode($pi->imei_numbers, true) ?: array_map('trim', explode(',', $pi->imei_numbers)));
            }
            $purchasedByProduct[$pid] = array_merge($purchasedByProduct[$pid] ?? [], $arr);
        }

        $soldByProduct = [];
        foreach ($sold as $si) {
            $pid = $si->product_id;
            $arr = [];
            if (!empty($si->imei_numbers)) {
                $arr = is_array($si->imei_numbers) ? $si->imei_numbers : (json_decode($si->imei_numbers, true) ?: array_map('trim', explode(',', $si->imei_numbers)));
            }
            $soldByProduct[$pid] = array_merge($soldByProduct[$pid] ?? [], $arr);
        }

        foreach ($products as $prod) {
            $pid = $prod->id;
            $purchasedList = array_unique(array_filter($purchasedByProduct[$pid] ?? []));
            $soldList = array_unique(array_filter($soldByProduct[$pid] ?? []));
            $prod->unsold_imeis = array_values(array_diff($purchasedList, $soldList));
        }

        if (class_exists(Spreadsheet::class)) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $headers = ['Product', 'Category', 'Brand', 'Unit Price', 'Total Stock', 'Unsold IMEIs'];
            $col = 1;
            foreach ($headers as $h) {
                $cell = Coordinate::stringFromColumnIndex($col) . '1';
                $sheet->setCellValue($cell, $h);
                $col++;
            }
            $row = 2;
            foreach ($products as $p) {
                $sheet->setCellValue(Coordinate::stringFromColumnIndex(1) . $row, $p->name);
                $sheet->setCellValue(Coordinate::stringFromColumnIndex(2) . $row, $p->category?->name ?? '');
                $sheet->setCellValue(Coordinate::stringFromColumnIndex(3) . $row, $p->brand?->name ?? '');
                $sheet->setCellValue(Coordinate::stringFromColumnIndex(4) . $row, $p->unit_price ?? $p->price ?? 0);
                $sheet->setCellValue(Coordinate::stringFromColumnIndex(5) . $row, $p->warehouses->sum(function ($w) {
                    return isset($w->pivot->quantity) ? (int) $w->pivot->quantity : 0;
                }));
                $sheet->setCellValue(Coordinate::stringFromColumnIndex(6) . $row, implode(', ', $p->unsold_imeis ?? []));
                $row++;
            }

            $writer = new XlsxWriter($spreadsheet);
            $filename = 'stock_' . Str::slug(now()->toDateString());
            if ($page > 1) {
                $filename .= '_page_' . $page;
            }
            $filename .= '.xlsx';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            $writer->save('php://output');
            exit;
        }

        $filename = 'stock_' . Str::slug(now()->toDateString());
        if ($page > 1) {
            $filename .= '_page_' . $page;
        }
        $filename .= '.csv';
        $handle = fopen('php://output', 'w');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        fputcsv($handle, ['Product', 'Category', 'Brand', 'Unit Price', 'Total Stock', 'Unsold IMEIs']);
        foreach ($products as $p) {
            fputcsv($handle, [
                $p->name,
                $p->category?->name ?? '',
                $p->brand?->name ?? '',
                $p->unit_price ?? $p->price ?? 0,
                $p->warehouses->sum(function ($w) {
                    return isset($w->pivot->quantity) ? (int) $w->pivot->quantity : 0;
                }),
                implode('|', $p->unsold_imeis ?? []),
            ]);
        }
        fclose($handle);
        exit;
    }
}
