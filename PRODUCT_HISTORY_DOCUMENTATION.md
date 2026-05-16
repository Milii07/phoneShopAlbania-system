# Product History / Product Tracking System

## Overview

A complete product tracking system for the Phone Shop Albania application that maintains a comprehensive history of every product movement from stock entry until sale. This system automatically tracks all product movements without requiring manual intervention.

## Features

- **Automatic Tracking**: Automatically records product movements when sales and purchases are created
- **Timeline View**: Visual timeline display of all product movements
- **Advanced Search**: Search products by name, SKU, barcode, or IMEI
- **Detailed History**: Complete information for each movement including:
  - Date and time
  - Movement type (stock entry, transfer, sale, return, repair, removal)
  - Source and destination warehouses
  - User who performed the action
  - Customer/Supplier information
  - Purchase and sale prices
  - Invoice numbers
  - Warranty information
  - Custom notes

- **Multiple Views**: Timeline view and table view for history display
- **Export**: Export product history to CSV format
- **Responsive Design**: Works on desktop, tablet, and mobile devices
- **Immutable Records**: History records are never deleted, ensuring complete audit trail

## Database Schema

### product_histories table

```
id                    - Primary key
product_id            - Foreign key to products table
warehouse_from_id     - Foreign key to warehouses (source)
warehouse_to_id       - Foreign key to warehouses (destination)
user_id               - Foreign key to users (who performed action)
partner_id            - Foreign key to partners (customer/supplier)
action_type           - Type of movement (stock_entry, store_transfer, product_return, sale, sale_cancel, repair_service, stock_removal)
quantity              - Number of items moved
purchase_price        - Purchase price at time of movement
sale_price            - Sale price at time of movement
invoice_number        - Associated invoice/purchase number
imei                  - IMEI number for individual device tracking
sku                   - SKU code
barcode               - Barcode
status                - Current status (active, returned, sold, repaired, removed)
warranty              - Warranty information
notes                 - Additional notes about the movement
created_at            - Timestamp of movement
updated_at            - Last update timestamp
```

## Installation

The system is already installed. To activate it, run the migration:

```bash
php artisan migrate
```

## Usage

### Access the Product History Module

Navigate to the admin panel and look for "Histori Produkti" in the sidebar menu under the Products section.

### Automatic Tracking

The system automatically records product movements when:

1. **Stock Entry**: When a purchase item is created
2. **Sales**: When a sale item is created
3. **Deletions**: When sale or purchase items are deleted
4. **Modifications**: When quantities are modified in sales or purchases

### Manual History Recording

You can manually record product movements using the `ProductHistoryService`:

```php
use App\Services\ProductHistoryService;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Partner;

// Record a stock entry
ProductHistoryService::recordStockEntry(
    product: Product::find(1),
    warehouse: Warehouse::find(1),
    supplier: Partner::find(1),
    quantity: 10,
    purchasePrice: 50.00,
    invoiceNumber: 'INV-001',
    imei: '123456789',
    notes: 'Initial stock entry'
);

// Record a store transfer
ProductHistoryService::recordStoreTransfer(
    product: Product::find(1),
    fromWarehouse: Warehouse::find(1),
    toWarehouse: Warehouse::find(2),
    quantity: 5,
    notes: 'Transferred for replenishment'
);

// Record a sale
ProductHistoryService::recordSale(
    product: Product::find(1),
    warehouse: Warehouse::find(1),
    customer: Partner::find(2),
    quantity: 1,
    salePrice: 100.00,
    purchasePrice: 50.00,
    invoiceNumber: 'SALE-001',
    imei: '123456789'
);

// Record a return
ProductHistoryService::recordReturn(
    product: Product::find(1),
    warehouse: Warehouse::find(1),
    quantity: 1,
    reason: 'Defective',
    notes: 'Returned by customer'
);

// Record a repair
ProductHistoryService::recordRepair(
    product: Product::find(1),
    warehouse: Warehouse::find(1),
    quantity: 1,
    repairType: 'Screen repair',
    notes: 'Screen replacement'
);

// Record stock removal
ProductHistoryService::recordStockRemoval(
    product: Product::find(1),
    warehouse: Warehouse::find(1),
    quantity: 1,
    reason: 'Damaged',
    notes: 'Removed from stock due to damage'
);

// Record sale cancellation
ProductHistoryService::recordSaleCancel(
    product: Product::find(1),
    warehouse: Warehouse::find(1),
    quantity: 1,
    invoiceNumber: 'SALE-001',
    reason: 'Customer request'
);
```

