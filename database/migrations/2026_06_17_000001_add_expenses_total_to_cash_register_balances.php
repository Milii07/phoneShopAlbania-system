<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_register_balances', function (Blueprint $table) {
            if (!Schema::hasColumn('cash_register_balances', 'expenses_total')) {
                $table->decimal('expenses_total', 15, 2)->default(0)->after('sales_total');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cash_register_balances', function (Blueprint $table) {
            if (Schema::hasColumn('cash_register_balances', 'expenses_total')) {
                $table->dropColumn('expenses_total');
            }
        });
    }
};
