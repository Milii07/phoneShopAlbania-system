<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'product_name',
        'warehouse_id',
        'category_id',
        'brand_id',
        'storage',
        'ram',
        'color',
        'quantity',
        'unit_type',
        'unit_price',
        'purchase_price',    // E RE
        'sale_price',        // E RE
        'discount',
        'tax',
        'line_total',
        'profit_total',      // E RE
        'owner_profit',      // E RE
        'imei_numbers',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'line_total' => 'decimal:2',
        'profit_total' => 'decimal:2',
        'owner_profit' => 'decimal:2',
        'imei_numbers' => 'array',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    // EVENTS - Llogarit fitimin automatikisht
    protected static function booted()
    {
        static::creating(function ($saleItem) {
            $saleItem->calculateProfit();
        });

        static::updating(function ($saleItem) {
            $saleItem->calculateProfit();
        });

        static::saved(function ($saleItem) {
            // Përditëso fitimin total të shitjes
            if ($saleItem->sale) {
                $saleItem->sale->calculateProfit();
            }
        });
    }

    // LLOGARIT FITIMIN
    public function calculateProfit()
    {
        // Nëse nuk ka purchase_price, merr nga produkti
        if (!$this->purchase_price && $this->product) {
            $this->purchase_price = $this->product->price;
        }

        // Nëse nuk ka sale_price, përdor unit_price
        if (!$this->sale_price) {
            $this->sale_price = $this->unit_price;
        }

        // Llogarit fitimin total për këtë artikull (pas zbritjes dhe taksave)
        $netSalePrice = $this->sale_price - $this->discount + $this->tax;
        $this->profit_total = ($netSalePrice - $this->purchase_price) * $this->quantity;

        // Merr përqindjen e fitimit nga warehouse
        if ($this->sale && $this->sale->warehouse) {
            $profitPercentage = $this->sale->warehouse->profit_percentage;
        } else {
            $profitPercentage = 100; // Default 100% nëse nuk ka warehouse
        }

        // Llogarit fitimin tuaj
        $this->owner_profit = $this->profit_total * ($profitPercentage / 100);

        return $this;
    }
}