### Getting Product Information

```php
use App\Services\ProductHistoryService;
use App\Models\Product;

$product = Product::find(1);

// Get timeline view of product movements
$timeline = ProductHistoryService::getProductTimeline($product, limit: 50);

// Get summary statistics
$summary = ProductHistoryService::getProductSummary($product);
// Returns: [
//     'total_movements' => 15,
//     'stock_entries' => 3,
//     'sales' => 5,
//     'transfers' => 4,
//     'returns' => 2,
//     'repairs' => 1,
//     'removals' => 0,
//     'last_movement' => ProductHistory object
// ]

// Get warehouse transaction summary
$warehouse = Warehouse::find(1);
$transactions = ProductHistoryService::getWarehouseTransactions($warehouse, days: 30);

// Get product narrative as text
$narrative = ProductHistoryService::getProductNarrative($product);
// Example output:
// "Rrugëtimi i produktit iPhone 13:
//  → Stoku hyri në Dyqani 1 nga furnitori ABC Company (15/01/2026)
//  → I transferuar nga Dyqani 1 në Dyqani 2 (16/01/2026)
//  → I shitur në Dyqani 2 tek blerësi John Doe (17/01/2026)"
```

### Routes

#### Main Routes

- `GET /product-history/` - Product history search page
- `GET /product-history/product/{product}` - Full product history timeline page
- `GET /product-history/modal/{product}` - Modal view of product history

#### API Routes

- `GET /product-history/search?q={term}&type={type}` - Search products (AJAX)
- `GET /product-history/product/{product}/data` - Get history data for DataTables
- `GET /product-history/filter` - Filter history with advanced filters
- `GET /product-history/export` - Export history to CSV
- `GET /product-history/stats` - Get statistics

### Search Types

- `name` - Search by product name
- `sku` - Search by SKU
- `barcode` - Search by barcode
- `imei` - Search by IMEI number

### Filters

You can filter product history using:

- `product_id` - Filter by specific product
- `action_type` - Filter by movement type
- `warehouse_id` - Filter by warehouse (source or destination)
- `start_date` - Filter from date
- `end_date` - Filter to date
- `user_id` - Filter by user who performed action
- `status` - Filter by current status

### Movement Types

| Type | Name (Albanian) | Description |
|------|-----------------|-------------|
| stock_entry | Hyrje Stoku | Product received from supplier |
| store_transfer | Transferim Magazine | Product transferred between warehouses |
| product_return | Kthim Produkti | Product returned from customer |
| sale | Shitje | Product sold to customer |
| sale_cancel | Anullim Shitje | Sale cancelled |
| repair_service | Riparim/Servis | Product sent for repair |
| stock_removal | Heqje Stoku | Product removed from inventory |

### Status Values

| Status | Name (Albanian) | Description |
|--------|-----------------|-------------|
| active | Aktiv | Product is in active stock |
| sold | I Shitur | Product has been sold |
| returned | I Kthyer | Product was returned |
| repaired | I Riparuar | Product was repaired |
| removed | I Hequr | Product was removed from stock |

## Views

### Index Page (`/product-history/`)

Search interface for finding products and viewing their complete history. Features:

- Multi-type search (name, SKU, barcode, IMEI)
- Product list with details
- Quick access to product history

### Product History Page (`/product-history/product/{id}`)

Complete product history with:

- Product information and statistics
- Timeline view with detailed movement cards
- Table view for quick scanning
- Toggle between timeline and table views
- Export functionality
- Responsive design

### Modal View

Quick history view in a modal dialog, useful for quick checks while working with products.

## Observers

