<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_cash_registers', function (Blueprint $table) {
            if (!Schema::hasColumn('daily_cash_registers', 'total_expenses')) {
                $table->decimal('total_expenses', 15, 2)->default(0)->after('total_transactions');
            }
        });
    }

    public function down(): void
    {
        Schema::table('daily_cash_registers', function (Blueprint $table) {
            if (Schema::hasColumn('daily_cash_registers', 'total_expenses')) {
                $table->dropColumn('total_expenses');
            }
        });
    }
};
