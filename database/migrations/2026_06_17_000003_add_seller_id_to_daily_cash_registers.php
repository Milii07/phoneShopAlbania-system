<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_cash_registers', function (Blueprint $table) {
            if (!Schema::hasColumn('daily_cash_registers', 'seller_id')) {
                $table->unsignedBigInteger('seller_id')->nullable()->after('employee_id');
                $table->foreign('seller_id')->references('id')->on('sellers')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('daily_cash_registers', function (Blueprint $table) {
            if (Schema::hasColumn('daily_cash_registers', 'seller_id')) {
                $table->dropForeign(['seller_id']);
                $table->dropColumn('seller_id');
            }
        });
    }
};
