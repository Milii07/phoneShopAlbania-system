<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'product_id',
        'product_name',
        'storage',
        'ram',
        'color',
        'quantity',
        'unit_type',
        'unit_cost',
        'discount',
        'tax',
        'line_total',
        'imei_numbers',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'line_total' => 'decimal:2',
        'imei_numbers' => 'array'
    ];

    // Relacionet
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getImeiString()
    {
        return $this->imei_numbers ? implode(', ', $this->imei_numbers) : '';
    }
}
