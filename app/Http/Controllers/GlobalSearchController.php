<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Partner;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\OnlineOrder;
use App\Models\SellerBonus;
use Illuminate\Support\Facades\DB;

class GlobalSearchController extends Controller
{
    public function search(Request $request)
    {
        $q = trim($request->get('q', ''));

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $results = [];

        // ==================== PARTNERS ====================
        $partners = Partner::where('name', 'like', "%{$q}%")
            ->orWhere('phone', 'like', "%{$q}%")
            ->limit(5)
            ->get();

        foreach ($partners as $p) {
            $results[] = [
                'type'     => 'partner',
                'icon'     => 'ri-user-line',
                'color'    => 'primary',
                'label'    => 'Klient',
                'title'    => $p->name,
                'subtitle' => $p->phone,
                'id'       => $p->id,
                'url'      => null,
            ];
        }

        // ==================== SALES (Faturat) ====================
        $sales = Sale::with('partner')
            ->where(function ($query) use ($q) {
                $query->where('invoice_number', 'like', "%{$q}%")
                    ->orWhereHas('partner', fn($pq) => $pq->where('name', 'like', "%{$q}%"));
            })
            ->limit(5)
            ->get();

        foreach ($sales as $s) {
            $results[] = [
                'type'     => 'sale',
                'icon'     => 'ri-file-list-3-line',
                'color'    => 'success',
                'label'    => 'Faturë',
                'title'    => $s->invoice_number,
                'subtitle' => ($s->partner->name ?? '-') . ' • ' . $s->invoice_date->format('d-m-Y'),
                'id'       => $s->id,
                'url'      => route('sales.show', $s->id),
            ];
        }

        // ==================== PRODUCTS ====================
        $products = Product::with(['brand', 'category'])
            ->where('name', 'like', "%{$q}%")
            ->orWhere('storage', 'like', "%{$q}%")
            ->orWhere('color', 'like', "%{$q}%")
            ->limit(5)
            ->get();

        foreach ($products as $pr) {
            $stockRows = DB::table('product_warehouse')
                ->join('warehouses', 'product_warehouse.warehouse_id', '=', 'warehouses.id')
                ->where('product_warehouse.product_id', $pr->id)
                ->where('product_warehouse.quantity', '>', 0)
                ->select('warehouses.name as warehouse_name', 'product_warehouse.quantity')
                ->get();

            $totalStock = $stockRows->sum('quantity');

            $warehouseInfo = $stockRows->map(fn($r) => $r->warehouse_name . ': ' . $r->quantity)->implode(' | ');

            $stockBadge = $totalStock > 0
                ? "Stok: {$totalStock}" . ($warehouseInfo ? " ({$warehouseInfo})" : '')
                : 'Pa stok';

            $results[] = [
                'type'     => 'product',
                'icon'     => 'ri-smartphone-line',
                'color'    => $totalStock > 0 ? 'info' : 'danger',
                'label'    => 'Produkt',
                'title'    => $pr->name,
                'subtitle' => implode(' • ', array_filter([
                    $pr->brand->name ?? null,
                    $pr->category->name ?? null,
                    $pr->storage,
                    $pr->color,
                    $stockBadge,
                ])),
                'id'       => $pr->id,
                'url'      => route('products.show', $pr->id),
            ];
        }

        // ==================== IMEI KËRKIM (purchase_items + sale_items) ====================
        // Kërko IMEI në purchase_items (stoku i hyrë)
        $purchaseImeiItems = DB::table('purchase_items')
            ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->join('warehouses', 'purchases.warehouse_id', '=', 'warehouses.id')
            ->join('products', 'purchase_items.product_id', '=', 'products.id')
            ->join('brands', 'products.brand_id', '=', 'brands.id')
            ->where('purchase_items.imei_numbers', 'like', "%{$q}%")
            ->select(
                'purchase_items.product_name',
                'purchase_items.storage',
                'purchase_items.color',
                'purchase_items.imei_numbers',
                'purchase_items.unit_cost',
                'purchase_items.selling_price',
                'purchases.id as purchase_id',
                'purchases.purchase_number',
                'purchases.purchase_date',
                'warehouses.name as warehouse_name',
                'brands.name as brand_name'
            )
            ->limit(5)
            ->get();

        // Merr të gjithë IMEI-t e shitur (nga sale_items) për krahasim
        $soldImeis = DB::table('sale_items')
            ->whereNotNull('imei_numbers')
            ->pluck('imei_numbers')
            ->flatMap(fn($json) => json_decode($json, true) ?? [])
            ->map(fn($imei) => strtolower(trim($imei)))
            ->toArray();

        foreach ($purchaseImeiItems as $item) {
            $imeiList = json_decode($item->imei_numbers, true) ?? [];

            // Filtro vetëm IMEI-t që përputhen me query-n
            $matchedImeis = array_filter($imeiList, fn($imei) => str_contains(strtolower($imei), strtolower($q)));

            foreach ($matchedImeis as $imei) {
                $isSold = in_array(strtolower(trim($imei)), $soldImeis);

                // Gjej në cilin sale_item është shitur ky IMEI (nëse është shitur)
                $saleInfo = null;
                if ($isSold) {
                    $saleItem = DB::table('sale_items')
                        ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                        ->join('partners', 'sales.partner_id', '=', 'partners.id')
                        ->where('sale_items.imei_numbers', 'like', "%{$imei}%")
                        ->select('sales.invoice_number', 'partners.name as partner_name', 'sales.invoice_date', 'sales.id as sale_id')
                        ->first();

                    if ($saleItem) {
                        $saleInfo = $saleItem;
                    }
                }

                if ($isSold && $saleInfo) {
                    // IMEI i shitur — trego faturën e shitjes
                    $results[] = [
                        'type'     => 'imei',
                        'icon'     => 'ri-barcode-line',
                        'color'    => 'secondary',
                        'label'    => 'IMEI (Shitur)',
                        'title'    => ($item->brand_name ? $item->brand_name . ' ' : '') . $item->product_name . ' — ' . $imei,
                        'subtitle' => implode(' • ', array_filter([
                            $item->storage,
                            $item->color,
                            'Fatura: ' . $saleInfo->invoice_number,
                            $saleInfo->partner_name,
                            date('d-m-Y', strtotime($saleInfo->invoice_date)),
                        ])),
                        'id'       => $saleInfo->sale_id,
                        'url'      => route('sales.show', $saleInfo->sale_id),
                    ];
                } else {
                    // IMEI në stok — trego dyqanin dhe çmimin
                    $results[] = [
                        'type'     => 'imei',
                        'icon'     => 'ri-barcode-line',
                        'color'    => 'warning',
                        'label'    => 'IMEI (Në Stok)',
                        'title'    => ($item->brand_name ? $item->brand_name . ' ' : '') . $item->product_name . ' — ' . $imei,
                        'subtitle' => implode(' • ', array_filter([
                            $item->storage,
                            $item->color,
                            'Dyqan: ' . $item->warehouse_name,
                            $item->selling_price > 0 ? 'Çmim: ' . number_format($item->selling_price, 2) : null,
                            $item->unit_cost > 0 ? 'Blerë: ' . number_format($item->unit_cost, 2) : null,
                            'Hyrje: ' . $item->purchase_number,
                        ])),
                        'id'       => $item->purchase_id,
                        'url'      => route('purchases.show', $item->purchase_id),
                    ];
                }
            }
        }

        // ==================== PURCHASES (Hyrjet) ====================
        $purchases = Purchase::with('partner')
            ->where(function ($query) use ($q) {
                $query->where('purchase_number', 'like', "%{$q}%")
                    ->orWhereHas('partner', fn($pq) => $pq->where('name', 'like', "%{$q}%"));
            })
            ->limit(3)
            ->get();

        foreach ($purchases as $pu) {
            $results[] = [
                'type'     => 'purchase',
                'icon'     => 'ri-download-2-line',
                'color'    => 'secondary',
                'label'    => 'Hyrje',
                'title'    => $pu->purchase_number,
                'subtitle' => ($pu->partner->name ?? '-') . ' • ' . $pu->purchase_date,
                'id'       => $pu->id,
                'url'      => route('purchases.show', $pu->id),
            ];
        }

        // ==================== ONLINE ORDERS ====================
        $orders = OnlineOrder::with('partner')
            ->where(function ($query) use ($q) {
                $query->where('order_number', 'like', "%{$q}%")
                    ->orWhereHas('partner', fn($pq) => $pq->where('name', 'like', "%{$q}%"));
            })
            ->limit(3)
            ->get();

        foreach ($orders as $o) {
            $results[] = [
                'type'     => 'online_order',
                'icon'     => 'ri-shopping-cart-line',
                'color'    => 'info',
                'label'    => 'Porosi Online',
                'title'    => $o->order_number,
                'subtitle' => ($o->partner->name ?? '-') . ' • ' . $o->order_date,
                'id'       => $o->id,
                'url'      => route('online-orders.show', $o->id),
            ];
        }

        // ==================== SELLER BONUSES ====================
        $bonuses = SellerBonus::with('seller')
            ->whereHas('seller', fn($sq) => $sq->where('name', 'like', "%{$q}%"))
            ->limit(3)
            ->get();

        foreach ($bonuses as $b) {
            $results[] = [
                'type'     => 'bonus',
                'icon'     => 'ri-gift-line',
                'color'    => 'success',
                'label'    => 'Bonus',
                'title'    => $b->seller->name ?? '-',
                'subtitle' => $b->period_start . ' → ' . $b->period_end . ' • ' . number_format($b->total_bonus, 2) . ' ALL',
                'id'       => $b->id,
                'url'      => route('seller-bonuses.index'),
            ];
        }

        $pages = $this->getNavigationPages();

        foreach ($pages as $page) {
            $haystack = strtolower($page['title'] . ' ' . implode(' ', $page['keywords']));
            $needle   = strtolower($q);

            if (str_contains($haystack, $needle)) {
                $allowed = true;
                if (!empty($page['permission']) && !auth()->user()->can($page['permission'])) {
                    $allowed = false;
                }
                if (!empty($page['role']) && !auth()->user()->hasRole($page['role'])) {
                    $allowed = false;
                }

                if ($allowed) {
                    $results[] = [
                        'type'     => 'page',
                        'icon'     => $page['icon'],
                        'color'    => 'dark',
                        'label'    => 'Faqe',
                        'title'    => $page['title'],
                        'subtitle' => $page['description'] ?? '',
                        'id'       => null,
                        'url'      => $page['url'],
                    ];
                }
            }
        }

        return response()->json($results);
    }