The system uses Laravel Observers to automatically track changes:

### SaleItemObserver

Automatically records when:
- Sale item is created → Records "sale" action
- Sale item quantity is updated → Records quantity changes
- Sale item is deleted → Records "sale_cancel" action

### PurchaseItemObserver

Automatically records when:
- Purchase item is created → Records "stock_entry" action
- Purchase item quantity is updated → Records quantity changes
- Purchase item is deleted → Records "stock_removal" action

## Models and Relationships

### ProductHistory Model

```php
// Relationships
$productHistory->product;           // Get the product
$productHistory->warehouseFrom;     // Get source warehouse
$productHistory->warehouseTo;       // Get destination warehouse
$productHistory->user;              // Get user who performed action
$productHistory->partner;           // Get customer/supplier

// Scopes
ProductHistory::forProduct($id)                          // Filter by product
ProductHistory::byActionType('sale')                     // Filter by action type
ProductHistory::byWarehouse($id, 'to')                   // Filter by warehouse
ProductHistory::byUser($id)                              // Filter by user
ProductHistory::dateRange($start, $end)                  // Filter by date range
ProductHistory::byStatus('sold')                         // Filter by status

// Attributes
$productHistory->action_type_name                        // Human-readable action type
$productHistory->action_badge_color                      // Bootstrap color for badge
$productHistory->action_icon                             // Remix icon class
$productHistory->status_name                             // Human-readable status
$productHistory->status_badge_color                      // Bootstrap color for status badge
$productHistory->movement_description                    // Description of the movement
```

## API Examples

### Search Products
```javascript
fetch('/product-history/search?q=iPhone&type=name')
    .then(response => response.json())
    .then(products => console.log(products));
```

### Get History Data
```javascript
fetch('/product-history/product/1/data')
    .then(response => response.json())
    .then(data => console.log(data));
```

### Export to CSV
```javascript
// Download CSV file
window.location.href = '/product-history/export?product_id=1&start_date=2026-01-01';
```

### Get Statistics
```javascript
fetch('/product-history/stats?start_date=2026-01-01&end_date=2026-05-16')
    .then(response => response.json())
    .then(stats => console.log(stats));
```

## Query Optimization

The system uses:

- **Indexes**: Database indexes on frequently queried columns
- **Eager Loading**: Relationships are eager-loaded to prevent N+1 queries
- **Scopes**: Reusable query scopes for common filters
- **Pagination**: Large result sets are paginated

## Permissions

Access to product history features can be controlled via:

- `product-history.index` - View product history
- `product-history.search` - Search products
- `product-history.show` - View product timeline
- `product-history.filter` - Use advanced filters
- `product-history.export` - Export history

## Best Practices

1. **Always use ProductHistoryService** for manual history recording
2. **Never delete history records** - they form the audit trail
3. **Include meaningful notes** when recording manual movements
4. **Use consistent IMEI tracking** for individual device tracking
5. **Regular exports** for backup and analysis
6. **Monitor warehouse transfers** for inventory accuracy

## Troubleshooting

### History not recording automatically

1. Check if observers are registered in `AppServiceProvider.php`
2. Verify that `SaleItem` and `PurchaseItem` use the observers
3. Check application logs for errors

### Export not working

1. Ensure proper permissions
2. Check if CSV headers are being sent correctly
3. Verify file download in browser

### Performance issues

1. Add database indexes if needed
2. Use pagination for large result sets
3. Filter date ranges to reduce data load

## Migration

The migration file is: `database/migrations/2026_05_16_000001_create_product_histories_table.php`

To run the migration:

```bash
php artisan migrate
```

To rollback:

```bash
php artisan migrate:rollback --step=1
```

## Future Enhancements

Potential improvements:

- Batch operations for multiple products
- Advanced analytics and reporting
- Product lifecycle analysis
- Automatic alerts for slow-moving products
- Integration with inventory management system
- Mobile app support
- Real-time notifications
- Custom report builder

## Support

For issues or questions, contact the development team or check the application logs.

---

**Last Updated**: May 16, 2026
**Version**: 1.0
