<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\SaleController;


Route::get('/', function () {
    return view('welcome');
});

require __DIR__ . '/auth.php';
Route::middleware(['auth', 'verified'])->group(function () {

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



    Route::resource('purchases', PurchaseController::class);
    Route::get('purchases-api/search-products', [PurchaseController::class, 'searchProducts'])->name('purchases.search.products');

    Route::resource('sales', SaleController::class);

    Route::prefix('sales-api')->group(function () {
        Route::get('/search-products', [SaleController::class, 'searchProducts']);
        Route::post('/update-payment-status/{id}', [SaleController::class, 'updatePaymentStatus']);
    });
});
