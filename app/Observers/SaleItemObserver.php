<?php

namespace App\Observers;

use App\Models\SaleItem;
use App\Models\ProductHistory;

class SaleItemObserver
{
    /**
     * Handle the SaleItem "created" event.
     * Track product sale in history
     */
    public function created(SaleItem $saleItem): void
    {
        try {
            if ($saleItem->sale && $saleItem->product) {
                ProductHistory::create([
                    'product_id' => $saleItem->product_id,
                    'warehouse_from_id' => $saleItem->warehouse_id,
                    'user_id' => auth()->id(),
                    'partner_id' => $saleItem->sale->partner_id,
                    'action_type' => 'sale',
                    'quantity' => $saleItem->quantity,
                    'purchase_price' => $saleItem->purchase_price,
                    'sale_price' => $saleItem->sale_price,
                    'invoice_number' => $saleItem->sale->invoice_number,
                    'imei' => isset($saleItem->imei_numbers[0]) ? $saleItem->imei_numbers[0] : null,
                    'status' => 'sold',
                    'notes' => "Shitje: " . ($saleItem->sale->description ?? ''),
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Error creating ProductHistory for SaleItem: ' . $e->getMessage());
        }
    }

    /**
     * Handle the SaleItem "updated" event.
     * Track changes in sale items
     */
    public function updated(SaleItem $saleItem): void
    {
        try {
            $changes = $saleItem->getChanges();

            // If quantity changed, record it
            if (isset($changes['quantity'])) {
                $oldQuantity = $saleItem->getOriginal('quantity');
                $newQuantity = $saleItem->quantity;

                if ($newQuantity > $oldQuantity) {
                    // Quantity increased
                    ProductHistory::create([
                        'product_id' => $saleItem->product_id,
                        'warehouse_from_id' => $saleItem->warehouse_id,
                        'user_id' => auth()->id(),
                        'partner_id' => $saleItem->sale->partner_id,
                        'action_type' => 'sale',
                        'quantity' => $newQuantity - $oldQuantity,
                        'sale_price' => $saleItem->sale_price,
                        'invoice_number' => $saleItem->sale->invoice_number,
                        'status' => 'sold',
                        'notes' => "Rritje shumë në shitje",
                    ]);
                } elseif ($newQuantity < $oldQuantity) {
                    // Quantity decreased - treat as return
                    ProductHistory::create([
                        'product_id' => $saleItem->product_id,
                        'warehouse_to_id' => $saleItem->warehouse_id,
                        'user_id' => auth()->id(),
                        'action_type' => 'product_return',
                        'quantity' => $oldQuantity - $newQuantity,
                        'invoice_number' => $saleItem->sale->invoice_number,
                        'status' => 'active',
                        'notes' => "Kthim nga shitje",
                    ]);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error updating ProductHistory for SaleItem: ' . $e->getMessage());
        }
    }

    /**
     * Handle the SaleItem "deleted" event.
     * Track sale item deletion as cancellation
     */
    public function deleted(SaleItem $saleItem): void
    {
        try {
            if ($saleItem->sale && $saleItem->product) {
                // Create a cancellation record
                ProductHistory::create([
                    'product_id' => $saleItem->product_id,
                    'warehouse_to_id' => $saleItem->warehouse_id,
                    'user_id' => auth()->id(),
                    'action_type' => 'sale_cancel',
                    'quantity' => $saleItem->quantity,
                    'invoice_number' => $saleItem->sale->invoice_number,
                    'status' => 'active',
                    'notes' => "Anullim shitjeje",
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Error deleting ProductHistory for SaleItem: ' . $e->getMessage());
        }
    }
}
