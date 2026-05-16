<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'warehouse_from_id',
        'warehouse_to_id',
        'user_id',
        'partner_id',
        'action_type',
        'quantity',
        'purchase_price',
        'sale_price',
        'invoice_number',
        'imei',
        'sku',
        'barcode',
        'status',
        'warranty',
        'notes',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ===== RELATIONSHIPS =====

    /**
     * Get the product
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the source warehouse (where product came from)
     */
    public function warehouseFrom()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_from_id');
    }

    /**
     * Get the destination warehouse (where product went to)
     */
    public function warehouseTo()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_to_id');
    }

    /**
     * Get the user who performed the action
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the customer (partner) who bought the product
     */
    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    // ===== SCOPES =====

    /**
     * Filter by action type
     */
    public function scopeByActionType($query, $actionType)
    {
        return $query->where('action_type', $actionType);
    }

    /**
     * Filter by product
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Filter by warehouse
     */
    public function scopeByWarehouse($query, $warehouseId, $direction = 'to')
    {
        $column = $direction === 'to' ? 'warehouse_to_id' : 'warehouse_from_id';
        return $query->where($column, $warehouseId);
    }

    /**
     * Filter by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate = null)
    {
        $query->whereDate('created_at', '>=', $startDate);
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }
        return $query;
    }

    /**
     * Filter by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // ===== HELPER METHODS =====

    /**
     * Get human-readable action type name
     */
    public function getActionTypeNameAttribute()
    {
        $actions = [
            'stock_entry' => 'Hyrje Stoku',
            'store_transfer' => 'Transferim Magazine',
            'product_return' => 'Kthim Produkti',
            'sale' => 'Shitje',
            'sale_cancel' => 'Anullim Shitje',
            'repair_service' => 'Riparim/Servis',
            'stock_removal' => 'Heqje Stoku',
        ];

        return $actions[$this->action_type] ?? $this->action_type;
    }

    /**
     * Get badge color for action type
     */
    public function getActionBadgeColorAttribute()
    {
        $colors = [
            'stock_entry' => 'success',
            'store_transfer' => 'info',
            'product_return' => 'warning',
            'sale' => 'primary',
            'sale_cancel' => 'danger',
            'repair_service' => 'secondary',
            'stock_removal' => 'dark',
        ];

        return $colors[$this->action_type] ?? 'secondary';
    }

    /**
     * Get badge icon for action type
     */
    public function getActionIconAttribute()
    {
        $icons = [
            'stock_entry' => 'ri-inbox-line',
            'store_transfer' => 'ri-share-forward-line',
            'product_return' => 'ri-arrow-go-back-line',
            'sale' => 'ri-shopping-bag-2-line',
            'sale_cancel' => 'ri-close-line',
            'repair_service' => 'ri-tools-line',
            'stock_removal' => 'ri-delete-bin-line',
        ];

        return $icons[$this->action_type] ?? 'ri-information-line';
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeColorAttribute()
    {
        $colors = [
            'active' => 'success',
            'returned' => 'warning',
            'sold' => 'primary',
            'repaired' => 'info',
            'removed' => 'danger',
        ];

        return $colors[$this->status] ?? 'secondary';
    }

    /**
     * Get human-readable status name
     */
    public function getStatusNameAttribute()
    {
        $statuses = [
            'active' => 'Aktiv',
            'returned' => 'I Kthyer',
            'sold' => 'I Shitur',
            'repaired' => 'I Riparuar',
            'removed' => 'I Hequr',
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    /**
     * Get movement description
     */
    public function getMovementDescriptionAttribute()
    {
        $from = $this->warehouseFrom ? $this->warehouseFrom->name : 'N/A';
        $to = $this->warehouseTo ? $this->warehouseTo->name : 'N/A';

        $descriptions = [
            'stock_entry' => "Stoku hyrë në {$to} - Furnitor: " . ($this->partner->name ?? 'N/A'),
            'store_transfer' => "I transferuar nga {$from} në {$to}",
            'product_return' => "Kthim nga {$from} - Arsyeja: " . ($this->notes ?? 'N/A'),
            'sale' => "I shitur nga {$from} - Blerësi: " . ($this->partner->name ?? 'N/A'),
            'sale_cancel' => "Anullim shitjeje - Arsyeja: " . ($this->notes ?? 'N/A'),
            'repair_service' => "Në riparim - Shënim: " . ($this->notes ?? 'N/A'),
            'stock_removal' => "Hequr nga stoku - Arsyeja: " . ($this->notes ?? 'N/A'),
        ];

        return $descriptions[$this->action_type] ?? 'Lëvizje produkti';
    }
}
