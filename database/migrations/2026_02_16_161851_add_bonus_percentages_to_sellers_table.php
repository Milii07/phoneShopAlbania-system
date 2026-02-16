<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->decimal('phone_bonus_percentage', 5, 2)->default(0)->after('age');
            $table->decimal('accessory_bonus_percentage', 5, 2)->default(0)->after('phone_bonus_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->dropColumn(['phone_bonus_percentage', 'accessory_bonus_percentage']);
        });
    }
};
