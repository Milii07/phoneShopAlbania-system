<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seller_bonuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('sellers')->onDelete('cascade');
            $table->date('period_start'); // Fillimi i periudhës (muaji)
            $table->date('period_end'); // Mbarimi i periudhës
            $table->decimal('phone_sales_total', 15, 2)->default(0); // Xhiro nga telefonat
            $table->decimal('accessory_sales_total', 15, 2)->default(0); // Xhiro nga aksesorët
            $table->decimal('phone_bonus_percentage', 5, 2)->default(0); // % bonusi për telefona
            $table->decimal('accessory_bonus_percentage', 5, 2)->default(0); // % bonusi për aksesorë
            $table->decimal('phone_bonus_amount', 15, 2)->default(0); // Shuma e bonusit nga telefonat
            $table->decimal('accessory_bonus_amount', 15, 2)->default(0); // Shuma e bonusit nga aksesorët
            $table->decimal('total_bonus', 15, 2)->default(0); // Bonusi total
            $table->integer('total_sales_count')->default(0); // Numri i shitjeve totale
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_bonuses');
    }
};
