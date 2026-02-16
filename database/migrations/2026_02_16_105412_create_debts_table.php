<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('debts', function (Blueprint $table) {
            $table->id();
            $table->string('debt_number')->unique(); // Numri i borxhit
            $table->foreignId('supplier_id')->constrained('partners')->onDelete('cascade'); // Furnizuesi
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade'); // Magazina
            $table->foreignId('currency_id')->constrained('currencies')->onDelete('cascade');
            $table->decimal('total_amount', 15, 2); // Shuma totale
            $table->decimal('paid_amount', 15, 2)->default(0); // Shuma e paguar
            $table->decimal('remaining_amount', 15, 2); // Shuma e mbetur
            $table->date('debt_date'); // Data e borxhit
            $table->date('due_date')->nullable(); // Afati i pagesës
            $table->enum('status', ['pending', 'partial', 'paid'])->default('pending'); // Statusi
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debts');
    }
};
