<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('product_warehouse', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->integer('quantity')->default(0);
            $table->timestamps();

            // Një produkt mund të jetë vetëm një herë në një warehouse
            $table->unique(['product_id', 'warehouse_id']);

            // Indexes për performance
            $table->index('product_id');
            $table->index('warehouse_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_warehouse');
    }
};
