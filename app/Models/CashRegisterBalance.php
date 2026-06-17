<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashRegisterBalance extends Model
{
    protected $fillable = [
        'daily_cash_register_id',
        'currency_id',
        'opening_balance',
        'closing_balance',
        'sales_total',
        'expenses_total',
        'adjustments_total',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'closing_balance' => 'decimal:2',
        'sales_total' => 'decimal:2',
        'expenses_total' => 'decimal:2',
        'adjustments_total' => 'decimal:2',
    ];

    /**
     * Get the daily cash register
     */
    public function dailyCashRegister(): BelongsTo
    {
        return $this->belongsTo(DailyCashRegister::class);
    }

    /**
     * Get the currency
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Get all transactions for this balance
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(CashTransaction::class, 'daily_cash_register_id', 'daily_cash_register_id')
            ->where('currency_id', $this->currency_id);
    }

    /**
     * Calculate difference (what should be vs what is)
     */
    public function getDifference(): float
    {
        return $this->closing_balance - $this->opening_balance;
    }

    /**
     * Check if balanced
     */
    public function isBalanced(float $declaredAmount): bool
    {
        return abs($this->closing_balance - $declaredAmount) < 0.01;
    }
}
