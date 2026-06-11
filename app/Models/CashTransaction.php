<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashTransaction extends Model
{
    protected $fillable = [
        'daily_cash_register_id',
        'currency_id',
        'type',
        'amount',
        'description',
        'reference_type',
        'reference_id',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
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
     * Get the user who created this transaction
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the sale if this transaction is from a sale
     */
    public function getSale()
    {
        if ($this->reference_type === 'Sale' && $this->reference_id) {
            return Sale::find($this->reference_id);
        }
        return null;
    }

    /**
     * Get transaction type badge
     */
    public function getTypeBadgeClass(): string
    {
        return match($this->type) {
            'income' => 'bg-success',
            'expense' => 'bg-danger',
            'adjustment' => 'bg-warning',
            default => 'bg-secondary',
        };
    }

    /**
     * Get transaction type label
     */
    public function getTypeLabel(): string
    {
        return match($this->type) {
            'income' => 'Shitje',
            'expense' => 'Shpenzim',
            'adjustment' => 'Rregullim',
            default => 'E panjohur',
        };
    }
}
