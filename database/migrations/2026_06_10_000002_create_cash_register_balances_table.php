<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cash_register_balances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('daily_cash_register_id')->index();
            $table->unsignedBigInteger('currency_id')->index();
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('closing_balance', 15, 2)->default(0);
            $table->decimal('sales_total', 15, 2)->default(0);
            $table->decimal('adjustments_total', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('daily_cash_register_id')->references('id')->on('daily_cash_registers')->onDelete('cascade');
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('restrict');
            $table->unique(['daily_cash_register_id', 'currency_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_register_balances');
    }
};
