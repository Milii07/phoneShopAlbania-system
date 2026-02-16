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
        'seller_id',
        'warehouse_id',
        'currency_id',
        'sale_status',
        'payment_status',
        'payment_method',
        'purchase_location',
        'payment_term',
        'subtotal',
        'tax',
        'discount',
        'total_amount',
        'profit_total',
        'owner_profit',
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
        'profit_total' => 'decimal:2',
        'owner_profit' => 'decimal:2',
    ];

    // RELATIONSHIPS
    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function seller()
    {
        return $this->belongsTo(Seller::class);
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

    // GENERATE INVOICE NUMBER
    public static function generateInvoiceNumber()
    {
        $lastSale = self::latest('id')->first();
        $number = $lastSale ? intval(substr($lastSale->invoice_number, 3)) + 1 : 1;
        return 'INV' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    // RECALCULATE PROFIT FROM ITEMS
    public function recalculateProfit()
    {
        $profitTotal = $this->items->sum('profit_total');
        $ownerProfit = $this->items->sum('owner_profit');

        $this->update([
            'profit_total' => $profitTotal,
            'owner_profit' => $ownerProfit,
        ]);

        return $this;
    }
    public function onlineOrder()
    {
        return $this->hasOne(OnlineOrder::class);
    }
}
