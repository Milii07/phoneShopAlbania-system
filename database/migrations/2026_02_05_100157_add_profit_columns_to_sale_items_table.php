<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sale_items', function (Blueprint $table) {
            // Shto kolonat për llogaritjen e fitimit
            if (!Schema::hasColumn('sale_items', 'purchase_price')) {
                $table->decimal('purchase_price', 15, 2)->default(0)->after('unit_price');
            }
            if (!Schema::hasColumn('sale_items', 'sale_price')) {
                $table->decimal('sale_price', 15, 2)->default(0)->after('purchase_price');
            }
            if (!Schema::hasColumn('sale_items', 'profit_total')) {
                $table->decimal('profit_total', 15, 2)->default(0)->after('line_total');
            }
            if (!Schema::hasColumn('sale_items', 'owner_profit')) {
                $table->decimal('owner_profit', 15, 2)->default(0)->after('profit_total');
            }
        });
    }

    public function down()
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn(['purchase_price', 'sale_price', 'profit_total', 'owner_profit']);
        });
    }
};
