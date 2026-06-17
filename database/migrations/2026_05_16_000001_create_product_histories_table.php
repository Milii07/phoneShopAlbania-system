<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('product_histories')) {
            return;
        }

        Schema::create('product_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_from_id')->nullable()->constrained('warehouses')->onDelete('set null');
            $table->foreignId('warehouse_to_id')->nullable()->constrained('warehouses')->onDelete('set null');
            $table->foreignId('user_id')->constrained()->onDelete('set null');
            $table->foreignId('partner_id')->nullable()->constrained()->onDelete('set null'); // Customer who bought
            $table->string('action_type'); // stock_entry, store_transfer, product_return, sale, sale_cancel, repair_service, stock_removal
            $table->integer('quantity')->default(1);
            $table->decimal('purchase_price', 12, 2)->nullable();
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->string('invoice_number')->nullable();
            $table->string('imei')->nullable(); // For tracking individual devices
            $table->string('sku')->nullable();
            $table->string('barcode')->nullable();
            $table->string('status')->default('active'); // active, returned, sold, repaired, removed
            $table->string('warranty')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index('product_id');
            $table->index('warehouse_from_id');
            $table->index('warehouse_to_id');
            $table->index('user_id');
            $table->index('partner_id');
            $table->index('action_type');
            $table->index('created_at');
            $table->index(['product_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_histories');
    }
};
