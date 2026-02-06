<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            // Hiq foreign key constraint
            $table->dropForeign(['warehouse_id']);

            // Hiq kolonat
            $table->dropColumn(['warehouse_id', 'quantity']);
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('warehouse_id')->nullable()->constrained();
            $table->integer('quantity')->default(0);
        });
    }
};
