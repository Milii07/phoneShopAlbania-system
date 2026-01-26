<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'invoice_date',
        'delivery_date',
        'due_date',
        'partner_id',
        'warehouse_id',
        'currency_id',
        'sale_status',
        'payment_status',
        'payment_method',
        'payment_term',
        'subtotal',
        'tax',
        'discount',
        'total_amount',
        'description',
        'notes',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'delivery_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

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
        return $this->hasMany(SaleItem::class);
    }

    public static function generateInvoiceNumber()
    {
        $lastSale = self::latest('id')->first();
        $number = $lastSale ? intval(substr($lastSale->invoice_number, 3)) + 1 : 1;
        return 'INV' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }
}
