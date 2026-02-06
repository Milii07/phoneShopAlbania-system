<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    public function up()
    {
        // Merr të gjitha produktet që kanë warehouse_id
        $products = DB::table('products')
            ->whereNotNull('warehouse_id')
            ->get();

        $now = now();
        $data = [];

        foreach ($products as $product) {
            $data[] = [
                'product_id' => $product->id,
                'warehouse_id' => $product->warehouse_id,
                'quantity' => $product->quantity ?? 0,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Bulk insert për performance më të mirë
        if (!empty($data)) {
            DB::table('product_warehouse')->insert($data);
        }

        // Log për verifikim
        Log::info('Migruar ' . count($data) . ' produkte në product_warehouse table');
    }

    public function down()
    {
        DB::table('product_warehouse')->truncate();
    }
};
