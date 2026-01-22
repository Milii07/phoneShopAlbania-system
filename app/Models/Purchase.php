<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_number',
        'purchase_date',
        'due_date',
        'partner_id',
        'warehouse_id',
        'currency_id',
        'order_status',
        'payment_status',
        'payment_method',
        'subtotal',
        'tax',
        'discount',
        'total_amount',
        'notes',
        'attachment',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    // Relacionet
    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    // Generate Purchase Number
    public static function generatePurchaseNumber()
    {
        $lastPurchase = self::latest('id')->first();
        $lastNumber = $lastPurchase ? intval(substr($lastPurchase->purchase_number, 3)) : 0;
        return 'PUR' . str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
    }
}
