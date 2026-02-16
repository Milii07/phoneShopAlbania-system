<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Str;

class OnlineOrder extends Model
{
    protected $fillable = [
        'sale_id',
        'order_number',
        'partner_id',
        'warehouse_id',
        'currency_id',
        'order_amount',
        'order_date',
        'expected_payment_date',
        'is_paid',
        'payment_received_date',
        'payment_method',
        'delivery_address',
        'notes',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_payment_date' => 'date',
        'payment_received_date' => 'date',
        'order_amount' => 'decimal:2',
        'is_paid' => 'boolean',
    ];

    // Relationships
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

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

    // Attributes
    public function getIsOverdueAttribute()
    {
        if ($this->is_paid || !$this->expected_payment_date) {
            return false;
        }
        return Carbon::today()->greaterThan($this->expected_payment_date);
    }

    public function getIsDueSoonAttribute()
    {
        if ($this->is_paid || !$this->expected_payment_date) {
            return false;
        }
        $daysUntilDue = Carbon::today()->diffInDays($this->expected_payment_date, false);
        return $daysUntilDue >= 0 && $daysUntilDue <= 7;
    }

    // Generate order number
    public static function generateOrderNumber()
    {
        return 'ONL-' . now()->format('His') . '-' . strtoupper(Str::random(4));
    }
}
