<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class DailyCashRegister extends Model
{
    protected $fillable = [
        'register_date',
        'employee_id',
        'seller_id',
        'notes',
        'status',
        'total_opening',
        'total_closing',
        'total_transactions',
        'total_expenses',
        'total_adjustments',
    ];

    protected $casts = [
        'register_date' => 'date',
        'total_opening' => 'decimal:2',
        'total_closing' => 'decimal:2',
        'total_transactions' => 'decimal:2',
        'total_expenses' => 'decimal:2',
        'total_adjustments' => 'decimal:2',
    ];

    /**
     * Get the employee who registered the cash
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    /**
     * Get the seller assigned to this register
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Seller::class, 'seller_id');
    }

    /**
     * Get all balances for this register
     */
    public function balances(): HasMany
    {
        return $this->hasMany(CashRegisterBalance::class);
    }

    /**
     * Get all transactions for this register
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(CashTransaction::class);
    }

    /**
     * Get today's cash register
     */
    public static function today()
    {
        return static::where('register_date', Carbon::today())->first();
    }

    /**
     * Create or get today's cash register, with balances for every currency
     */
    public static function todayOrCreate()
    {
        $register = static::firstOrCreate([
            'register_date' => Carbon::today(),
        ], [
            'status' => 'open',
        ]);

        $register->ensureBalances();

        return $register;
    }

    /**
     * Ensure a balance row exists for every active currency
     */
    public function ensureBalances(): void
    {
        $currencies = Currency::all();
        foreach ($currencies as $currency) {
            $this->balances()->firstOrCreate(
                ['currency_id' => $currency->id],
                [
                    'opening_balance'  => 0,
                    'closing_balance'  => 0,
                    'sales_total'      => 0,
                    'expenses_total'   => 0,
                    'adjustments_total'=> 0,
                ]
            );
        }
    }

    /**
     * Calculate total by currency
     */
    public function getBalance(Currency $currency)
    {
        return $this->balances()->where('currency_id', $currency->id)->first();
    }

    /**
     * Add transaction (sale, expense, adjustment)
     */
    public function addTransaction(
        Currency $currency,
        string $type,
        float $amount,
        ?string $description = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?int $userId = null
    ): CashTransaction {
        // Get or create balance
        $balance = $this->balances()
            ->where('currency_id', $currency->id)
            ->first();

        if (!$balance) {
            $balance = $this->balances()->create([
                'currency_id' => $currency->id,
                'opening_balance' => 0,
                'closing_balance' => 0,
            ]);
        }

        // Create transaction
        $transaction = $this->transactions()->create([
            'currency_id' => $currency->id,
            'type' => $type,
            'amount' => $amount,
            'description' => $description,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'created_by' => $userId ?? auth()->id(),
        ]);

        // Update balance
        $this->updateBalance($currency);

        return $transaction;
    }

    /**
     * Update balance totals for a currency
     */
    public function updateBalance(Currency $currency): void
    {
        $balance = $this->balances()->where('currency_id', $currency->id)->first();

        if (!$balance) {
            return;
        }

        $transactions = $this->transactions()
            ->where('currency_id', $currency->id)
            ->get();

        $totalIncome = $transactions->where('type', 'income')->sum('amount');
        $totalExpense = $transactions->where('type', 'expense')->sum('amount');
        $totalAdjustment = $transactions->where('type', 'adjustment')->sum('amount');

        $balance->sales_total = $totalIncome;
        $balance->expenses_total = $totalExpense;
        $balance->adjustments_total = $totalAdjustment;
        $balance->closing_balance = $balance->opening_balance + $totalIncome - $totalExpense + $totalAdjustment;
        $balance->save();

        // Update daily register totals
        $this->recalculateTotals();
    }

    /**
     * Recalculate all totals
     */
    public function recalculateTotals(): void
    {
        $this->total_opening = $this->balances()->sum('opening_balance');
        $this->total_closing = $this->balances()->sum('closing_balance');
        $this->total_transactions = $this->balances()->sum('sales_total');
        $this->total_expenses = $this->balances()->sum('expenses_total');
        $this->total_adjustments = $this->balances()->sum('adjustments_total');
        $this->save();
    }

    /**
     * Close the cash register
     */
    public function close(): bool
    {
        if ($this->status === 'closed') {
            return false;
        }

        $this->status = 'closed';
        $this->save();

        return true;
    }

    /**
     * Balance the cash register
     */
    public function balance(): bool
    {
        if ($this->status === 'balanced') {
            return false;
        }

        $this->status = 'balanced';
        $this->save();

        return true;
    }
}
