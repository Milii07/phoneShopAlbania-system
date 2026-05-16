<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\SaleItem;
use App\Models\PurchaseItem;
use App\Observers\SaleItemObserver;
use App\Observers\PurchaseItemObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register observers for product history tracking
        SaleItem::observe(SaleItemObserver::class);
        PurchaseItem::observe(PurchaseItemObserver::class);
    }
}
