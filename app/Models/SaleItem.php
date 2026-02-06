<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'product_name',
        'warehouse_id',
        'category_id',
        'brand_id',
        'storage',
        'ram',
        'color',
        'quantity',
        'unit_type',
        'unit_price',
        'purchase_price',
        'sale_price',
        'discount',
        'tax',
        'line_total',
        'profit_total',
        'owner_profit',
        'imei_numbers',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'line_total' => 'decimal:2',
        'profit_total' => 'decimal:2',
        'owner_profit' => 'decimal:2',
        'imei_numbers' => 'array',
    ];

    // RELATIONSHIPS
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
}
