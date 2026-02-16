<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('online_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade'); // Lidhja me shitjen
            $table->string('order_number')->unique();
            $table->foreignId('partner_id')->constrained('partners')->onDelete('cascade'); // Klienti
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->foreignId('currency_id')->constrained('currencies')->onDelete('cascade');
            $table->decimal('order_amount', 15, 2); // Shuma e porosisë
            $table->date('order_date'); // Data e porosisë
            $table->date('expected_payment_date')->nullable(); // Data e pritshme e pagesës
            $table->boolean('is_paid')->default(false); // A është paguar nga posta?
            $table->date('payment_received_date')->nullable(); // Data kur u mor pagesa
            $table->enum('payment_method', ['Cash', 'Bank'])->nullable(); // Si u bë pagesa
            $table->text('delivery_address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('online_orders');
    }
};
