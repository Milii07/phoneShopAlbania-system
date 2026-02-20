<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'brand_id',
        'name',
        'unit_price',
        'purchase_price',
        'selling_price',
        'currency_id',
        'storage',
        'ram',
        'color',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ===== RELATIONSHIPS =====

    // Many-to-Many me Warehouses
    public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class, 'product_warehouse')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    // ===== HELPER METHODS =====

    /**
     * Merr total quantity në të gjitha warehouses
     */
    public function getTotalQuantityAttribute()
    {
        return $this->warehouses()->sum('product_warehouse.quantity');
    }

    /**
     * Merr quantity në një warehouse specifik
     */
    public function getQuantityInWarehouse($warehouseId)
    {
        $warehouse = $this->warehouses()->find($warehouseId);
        return $warehouse ? $warehouse->pivot->quantity : 0;
    }

    /**
     * Check nëse produkti ekziston në një warehouse
     */
    public function isInWarehouse($warehouseId)
    {
        return $this->warehouses()->where('warehouse_id', $warehouseId)->exists();
    }

    /**
     * Merr warehouses ku produkti ka stok > 0
     */
    public function getAvailableWarehousesAttribute()
    {
        return $this->warehouses()
            ->wherePivot('quantity', '>', 0)
            ->get();
    }

    /**
     * Shto stock në një warehouse
     */
    public function addStock($warehouseId, $quantity)
    {
        if ($this->isInWarehouse($warehouseId)) {
            // Update existing
            $currentQty = $this->getQuantityInWarehouse($warehouseId);
            $this->warehouses()->updateExistingPivot($warehouseId, [
                'quantity' => $currentQty + $quantity
            ]);
        } else {
            // Attach new
            $this->warehouses()->attach($warehouseId, ['quantity' => $quantity]);
        }
    }

    /**
     * Zvogëlo stock nga një warehouse
     */
    public function reduceStock($warehouseId, $quantity)
    {
        $currentQty = $this->getQuantityInWarehouse($warehouseId);

        if ($currentQty >= $quantity) {
            $newQty = $currentQty - $quantity;

            $this->warehouses()->updateExistingPivot($warehouseId, [
                'quantity' => $newQty
            ]);

            return true;
        }

        return false; // Nuk ka stok të mjaftueshëm
    }

    /**
     * Transfero stock nga një warehouse në tjetrin
     */
    public function transferStock($fromWarehouseId, $toWarehouseId, $quantity)
    {
        if ($this->reduceStock($fromWarehouseId, $quantity)) {
            $this->addStock($toWarehouseId, $quantity);
            return true;
        }

        return false;
    }

    /**
     * Llogarit fitimin total nga shitjet
     */
    public function getTotalProfitAttribute()
    {
        return $this->saleItems->sum('profit_total');
    }


    public function getProfitMarginAttribute()
    {
        if ($this->purchase_price > 0) {
            return (($this->unit_price - $this->purchase_price) / $this->purchase_price) * 100;
        }
        return 0;
    }
}
