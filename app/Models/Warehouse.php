<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ===== RELATIONSHIPS =====

    // Many-to-Many me Products (E RE)
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_warehouse')
            ->withPivot('quantity')
            ->withTimestamps();
    }

    // ===== HELPER METHODS =====

    /**
     * Merr vetëm produktet me stok > 0
     */
    public function getAvailableProductsAttribute()
    {
        return $this->products()
            ->wherePivot('quantity', '>', 0)
            ->get();
    }

    /**
     * Total lloje produktësh (unique products)
     */
    public function getTotalProductTypesAttribute()
    {
        return $this->products()->count();
    }

    /**
     * Total copë (sum of all quantities)
     */
    public function getTotalItemsAttribute()
    {
        return $this->products()->sum('product_warehouse.quantity');
    }

    /**
     * Produktet që po mbarojnë (quantity < threshold)
     */
    public function getLowStockProducts($threshold = 5)
    {
        return $this->products()
            ->wherePivot('quantity', '<=', $threshold)
            ->wherePivot('quantity', '>', 0)
            ->get();
    }

    /**
     * Produktet që janë out of stock
     */
    public function getOutOfStockProductsAttribute()
    {
        return $this->products()
            ->wherePivot('quantity', '=', 0)
            ->get();
    }
}
