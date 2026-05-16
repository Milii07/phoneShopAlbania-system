<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductHistory;
use App\Models\Warehouse;
use App\Models\User;
use App\Models\Partner;

class ProductHistoryService
{
    /**
     * Record a stock entry (product received from supplier)
     */
    public static function recordStockEntry(
        Product $product,
        Warehouse $warehouse,
        Partner $supplier,
        int $quantity,
        ?float $purchasePrice = null,
        ?string $invoiceNumber = null,
        ?string $imei = null,
        ?string $notes = null
    ): ProductHistory {
        return ProductHistory::create([
            'product_id' => $product->id,
            'warehouse_to_id' => $warehouse->id,
            'user_id' => auth()->id(),
            'partner_id' => $supplier->id,
            'action_type' => 'stock_entry',
            'quantity' => $quantity,
            'purchase_price' => $purchasePrice,
            'invoice_number' => $invoiceNumber,
            'imei' => $imei,
            'status' => 'active',
            'notes' => $notes ?? "Stoku hyrë nga furnitor: {$supplier->name}",
        ]);
    }

    /**
     * Record a store transfer (product transferred between warehouses)
     */
    public static function recordStoreTransfer(
        Product $product,
        Warehouse $fromWarehouse,
        Warehouse $toWarehouse,
        int $quantity,
        ?string $notes = null
    ): ProductHistory {
        return ProductHistory::create([
            'product_id' => $product->id,
            'warehouse_from_id' => $fromWarehouse->id,
            'warehouse_to_id' => $toWarehouse->id,
            'user_id' => auth()->id(),
            'action_type' => 'store_transfer',
            'quantity' => $quantity,
            'status' => 'active',
            'notes' => $notes ?? "I transferuar nga {$fromWarehouse->name} në {$toWarehouse->name}",
        ]);
    }

    /**
     * Record a sale (product sold to customer)
     */
    public static function recordSale(
        Product $product,
        Warehouse $warehouse,
        Partner $customer,
        int $quantity,
        ?float $salePrice = null,
        ?float $purchasePrice = null,
        ?string $invoiceNumber = null,
        ?string $imei = null,
        ?string $notes = null
    ): ProductHistory {
        return ProductHistory::create([
            'product_id' => $product->id,
            'warehouse_from_id' => $warehouse->id,
            'user_id' => auth()->id(),
            'partner_id' => $customer->id,
            'action_type' => 'sale',
            'quantity' => $quantity,
            'purchase_price' => $purchasePrice,
            'sale_price' => $salePrice,
            'invoice_number' => $invoiceNumber,
            'imei' => $imei,
            'status' => 'sold',
            'notes' => $notes ?? "Shitje tek: {$customer->name}",
        ]);
    }

    /**
     * Record a product return (product returned from customer)
     */
    public static function recordReturn(
        Product $product,
        Warehouse $warehouse,
        int $quantity,
        ?string $reason = null,
        ?string $notes = null
    ): ProductHistory {
        return ProductHistory::create([
            'product_id' => $product->id,
            'warehouse_to_id' => $warehouse->id,
            'user_id' => auth()->id(),
            'action_type' => 'product_return',
            'quantity' => $quantity,
            'status' => 'active',
            'notes' => $notes ?? "Kthim produkti. Arsyeja: {$reason}",
        ]);
    }

    /**
     * Record a repair/service action
     */
    public static function recordRepair(
        Product $product,
        Warehouse $warehouse,
        int $quantity = 1,
        ?string $repairType = null,
        ?string $notes = null
    ): ProductHistory {
        return ProductHistory::create([
            'product_id' => $product->id,
            'warehouse_to_id' => $warehouse->id,
            'user_id' => auth()->id(),
            'action_type' => 'repair_service',
            'quantity' => $quantity,
            'status' => 'repaired',
            'notes' => $notes ?? "Riparim/Servis: {$repairType}",
        ]);
    }

    /**
     * Record stock removal (product removed from inventory)
     */
    public static function recordStockRemoval(
        Product $product,
        Warehouse $warehouse,
        int $quantity,
        ?string $reason = null,
        ?string $notes = null
    ): ProductHistory {
        return ProductHistory::create([
            'product_id' => $product->id,
            'warehouse_from_id' => $warehouse->id,
            'user_id' => auth()->id(),
            'action_type' => 'stock_removal',
            'quantity' => $quantity,
            'status' => 'removed',
            'notes' => $notes ?? "Heqje stogu. Arsyeja: {$reason}",
        ]);
    }

    /**
     * Record a sale cancellation
     */
    public static function recordSaleCancel(
        Product $product,
        Warehouse $warehouse,
        int $quantity,
        ?string $invoiceNumber = null,
        ?string $reason = null
    ): ProductHistory {
        return ProductHistory::create([
            'product_id' => $product->id,
            'warehouse_to_id' => $warehouse->id,
            'user_id' => auth()->id(),
            'action_type' => 'sale_cancel',
            'quantity' => $quantity,
            'invoice_number' => $invoiceNumber,
            'status' => 'active',
            'notes' => "Anullim shitjeje. Arsyeja: {$reason}",
        ]);
    }

