<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerBonus extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'period_start',
        'period_end',
        'phone_sales_total',
        'accessory_sales_total',
        'phone_bonus_percentage',
        'accessory_bonus_percentage',
        'phone_bonus_amount',
        'accessory_bonus_amount',
        'total_bonus',
        'total_sales_count',
        'notes',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'phone_sales_total' => 'decimal:2',
        'accessory_sales_total' => 'decimal:2',
        'phone_bonus_percentage' => 'decimal:2',
        'accessory_bonus_percentage' => 'decimal:2',
        'phone_bonus_amount' => 'decimal:2',
        'accessory_bonus_amount' => 'decimal:2',
        'total_bonus' => 'decimal:2',
    ];

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }
}
