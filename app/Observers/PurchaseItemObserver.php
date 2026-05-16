<?php

namespace App\Observers;

use App\Models\PurchaseItem;
use App\Models\ProductHistory;

class PurchaseItemObserver
{
    /**
     * Handle the PurchaseItem "created" event.
     * Track product stock entry in history
     */
    public function created(PurchaseItem $purchaseItem): void
    {
        try {
            if ($purchaseItem->purchase && $purchaseItem->product) {
                ProductHistory::create([
                    'product_id' => $purchaseItem->product_id,
                    'warehouse_to_id' => $purchaseItem->purchase->warehouse_id,
                    'user_id' => auth()->id(),
                    'partner_id' => $purchaseItem->purchase->partner_id,
                    'action_type' => 'stock_entry',
                    'quantity' => $purchaseItem->quantity,
                    'purchase_price' => $purchaseItem->unit_cost,
                    'invoice_number' => $purchaseItem->purchase->purchase_number,
                    'imei' => isset($purchaseItem->imei_numbers[0]) ? $purchaseItem->imei_numbers[0] : null,
                    'status' => 'active',
                    'notes' => "Stoku hyrë nga furnitor: " . ($purchaseItem->purchase->partner->name ?? ''),
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Error creating ProductHistory for PurchaseItem: ' . $e->getMessage());
        }
    }

    /**
     * Handle the PurchaseItem "updated" event.
     * Track changes in purchase items
     */
    public function updated(PurchaseItem $purchaseItem): void
    {
        try {
            $changes = $purchaseItem->getChanges();

            // If quantity changed, record it
            if (isset($changes['quantity'])) {
                $oldQuantity = $purchaseItem->getOriginal('quantity');
                $newQuantity = $purchaseItem->quantity;

                if ($newQuantity > $oldQuantity) {
                    // Quantity increased
                    ProductHistory::create([
                        'product_id' => $purchaseItem->product_id,
                        'warehouse_to_id' => $purchaseItem->purchase->warehouse_id,
                        'user_id' => auth()->id(),
                        'partner_id' => $purchaseItem->purchase->partner_id,
                        'action_type' => 'stock_entry',
                        'quantity' => $newQuantity - $oldQuantity,
                        'purchase_price' => $purchaseItem->unit_cost,
                        'invoice_number' => $purchaseItem->purchase->purchase_number,
                        'status' => 'active',
                        'notes' => "Rritje shumë stogu",
                    ]);
                } elseif ($newQuantity < $oldQuantity) {
                    // Quantity decreased
                    ProductHistory::create([
                        'product_id' => $purchaseItem->product_id,
                        'warehouse_from_id' => $purchaseItem->purchase->warehouse_id,
                        'user_id' => auth()->id(),
                        'action_type' => 'stock_removal',
                        'quantity' => $oldQuantity - $newQuantity,
                        'purchase_price' => $purchaseItem->unit_cost,
                        'invoice_number' => $purchaseItem->purchase->purchase_number,
                        'status' => 'removed',
                        'notes' => "Zvogëlim stogu",
                    ]);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error updating ProductHistory for PurchaseItem: ' . $e->getMessage());
        }
    }

    /**
     * Handle the PurchaseItem "deleted" event.
     * Track purchase item deletion
     */
    public function deleted(PurchaseItem $purchaseItem): void
    {
        try {
            if ($purchaseItem->purchase && $purchaseItem->product) {
                ProductHistory::create([
                    'product_id' => $purchaseItem->product_id,
                    'warehouse_from_id' => $purchaseItem->purchase->warehouse_id,
                    'user_id' => auth()->id(),
                    'action_type' => 'stock_removal',
                    'quantity' => $purchaseItem->quantity,
                    'purchase_price' => $purchaseItem->unit_cost,
                    'invoice_number' => $purchaseItem->purchase->purchase_number,
                    'status' => 'removed',
                    'notes' => "Heqje e artikullit të blerjes",
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Error deleting ProductHistory for PurchaseItem: ' . $e->getMessage());
        }
    }
}