    /**
     * Get complete product history as a formatted timeline
     */
    public static function getProductTimeline(Product $product, ?int $limit = null)
    {
        $query = ProductHistory::forProduct($product->id)
            ->with('user', 'partner', 'warehouseFrom', 'warehouseTo')
            ->latest();

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get()->map(function ($history) {
            return [
                'id' => $history->id,
                'date' => $history->created_at,
                'action_type' => $history->action_type_name,
                'action_icon' => $history->action_icon,
                'action_color' => $history->action_badge_color,
                'from_warehouse' => $history->warehouseFrom?->name,
                'to_warehouse' => $history->warehouseTo?->name,
                'user' => $history->user?->name,
                'partner' => $history->partner?->name,
                'quantity' => $history->quantity,
                'purchase_price' => $history->purchase_price,
                'sale_price' => $history->sale_price,
                'invoice' => $history->invoice_number,
                'status' => $history->status_name,
                'status_color' => $history->status_badge_color,
                'warranty' => $history->warranty,
                'imei' => $history->imei,
                'notes' => $history->notes,
                'movement_description' => $history->movement_description,
            ];
        });
    }

    /**
     * Get movement summary for a product
     */
    public static function getProductSummary(Product $product)
    {
        return [
            'total_movements' => ProductHistory::forProduct($product->id)->count(),
            'stock_entries' => ProductHistory::forProduct($product->id)->byActionType('stock_entry')->count(),
            'sales' => ProductHistory::forProduct($product->id)->byActionType('sale')->count(),
            'transfers' => ProductHistory::forProduct($product->id)->byActionType('store_transfer')->count(),
            'returns' => ProductHistory::forProduct($product->id)->byActionType('product_return')->count(),
            'repairs' => ProductHistory::forProduct($product->id)->byActionType('repair_service')->count(),
            'removals' => ProductHistory::forProduct($product->id)->byActionType('stock_removal')->count(),
            'last_movement' => ProductHistory::forProduct($product->id)->latest()->first(),
        ];
    }

    /**
     * Get warehouse transaction summary
     */
    public static function getWarehouseTransactions(Warehouse $warehouse, ?int $days = 30)
    {
        $startDate = now()->subDays($days);

        return [
            'inbound' => ProductHistory::where('warehouse_to_id', $warehouse->id)
                ->whereDate('created_at', '>=', $startDate)
                ->count(),
            'outbound' => ProductHistory::where('warehouse_from_id', $warehouse->id)
                ->whereDate('created_at', '>=', $startDate)
                ->count(),
            'stock_entries' => ProductHistory::where('warehouse_to_id', $warehouse->id)
                ->byActionType('stock_entry')
                ->whereDate('created_at', '>=', $startDate)
                ->count(),
            'sales' => ProductHistory::where('warehouse_from_id', $warehouse->id)
                ->byActionType('sale')
                ->whereDate('created_at', '>=', $startDate)
                ->count(),
        ];
    }

    /**
     * Get product movement chain as narrative
     */
    public static function getProductNarrative(Product $product): string
    {
        $histories = ProductHistory::forProduct($product->id)
            ->with('warehouseFrom', 'warehouseTo', 'partner')
            ->latest()
            ->get();

        if ($histories->isEmpty()) {
            return "Ky produkt nuk ka histori.";
        }

        $narrative = "Rrugëtimi i produktit {$product->name}:\n";

        foreach ($histories as $history) {
            switch ($history->action_type) {
                case 'stock_entry':
                    $narrative .= "\n→ Stoku hyri në {$history->warehouseTo->name} nga furnitori {$history->partner->name} ({$history->created_at->format('d/m/Y')})";
                    break;
                case 'store_transfer':
                    $narrative .= "\n→ I transferuar nga {$history->warehouseFrom->name} në {$history->warehouseTo->name} ({$history->created_at->format('d/m/Y')})";
                    break;
                case 'sale':
                    $narrative .= "\n→ I shitur në {$history->warehouseFrom->name} tek blerësi {$history->partner->name} ({$history->created_at->format('d/m/Y')})";
                    break;
                case 'product_return':
                    $narrative .= "\n→ I kthyer në {$history->warehouseTo->name} ({$history->created_at->format('d/m/Y')})";
                    break;
                case 'repair_service':
                    $narrative .= "\n→ Në riparim në {$history->warehouseTo->name} ({$history->created_at->format('d/m/Y')})";
                    break;
                case 'stock_removal':
                    $narrative .= "\n→ Hequr nga stoku në {$history->warehouseFrom->name} ({$history->created_at->format('d/m/Y')})";
                    break;
                case 'sale_cancel':
                    $narrative .= "\n→ Shitja anulluar ({$history->created_at->format('d/m/Y')})";
                    break;
            }
        }

        return $narrative;
    }
}