    private function getNavigationPages(): array
    {
        return [
            [
                'title'       => 'Dashboard',
                'description' => 'Statistikat kryesore',
                'icon'        => 'ri-dashboard-2-line',
                'url'         => route('dashboard'),
                'permission'  => 'view statistics',
                'keywords'    => ['dashboard', 'statistika', 'kryefaqe', 'home', 'ballina'],
            ],

            [
                'title'       => 'Shitjet',
                'description' => 'Lista e të gjitha shitjeve',
                'icon'        => 'ri-shopping-bag-3-line',
                'url'         => route('sales.index'),
                'permission'  => 'view sales',
                'keywords'    => ['shitjet', 'shitje', 'faturat', 'fatura', 'sales', 'lista shitjeve'],
            ],
            [
                'title'       => 'Krijo Shitje',
                'description' => 'Shto një shitje të re',
                'icon'        => 'ri-add-circle-line',
                'url'         => route('sales.create'),
                'permission'  => 'create sales',
                'keywords'    => ['krijo shitje', 'shitje e re', 'shto shitje', 'new sale', 'create sale', 'fature e re', 'faturë e re'],
            ],


            [
                'title'       => 'Blerjet & Hyrjet',
                'description' => 'Lista e të gjitha blerjeve',
                'icon'        => 'ri-shopping-cart-2-line',
                'url'         => route('purchases.index'),
                'permission'  => 'view purchases',
                'keywords'    => ['blerjet', 'blerje', 'hyrjet', 'hyrje', 'purchases', 'lista blerjeve'],
            ],
            [
                'title'       => 'Krijo Hyrje',
                'description' => 'Shto një blerje / hyrje të re',
                'icon'        => 'ri-add-circle-line',
                'url'         => route('purchases.create'),
                'permission'  => 'create purchases',
                'keywords'    => ['krijo hyrje', 'hyrje e re', 'blerje e re', 'shto blerje', 'new purchase', 'create purchase'],
            ],


            [
                'title'       => 'Raportet Ditore',
                'description' => 'Raportet e shitjeve ditore',
                'icon'        => 'ri-bar-chart-fill',
                'url'         => route('sales.daily-report'),
                'role'        => 'admin',
                'keywords'    => ['raportet', 'raport', 'ditore', 'reports', 'daily report', 'statistika ditore'],
            ],

            [
                'title'       => 'Inventari',
                'description' => 'Lëvizjet e stokut',
                'icon'        => 'ri-todo-line',
                'url'         => route('stock-movements.index'),
                'role'        => 'admin',
                'keywords'    => ['inventari', 'inventar', 'stoku', 'stock', 'levizjet', 'lëvizjet', 'magazina'],
            ],


            [
                'title'       => 'Produktet',
                'description' => 'Lista e të gjitha produkteve',
                'icon'        => 'ri-barcode-line',
                'url'         => route('products.index'),
                'permission'  => 'view products',
                'keywords'    => ['produktet', 'produkt', 'produkti', 'products', 'telefona', 'aksesore', 'artikuj'],
            ],

            [
                'title'       => 'Menaxhimi i Pagesave',
                'description' => 'Borxhet dhe pagesat',
                'icon'        => 'ri-wallet-3-line',
                'url'         => route('debts.index'),
                'role'        => 'admin',
                'keywords'    => ['pagesat', 'pagesa', 'borxhi', 'borxhet', 'debts', 'payments', 'menaxhimi pagesave', 'detyrime'],
            ],


            [
                'title'       => 'Porositë Online',
                'description' => 'Të gjitha porositë online',
                'icon'        => 'ri-truck-line',
                'url'         => route('online-orders.index'),
                'permission'  => 'view orders',
                'keywords'    => ['porosi', 'porosite', 'online', 'orders', 'online orders', 'dërgesa', 'dergesa'],
            ],

            [
                'title'       => 'Shitësit',
                'description' => 'Lista e shitësve',
                'icon'        => 'ri-user-add-fill',
                'url'         => route('sellers.index'),
                'role'        => 'admin',
                'keywords'    => ['shitesit', 'shitësi', 'shitësit', 'sellers', 'punonjesit', 'punonjësit', 'stafi'],
            ],


            [
                'title'       => 'Bonuset',
                'description' => 'Bonuset e shitësve',
                'icon'        => 'ri-medal-line',
                'url'         => route('seller-bonuses.index'),
                'permission'  => 'view seller-bonuses',
                'keywords'    => ['bonuset', 'bonus', 'bonuse', 'seller bonuses', 'shperblime', 'shpërblime'],
            ],


            [
                'title'       => 'Dyqanet',
                'description' => 'Menaxho dyqanet / depot',
                'icon'        => 'ri-store-2-line',
                'url'         => route('warehouses.index'),
                'role'        => 'admin',
                'keywords'    => ['dyqanet', 'dyqan', 'depot', 'depo', 'warehouses', 'magazina'],
            ],

            [
                'title'       => 'Kategorite',
                'description' => 'Kategoritë e produkteve',
                'icon'        => 'ri-smartphone-fill',
                'url'         => route('categories.index'),
                'permission'  => 'view categories',
                'keywords'    => ['kategorite', 'kategori', 'categories', 'llojet'],
            ],

            [
                'title'       => 'Brendet',
                'description' => 'Markat e produkteve',
                'icon'        => 'ri-apple-fill',
                'url'         => route('brands.index'),
                'permission'  => 'view brands',
                'keywords'    => ['brendet', 'brend', 'brands', 'marka', 'markat', 'apple', 'samsung', 'xiaomi'],
            ],

            [
                'title'       => 'Monedhat',
                'description' => 'Kurset e këmbimit',
                'icon'        => 'ri-money-pound-box-fill',
                'url'         => route('currencies.index'),
                'permission'  => 'view currencies',
                'keywords'    => ['monedhat', 'monedhë', 'valuta', 'currencies', 'kursi', 'kembimi', 'këmbimi', 'lek', 'euro', 'usd'],
            ],

            [
                'title'       => 'Partnerët',
                'description' => 'Klientët dhe furnitorët',
                'icon'        => 'ri-group-2-fill',
                'url'         => route('partners.index'),
                'permission'  => 'view partners',
                'keywords'    => ['partneret', 'partner', 'klientet', 'klient', 'furnitori', 'furnitoret', 'partners', 'clients'],
            ],

            [
                'title'       => 'Menaxho Lejet',
                'description' => 'Lejet dhe rolet e përdoruesve',
                'icon'        => 'ri-shield-keyhole-line',
                'url'         => route('admin.permissions.index'),
                'role'        => 'admin',
                'keywords'    => ['lejet', 'leje', 'permissions', 'rolet', 'role', 'admin', 'administrata', 'perdoruesit', 'përdoruesit'],
            ],
        ];
    }
}
