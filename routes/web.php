<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\GaranciController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ExchangeRateController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\OnlineOrderController;
use App\Http\Controllers\SellerBonusController;



Route::get('/', function () {
    return redirect()->route('dashboard');
});

require __DIR__ . '/auth.php';
Route::middleware(['auth', 'verified', 'check.user.access'])->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard.index');
    })->name('dashboard');

    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/', [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });

    Route::resource('warehouses', WarehouseController::class);

    Route::resource('categories', CategoryController::class);

    Route::resource('brands', BrandController::class);

    Route::resource('products', ProductController::class);

    Route::get('/api/warehouses', [ProductController::class, 'getWarehouses'])->name('api.warehouses');
    Route::get('/api/categories', [ProductController::class, 'getCat egories'])->name('api.categories');
    Route::get('/api/brands', [ProductController::class, 'getBrands'])->name('api.brands');
    Route::resource('currencies', CurrencyController::class);
    Route::get('partners/search', [PartnerController::class, 'search'])->name('partners.search');
    Route::resource('partners', PartnerController::class);
    Route::resource('sellers', SellerController::class);


    Route::post('purchase/extract-pdf',   [PurchaseController::class, 'extractPdf'])->name('purchases.extract-pdf');

    Route::post('purchase/extract-image', [PurchaseController::class, 'extractImage'])->name('purchases.extract-image');

    Route::resource('purchases', PurchaseController::class);

    Route::get('purchases-api/search-products', [PurchaseController::class, 'searchProducts'])->name('purchases.search.products');

    Route::resource('sales', SaleController::class);

    Route::prefix('sales-api')->group(function () {
        Route::get('/search-products', [SaleController::class, 'searchProducts'])->name('sales.search.products');
        Route::post('/update-payment-status/{id}', [SaleController::class, 'updatePaymentStatus']);
        Route::get('/sales/daily-report', [SaleController::class, 'dailyReport'])->name('sales.daily-report');
    });

    Route::get('exchange-rates', [ExchangeRateController::class, 'index'])->name('exchange-rates.index');
    Route::get('exchange-rates/{currency}', [ExchangeRateController::class, 'show'])->name('exchange-rates.show');

    // Stock Movements Routes
    Route::resource('stock-movements', StockMovementController::class)->only([
        'index',
    ]);
    Route::get('stock-movements/export/pdf', [StockMovementController::class, 'exportPdf'])->name('stock-movements.export.pdf');
    Route::get('stock-movements/export/xlsx', [StockMovementController::class, 'exportXlsx'])->name('stock-movements.export.xlsx');
    Route::get('stock-movements/report', [StockMovementController::class, 'report'])->name('stock-movements.report');

    // Debts Routes
    Route::resource('debts', DebtController::class);
    Route::post('debts/{debt}/add-payment', [DebtController::class, 'addPayment'])->name('debts.add-payment');


    Route::resource('online-orders', OnlineOrderController::class);
    Route::post('online-orders/{onlineOrder}/mark-paid', [OnlineOrderController::class, 'markAsPaid'])->name('online-orders.mark-paid');
    Route::post('online-orders/{onlineOrder}/mark-unpaid', [OnlineOrderController::class, 'markAsUnpaid'])->name('online-orders.mark-unpaid');

    // Seller Bonuses Routes
    Route::resource('seller-bonuses', SellerBonusController::class);
    Route::post('seller-bonuses/calculate', [SellerBonusController::class, 'calculateBonus'])->name('seller-bonuses.calculate');
    Route::get('seller-bonuses/seller-report/{seller}', [SellerBonusController::class, 'sellerReport'])->name('seller-bonuses.seller-report');


    Route::get('/sales-api/search-by-imei', [SaleController::class, 'searchByImei'])->name('sales.search-by-imei');
});
