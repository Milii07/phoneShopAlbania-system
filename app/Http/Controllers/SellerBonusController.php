<?php

namespace App\Http\Controllers;

use App\Models\Seller;
use App\Models\SellerBonus;
use App\Models\Sale;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SellerBonusController extends Controller
{
    public function index(Request $request)
    {
        $query = SellerBonus::with('seller');

        if ($request->filled('seller_id')) {
            $query->where('seller_id', $request->seller_id);
        }

        if ($request->filled('period_start')) {
            $query->whereDate('period_start', '>=', $request->period_start);
        }

        if ($request->filled('period_end')) {
            $query->whereDate('period_end', '<=', $request->period_end);
        }

        $bonuses = $query->latest()->paginate(15);
        $sellers = Seller::orderBy('name')->get();

        return view('seller-bonuses.index', compact('bonuses', 'sellers'));
    }

    public function create()
    {
        $sellers = Seller::orderBy('name')->get();
        return view('seller-bonuses.create', compact('sellers'));
    }

    public function calculateBonus(Request $request)
    {
        $validated = $request->validate([
            'seller_id' => 'required|exists:sellers,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'phone_bonus_percentage' => 'required|numeric|min:0|max:100',
            'accessory_bonus_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $seller = Seller::findOrFail($validated['seller_id']);
        $periodStart = Carbon::parse($validated['period_start']);
        $periodEnd = Carbon::parse($validated['period_end']);

        // Get all confirmed sales for this seller in the period
        $sales = Sale::with(['items.category'])
            ->where('seller_id', $seller->id)
            ->where('sale_status', 'Confirmed')
            ->whereBetween('invoice_date', [$periodStart, $periodEnd])
            ->get();

        // Identify phone and accessory categories
        $phoneCategories = Category::whereIn('name', ['Telefona', 'Smartphone', 'Phone', 'Telefonat'])->pluck('id')->toArray();
        $accessoryCategories = Category::whereIn('name', ['Aksesore', 'Accessories', 'Aksesorë'])->pluck('id')->toArray();

        $phoneSalesTotal = 0;
        $accessorySalesTotal = 0;

        foreach ($sales as $sale) {
            foreach ($sale->items as $item) {
                if (in_array($item->category_id, $phoneCategories)) {
                    $phoneSalesTotal += $item->line_total;
                } elseif (in_array($item->category_id, $accessoryCategories)) {
                    $accessorySalesTotal += $item->line_total;
                }
            }
        }

        $phoneBonusAmount = ($phoneSalesTotal * $validated['phone_bonus_percentage']) / 100;
        $accessoryBonusAmount = ($accessorySalesTotal * $validated['accessory_bonus_percentage']) / 100;
        $totalBonus = $phoneBonusAmount + $accessoryBonusAmount;

        $data = [
            'seller' => $seller,
            'period_start' => $periodStart->format('Y-m-d'),
            'period_end' => $periodEnd->format('Y-m-d'),
            'phone_sales_total' => number_format($phoneSalesTotal, 2),
            'accessory_sales_total' => number_format($accessorySalesTotal, 2),
            'phone_bonus_percentage' => $validated['phone_bonus_percentage'],
            'accessory_bonus_percentage' => $validated['accessory_bonus_percentage'],
            'phone_bonus_amount' => number_format($phoneBonusAmount, 2),
            'accessory_bonus_amount' => number_format($accessoryBonusAmount, 2),
            'total_bonus' => number_format($totalBonus, 2),
            'total_sales_count' => $sales->count(),
        ];

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        }

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'seller_id' => 'required|exists:sellers,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'phone_sales_total' => 'required|numeric|min:0',
            'accessory_sales_total' => 'required|numeric|min:0',
            'phone_bonus_percentage' => 'required|numeric|min:0|max:100',
            'accessory_bonus_percentage' => 'required|numeric|min:0|max:100',
            'phone_bonus_amount' => 'required|numeric|min:0',
            'accessory_bonus_amount' => 'required|numeric|min:0',
            'total_bonus' => 'required|numeric|min:0',
            'total_sales_count' => 'required|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        try {
            SellerBonus::create($validated);

            return redirect()->route('seller-bonuses.index')
                ->with('success', 'Bonusi u regjistrua me sukses!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gabim: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(SellerBonus $sellerBonus)
    {
        $sellerBonus->load(['seller']);

        // Get all sales for this bonus period
        $sales = Sale::with(['items.category', 'items.product', 'partner', 'warehouse'])
            ->where('seller_id', $sellerBonus->seller_id)
            ->where('sale_status', 'Confirmed')
            ->whereBetween('invoice_date', [$sellerBonus->period_start, $sellerBonus->period_end])
            ->latest()
            ->get();

        // Categorize sales
        $phoneCategories = Category::whereIn('name', ['Telefona', 'Smartphone', 'Phone', 'Telefonat'])->pluck('id')->toArray();
        $accessoryCategories = Category::whereIn('name', ['Aksesore', 'Accessories', 'Aksesorë'])->pluck('id')->toArray();

        $phoneSales = [];
        $accessorySales = [];

        foreach ($sales as $sale) {
            $hasPhone = false;
            $hasAccessory = false;

            foreach ($sale->items as $item) {
                if (in_array($item->category_id, $phoneCategories)) {
                    $hasPhone = true;
                }
                if (in_array($item->category_id, $accessoryCategories)) {
                    $hasAccessory = true;
                }
            }

            if ($hasPhone) {
                $phoneSales[] = $sale;
            }
            if ($hasAccessory) {
                $accessorySales[] = $sale;
            }
        }

        return view('seller-bonuses.show', compact('sellerBonus', 'sales', 'phoneSales', 'accessorySales'));
    }

    public function destroy(SellerBonus $sellerBonus)
    {
        try {
            $sellerBonus->delete();
            return redirect()->route('seller-bonuses.index')
                ->with('success', 'Bonusi u fshi me sukses!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gabim: ' . $e->getMessage());
        }
    }

    // Seller Report - All sales for a specific seller
    public function sellerReport(Request $request, $sellerId)
    {
        $seller = Seller::findOrFail($sellerId);

        $query = Sale::with(['items.category', 'items.product', 'partner', 'warehouse', 'currency'])
            ->where('seller_id', $sellerId)
            ->where('sale_status', 'Confirmed');

        if ($request->filled('date_from')) {
            $query->whereDate('invoice_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('invoice_date', '<=', $request->date_to);
        }

        $sales = $query->latest()->paginate(20);

        // Calculate totals
        $phoneCategories = Category::whereIn('name', ['Telefona', 'Smartphone', 'Phone', 'Telefonat'])->pluck('id')->toArray();
        $accessoryCategories = Category::whereIn('name', ['Aksesore', 'Accessories', 'Aksesorë'])->pluck('id')->toArray();

        $totalPhoneSales = 0;
        $totalAccessorySales = 0;
        $totalSales = 0;

        foreach ($sales as $sale) {
            $totalSales += $sale->total_amount;
            foreach ($sale->items as $item) {
                if (in_array($item->category_id, $phoneCategories)) {
                    $totalPhoneSales += $item->line_total;
                } elseif (in_array($item->category_id, $accessoryCategories)) {
                    $totalAccessorySales += $item->line_total;
                }
            }
        }

        return view('seller-bonuses.seller-report', compact(
            'seller',
            'sales',
            'totalPhoneSales',
            'totalAccessorySales',
            'totalSales'
        ));
    }
}
