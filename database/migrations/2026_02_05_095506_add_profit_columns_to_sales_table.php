<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('profit_total', 10, 2)->default(0)->after('total_amount');
            $table->decimal('owner_profit', 10, 2)->default(0)->after('profit_total');
        });
    }

    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['profit_total', 'owner_profit']);
        });
    }
};
