<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            // Shto vetëm kolonat e reja
            // Kolona 'price' ekziston tashmë dhe do të jetë çmimi i blerjes (purchase price)
            $table->decimal('sale_price', 10, 2)->default(0)->after('quantity');
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('sale_price');
        });
    }
};
