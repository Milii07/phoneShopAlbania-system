<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Debt extends Model
{
    protected $fillable = [
        'debt_number',
        'supplier_id',
        'warehouse_id',
        'currency_id',
        'total_amount',
        'paid_amount',
        'remaining_amount',
        'debt_date',
        'due_date',
        'status',
        'description',
        'notes',
    ];

    protected $casts = [
        'debt_date' => 'date',
        'due_date' => 'date',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
    ];

    // Relationships
    public function supplier()
    {
        return $this->belongsTo(Partner::class, 'supplier_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function payments()
    {
        return $this->hasMany(DebtPayment::class);
    }

    // Attributes
    public function getIsOverdueAttribute()
    {
        if (!$this->due_date || $this->status === 'paid') {
            return false;
        }
        return Carbon::today()->greaterThan($this->due_date);
    }

    public function getIsDueSoonAttribute()
    {
        if (!$this->due_date || $this->status === 'paid') {
            return false;
        }
        $daysUntilDue = Carbon::today()->diffInDays($this->due_date, false);
        return $daysUntilDue >= 0 && $daysUntilDue <= 7; // 7 ditë para afatit
    }

    // Generate debt number
    public static function generateDebtNumber()
    {
        $lastDebt = self::latest('id')->first();
        $number = $lastDebt ? intval(substr($lastDebt->debt_number, 4)) + 1 : 1;
        return 'DBT-' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    // Update status based on payments
    public function updateStatus()
    {
        if ($this->remaining_amount <= 0) {
            $this->status = 'paid';
        } elseif ($this->paid_amount > 0) {
            $this->status = 'partial';
        } else {
            $this->status = 'pending';
        }
        $this->save();
    }
}
