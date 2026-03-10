<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ExchangeRateController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\OnlineOrderController;
use App\Http\Controllers\SellerBonusController;
use App\Http\Controllers\PermissionManagerController;

Route::get('/', fn() => redirect()->route('dashboard'));

require __DIR__ . '/auth.php';

Route::middleware(['auth', 'verified'])->group(function () {

    // ── Dashboard ─────────────────────────────────────────────────────────────
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:view statistics')
        ->name('dashboard');

    // ── Profile ───────────────────────────────────────────────────────────────
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/',    [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/',  [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });

    // ── Warehouses ────────────────────────────────────────────────────────────
    Route::middleware('role:admin')->group(function () {
        Route::get('warehouses',                [WarehouseController::class, 'index'])->name('warehouses.index');
        Route::get('warehouses/create',         [WarehouseController::class, 'create'])->name('warehouses.create');
        Route::post('warehouses',               [WarehouseController::class, 'store'])->name('warehouses.store');
        Route::get('warehouses/{warehouse}',    [WarehouseController::class, 'show'])->name('warehouses.show');
        Route::get('warehouses/{warehouse}/edit', [WarehouseController::class, 'edit'])->name('warehouses.edit');
        Route::put('warehouses/{warehouse}',    [WarehouseController::class, 'update'])->name('warehouses.update');
        Route::patch('warehouses/{warehouse}',  [WarehouseController::class, 'update']);
        Route::delete('warehouses/{warehouse}', [WarehouseController::class, 'destroy'])->name('warehouses.destroy');
    });

    // ── Categories ────────────────────────────────────────────────────────────
    Route::get('categories',                  [CategoryController::class, 'index'])->middleware('permission:view categories')->name('categories.index');
    Route::get('categories/create',           [CategoryController::class, 'create'])->middleware('permission:create categories')->name('categories.create');
    Route::post('categories',                 [CategoryController::class, 'store'])->middleware('permission:create categories')->name('categories.store');
    Route::get('categories/{category}',       [CategoryController::class, 'show'])->middleware('permission:view categories')->name('categories.show');
    Route::get('categories/{category}/edit',  [CategoryController::class, 'edit'])->middleware('permission:edit categories')->name('categories.edit');
    Route::put('categories/{category}',       [CategoryController::class, 'update'])->middleware('permission:edit categories')->name('categories.update');
    Route::patch('categories/{category}',     [CategoryController::class, 'update'])->middleware('permission:edit categories');
    Route::delete('categories/{category}',    [CategoryController::class, 'destroy'])->middleware('permission:delete categories')->name('categories.destroy');

    // ── Brands ────────────────────────────────────────────────────────────────
    Route::get('brands',              [BrandController::class, 'index'])->middleware('permission:view brands')->name('brands.index');
    Route::get('brands/create',       [BrandController::class, 'create'])->middleware('permission:create brands')->name('brands.create');
    Route::post('brands',             [BrandController::class, 'store'])->middleware('permission:create brands')->name('brands.store');
    Route::get('brands/{brand}',      [BrandController::class, 'show'])->middleware('permission:view brands')->name('brands.show');
    Route::get('brands/{brand}/edit', [BrandController::class, 'edit'])->middleware('permission:edit brands')->name('brands.edit');
    Route::put('brands/{brand}',      [BrandController::class, 'update'])->middleware('permission:edit brands')->name('brands.update');
    Route::patch('brands/{brand}',    [BrandController::class, 'update'])->middleware('permission:edit brands');
    Route::delete('brands/{brand}',   [BrandController::class, 'destroy'])->middleware('permission:delete brands')->name('brands.destroy');

    // ── Products ──────────────────────────────────────────────────────────────
    Route::get('/api/warehouses', [ProductController::class, 'getWarehouses'])->name('api.warehouses');
    Route::get('/api/categories', [ProductController::class, 'getCategories'])->name('api.categories');
    Route::get('/api/brands',     [ProductController::class, 'getBrands'])->name('api.brands');

    Route::get('products', [ProductController::class, 'index'])->middleware('permission:view products|create products')->name('products.index');
    Route::get('products/create',         [ProductController::class, 'create'])->middleware('permission:create products')->name('products.create');
    Route::post('products',               [ProductController::class, 'store'])->middleware('permission:create products')->name('products.store');
    Route::get('products/{product}',      [ProductController::class, 'show'])->middleware('permission:view products')->name('products.show');
    Route::get('products/{product}/edit', [ProductController::class, 'edit'])->middleware('permission:edit products')->name('products.edit');
    Route::put('products/{product}',      [ProductController::class, 'update'])->middleware('permission:edit products')->name('products.update');
    Route::patch('products/{product}',    [ProductController::class, 'update'])->middleware('permission:edit products');
    Route::delete('products/{product}',   [ProductController::class, 'destroy'])->middleware('permission:delete products')->name('products.destroy');

    // ── Currencies ────────────────────────────────────────────────────────────
    Route::get('currencies',                  [CurrencyController::class, 'index'])->middleware('permission:view currencies')->name('currencies.index');
    Route::get('currencies/create',           [CurrencyController::class, 'create'])->middleware('permission:create currencies')->name('currencies.create');
    Route::post('currencies',                 [CurrencyController::class, 'store'])->middleware('permission:create currencies')->name('currencies.store');
    Route::get('currencies/{currency}',       [CurrencyController::class, 'show'])->middleware('permission:view currencies')->name('currencies.show');
    Route::get('currencies/{currency}/edit',  [CurrencyController::class, 'edit'])->middleware('permission:edit currencies')->name('currencies.edit');
    Route::put('currencies/{currency}',       [CurrencyController::class, 'update'])->middleware('permission:edit currencies')->name('currencies.update');
    Route::patch('currencies/{currency}',     [CurrencyController::class, 'update'])->middleware('permission:edit currencies');
    Route::delete('currencies/{currency}',    [CurrencyController::class, 'destroy'])->middleware('permission:delete currencies')->name('currencies.destroy');

    // ── Partners ──────────────────────────────────────────────────────────────
    Route::get('partners/search', [PartnerController::class, 'search'])->name('partners.search');

    Route::get('partners',                [PartnerController::class, 'index'])->middleware('permission:view partners')->name('partners.index');
    Route::get('partners/create',         [PartnerController::class, 'create'])->middleware('permission:create partners')->name('partners.create');
    Route::post('partners',               [PartnerController::class, 'store'])->middleware('permission:create partners')->name('partners.store');
    Route::get('partners/{partner}',      [PartnerController::class, 'show'])->middleware('permission:view partners')->name('partners.show');
    Route::get('partners/{partner}/edit', [PartnerController::class, 'edit'])->middleware('permission:edit partners')->name('partners.edit');
    Route::put('partners/{partner}',      [PartnerController::class, 'update'])->middleware('permission:edit partners')->name('partners.update');
    Route::patch('partners/{partner}',    [PartnerController::class, 'update'])->middleware('permission:edit partners');
    Route::delete('partners/{partner}',   [PartnerController::class, 'destroy'])->middleware('permission:delete partners')->name('partners.destroy');

    // ── Sellers ───────────────────────────────────────────────────────────────
    Route::middleware('role:admin')->group(function () {
        Route::get('sellers',                [SellerController::class, 'index'])->name('sellers.index');
        Route::get('sellers/create',         [SellerController::class, 'create'])->name('sellers.create');
        Route::post('sellers',               [SellerController::class, 'store'])->name('sellers.store');
        Route::get('sellers/{seller}',       [SellerController::class, 'show'])->name('sellers.show');
        Route::get('sellers/{seller}/edit',  [SellerController::class, 'edit'])->name('sellers.edit');
        Route::put('sellers/{seller}',       [SellerController::class, 'update'])->name('sellers.update');
        Route::patch('sellers/{seller}',     [SellerController::class, 'update']);
        Route::delete('sellers/{seller}',    [SellerController::class, 'destroy'])->name('sellers.destroy');
    });

    // ── Purchases ─────────────────────────────────────────────────────────────
    Route::post('purchase/extract-pdf',    [PurchaseController::class, 'extractPdf'])->name('purchases.extract-pdf');
    Route::post('purchases/extract-excel', [PurchaseController::class, 'extractExcel'])->name('purchases.extract-excel');
    Route::post('purchase/extract-image',  [PurchaseController::class, 'extractImage'])->name('purchases.extract-image');
    Route::get('purchases-api/search-products', [PurchaseController::class, 'searchProducts'])->name('purchases.search.products');

    Route::get('purchases',                 [PurchaseController::class, 'index'])->middleware('permission:view purchases')->name('purchases.index');
    Route::get('purchases/create',          [PurchaseController::class, 'create'])->middleware('permission:create purchases')->name('purchases.create');
    Route::post('purchases',                [PurchaseController::class, 'store'])->middleware('permission:create purchases')->name('purchases.store');
    Route::get('purchases/{purchase}',      [PurchaseController::class, 'show'])->middleware('permission:view purchases')->name('purchases.show');
    Route::get('purchases/{purchase}/edit', [PurchaseController::class, 'edit'])->middleware('permission:edit purchases')->name('purchases.edit');
    Route::put('purchases/{purchase}',      [PurchaseController::class, 'update'])->middleware('permission:edit purchases')->name('purchases.update');
    Route::patch('purchases/{purchase}',    [PurchaseController::class, 'update'])->middleware('permission:edit purchases');
    Route::delete('purchases/{purchase}',   [PurchaseController::class, 'destroy'])->middleware('permission:delete purchases')->name('purchases.destroy');

    // ── Sales ─────────────────────────────────────────────────────────────────
    Route::prefix('sales-api')->group(function () {
        Route::get('/search-products',             [SaleController::class, 'searchProducts'])->name('sales.search.products');
        Route::post('/update-payment-status/{id}', [SaleController::class, 'updatePaymentStatus']);
        Route::get('/sales/daily-report',          [SaleController::class, 'dailyReport'])->name('sales.daily-report');
    });
    Route::get('/sales-api/search-by-imei', [SaleController::class, 'searchByImei'])->name('sales.search-by-imei');

    Route::get('sales',              [SaleController::class, 'index'])->middleware('permission:view sales')->name('sales.index');
    Route::get('sales/create',       [SaleController::class, 'create'])->middleware('permission:create sales')->name('sales.create');
    Route::post('sales',             [SaleController::class, 'store'])->middleware('permission:create sales')->name('sales.store');
    Route::get('sales/{sale}',       [SaleController::class, 'show'])->middleware('permission:view sales')->name('sales.show');
    Route::get('sales/{sale}/edit',  [SaleController::class, 'edit'])->middleware('permission:edit sales')->name('sales.edit');
    Route::put('sales/{sale}',       [SaleController::class, 'update'])->middleware('permission:edit sales')->name('sales.update');
    Route::patch('sales/{sale}',     [SaleController::class, 'update'])->middleware('permission:edit sales');
    Route::delete('sales/{sale}',    [SaleController::class, 'destroy'])->middleware('permission:delete sales')->name('sales.destroy');

    // ── Exchange Rates ────────────────────────────────────────────────────────
    Route::get('exchange-rates',            [ExchangeRateController::class, 'index'])->name('exchange-rates.index');
    Route::get('exchange-rates/{currency}', [ExchangeRateController::class, 'show'])->name('exchange-rates.show');

    // ── Stock Movements ───────────────────────────────────────────────────────
    Route::middleware('role:admin')->group(function () {
        Route::get('stock-movements',             [StockMovementController::class, 'index'])->name('stock-movements.index');
        Route::get('stock-movements/export/pdf',  [StockMovementController::class, 'exportPdf'])->name('stock-movements.export.pdf');
        Route::get('stock-movements/export/xlsx', [StockMovementController::class, 'exportXlsx'])->name('stock-movements.export.xlsx');
        Route::get('stock-movements/report',      [StockMovementController::class, 'report'])->name('stock-movements.report');
    });

    // ── Debts ─────────────────────────────────────────────────────────────────
    Route::middleware('role:admin')->group(function () {
        Route::get('debts',                    [DebtController::class, 'index'])->name('debts.index');
        Route::get('debts/create',             [DebtController::class, 'create'])->name('debts.create');
        Route::post('debts',                   [DebtController::class, 'store'])->name('debts.store');
        Route::get('debts/{debt}',             [DebtController::class, 'show'])->name('debts.show');
        Route::get('debts/{debt}/edit',        [DebtController::class, 'edit'])->name('debts.edit');
        Route::put('debts/{debt}',             [DebtController::class, 'update'])->name('debts.update');
        Route::patch('debts/{debt}',           [DebtController::class, 'update']);
        Route::delete('debts/{debt}',          [DebtController::class, 'destroy'])->name('debts.destroy');
        Route::post('debts/{debt}/add-payment', [DebtController::class, 'addPayment'])->name('debts.add-payment');
    });

    // ── Online Orders ─────────────────────────────────────────────────────────
    Route::get('online-orders',                    [OnlineOrderController::class, 'index'])->middleware('permission:view orders')->name('online-orders.index');
    Route::get('online-orders/create',             [OnlineOrderController::class, 'create'])->middleware('permission:create orders')->name('online-orders.create');
    Route::post('online-orders',                   [OnlineOrderController::class, 'store'])->middleware('permission:create orders')->name('online-orders.store');
    Route::get('online-orders/{onlineOrder}',      [OnlineOrderController::class, 'show'])->middleware('permission:view orders')->name('online-orders.show');
    Route::get('online-orders/{onlineOrder}/edit', [OnlineOrderController::class, 'edit'])->middleware('permission:edit orders')->name('online-orders.edit');
    Route::put('online-orders/{onlineOrder}',      [OnlineOrderController::class, 'update'])->middleware('permission:edit orders')->name('online-orders.update');
    Route::patch('online-orders/{onlineOrder}',    [OnlineOrderController::class, 'update'])->middleware('permission:edit orders');
    Route::delete('online-orders/{onlineOrder}',   [OnlineOrderController::class, 'destroy'])->middleware('permission:delete orders')->name('online-orders.destroy');
    Route::post('online-orders/{onlineOrder}/mark-paid',   [OnlineOrderController::class, 'markAsPaid'])->middleware('permission:edit orders')->name('online-orders.mark-paid');
    Route::post('online-orders/{onlineOrder}/mark-unpaid', [OnlineOrderController::class, 'markAsUnpaid'])->middleware('permission:edit orders')->name('online-orders.mark-unpaid');

    // ── Seller Bonuses ────────────────────────────────────────────────────────
    Route::get('seller-bonuses',                        [SellerBonusController::class, 'index'])->middleware('permission:view seller-bonuses')->name('seller-bonuses.index');
    Route::get('seller-bonuses/create',                 [SellerBonusController::class, 'create'])->middleware('permission:create seller-bonuses')->name('seller-bonuses.create');
    Route::post('seller-bonuses',                       [SellerBonusController::class, 'store'])->middleware('permission:create seller-bonuses')->name('seller-bonuses.store');
    Route::get('seller-bonuses/{sellerBonus}',          [SellerBonusController::class, 'show'])->middleware('permission:view seller-bonuses')->name('seller-bonuses.show');
    Route::get('seller-bonuses/{sellerBonus}/edit',     [SellerBonusController::class, 'edit'])->middleware('permission:edit seller-bonuses')->name('seller-bonuses.edit');
    Route::put('seller-bonuses/{sellerBonus}',          [SellerBonusController::class, 'update'])->middleware('permission:edit seller-bonuses')->name('seller-bonuses.update');
    Route::patch('seller-bonuses/{sellerBonus}',        [SellerBonusController::class, 'update'])->middleware('permission:edit seller-bonuses');
    Route::delete('seller-bonuses/{sellerBonus}',       [SellerBonusController::class, 'destroy'])->middleware('permission:delete seller-bonuses')->name('seller-bonuses.destroy');
    Route::post('seller-bonuses/calculate',             [SellerBonusController::class, 'calculateBonus'])->middleware('permission:create seller-bonuses')->name('seller-bonuses.calculate');
    Route::get('seller-bonuses/seller-report/{seller}', [SellerBonusController::class, 'sellerReport'])->middleware('permission:view seller-bonuses')->name('seller-bonuses.seller-report');

    // ── Permission Manager ────────────────────────────────────────────────────
    Route::middleware('role:admin')->prefix('admin/permissions')->name('admin.permissions.')->group(function () {
        Route::get('/',                  [PermissionManagerController::class, 'index'])->name('index');
        Route::get('/users/{user}/edit', [PermissionManagerController::class, 'edit'])->name('edit');
        Route::put('/users/{user}',      [PermissionManagerController::class, 'update'])->name('update');
    });

    Route::get('/global-search', [App\Http\Controllers\GlobalSearchController::class, 'search'])->name('global.search');
});
