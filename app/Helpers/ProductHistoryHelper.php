<?php

if (!function_exists('productHistory')) {
    /**
     * Helper function to access ProductHistoryService
     *
     * @return \App\Services\ProductHistoryService
     */
    function productHistory()
    {
        return new \App\Services\ProductHistoryService();
    }
}

if (!function_exists('recordProductHistory')) {
    /**
     * Helper to quickly record product history
     *
     * @param string $actionType
     * @param array $data
     * @return \App\Models\ProductHistory
     */
    function recordProductHistory(string $actionType, array $data)
    {
        return \App\Models\ProductHistory::create(array_merge([
            'action_type' => $actionType,
            'user_id' => auth()->id(),
            'created_at' => now(),
        ], $data));
    }
}

if (!function_exists('getProductTimeline')) {
    /**
     * Helper to get product timeline
     *
     * @param \App\Models\Product $product
     * @param int|null $limit
     * @return \Illuminate\Support\Collection
     */
    function getProductTimeline($product, $limit = null)
    {
        return \App\Services\ProductHistoryService::getProductTimeline($product, $limit);
    }
}

if (!function_exists('getProductHistory')) {
    /**
     * Helper to get product history summary
     *
     * @param \App\Models\Product $product
     * @return array
     */
    function getProductHistory($product)
    {
        return \App\Services\ProductHistoryService::getProductSummary($product);
    }
}

if (!function_exists('getActionBadgeColor')) {
    /**
     * Get badge color for action type
     *
     * @param string $actionType
     * @return string
     */
    function getActionBadgeColor(string $actionType): string
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

        return $colors[$actionType] ?? 'secondary';
    }
}

if (!function_exists('getActionIcon')) {
    /**
     * Get icon class for action type
     *
     * @param string $actionType
     * @return string
     */
    function getActionIcon(string $actionType): string
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

        return $icons[$actionType] ?? 'ri-information-line';
    }
}

if (!function_exists('getActionTypeName')) {
    /**
     * Get human-readable action type name in Albanian
     *
     * @param string $actionType
     * @return string
     */
    function getActionTypeName(string $actionType): string
    {
        $names = [
            'stock_entry' => 'Hyrje Stoku',
            'store_transfer' => 'Transferim Magazine',
            'product_return' => 'Kthim Produkti',
            'sale' => 'Shitje',
            'sale_cancel' => 'Anullim Shitje',
            'repair_service' => 'Riparim/Servis',
            'stock_removal' => 'Heqje Stoku',
        ];

        return $names[$actionType] ?? $actionType;
    }
}

if (!function_exists('getStatusBadgeColor')) {
    /**
     * Get badge color for status
     *
     * @param string $status
     * @return string
     */
    function getStatusBadgeColor(string $status): string
    {
        $colors = [
            'active' => 'success',
            'returned' => 'warning',
            'sold' => 'primary',
            'repaired' => 'info',
            'removed' => 'danger',
        ];

        return $colors[$status] ?? 'secondary';
    }
}

if (!function_exists('getStatusName')) {
    /**
     * Get human-readable status name in Albanian
     *
     * @param string $status
     * @return string
     */
    function getStatusName(string $status): string
    {
        $names = [
            'active' => 'Aktiv',
            'returned' => 'I Kthyer',
            'sold' => 'I Shitur',
            'repaired' => 'I Riparuar',
            'removed' => 'I Hequr',
        ];

        return $names[$status] ?? $status;
    }
}
