# Product History Module - Visual Architecture Guide

## 🏗️ System Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    ADMIN PANEL (User Interface)              │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  ┌──────────────────────────────────────────────────────┐   │
│  │  Sidebar Menu                                        │   │
│  │  └─ Histori Produkti ─┬──────────────────────────┐  │   │
│  │                       │ Search Products          │  │   │
│  │                       ├─ By Name                 │  │   │
│  │                       ├─ By SKU                  │  │   │
│  │                       ├─ By Barcode              │  │   │
│  │                       └─ By IMEI                 │  │   │
│  └──────────────────────────────────────────────────┘   │   │
│                                                               │
│  ┌──────────────────────────────────────────────────────┐   │
│  │  Products Index                                      │   │
│  │  Each Product Row:                                   │   │
│  │  ┌─────────────────────────────────────────────┐    │   │
│  │  │ View │ Timeline ← NEW │ Edit │ Delete        │    │   │
│  │  └─────────────────────────────────────────────┘    │   │
│  │  (Yellow history button)                            │   │
│  └──────────────────────────────────────────────────────┘   │
│                                                               │
│  ┌──────────────────────────────────────────────────────┐   │
│  │  Product History Timeline View                       │   │
│  │  ┌──────────────────────────────────────────────┐   │   │
│  │  │ Timeline View [Active]  │ Table View        │   │   │
│  │  ├──────────────────────────────────────────────┤   │   │
│  │  │                                              │   │   │
│  │  │  📦 Hyrje Stoku        (15/05/2026 10:30)   │   │   │
│  │  │  ├─ Nga: Dyqani 1                           │   │   │
│  │  │  ├─ Në: Dyqani 2                            │   │   │
│  │  │  ├─ Furnitor: ABC Company                   │   │   │
│  │  │  ├─ Sasia: 5                                │   │   │
│  │  │  └─ Çmim Blerjeje: 50.00 €                 │   │   │
│  │  │                                              │   │   │
│  │  │  🔄 Transferim Magazine   (16/05/2026 14:15)│   │   │
│  │  │  ├─ Nga: Dyqani 1                           │   │   │
│  │  │  ├─ Në: Dyqani 2                            │   │   │
│  │  │  ├─ Përdoruesi: Admin User                  │   │   │
│  │  │  └─ Sasia: 2                                │   │   │
│  │  │                                              │   │   │
│  │  │  💰 Shitje                (17/05/2026 09:45)│   │   │
│  │  │  ├─ Nga: Dyqani 2                           │   │   │
│  │  │  ├─ Blerësi: John Doe                       │   │   │
│  │  │  ├─ Telefon: +355 692 123 456              │   │   │
│  │  │  ├─ Sasia: 1                                │   │   │
│  │  │  ├─ Çmim Shitjeje: 100.00 €               │   │   │
│  │  │  └─ Fatura: INV-001                         │   │   │
│  │  │                                              │   │   │
│  │  └──────────────────────────────────────────────┘   │   │
│  └──────────────────────────────────────────────────────┘   │
│                                                               │
└─────────────────────────────────────────────────────────────┘

                           ↓↓↓ AJAX/HTTP ↓↓↓

┌─────────────────────────────────────────────────────────────┐
│                    API LAYER (Routes)                        │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  GET  /product-history/                  → index()          │
│  GET  /product-history/search             → search()        │
│  GET  /product-history/filter             → filter()        │
│  GET  /product-history/export             → export()        │
│  GET  /product-history/stats              → getStats()      │
│  GET  /product-history/product/{id}       → show()          │
│  GET  /product-history/product/{id}/data  → getHistoryData()│
│  GET  /product-history/product/{id}/modal → modal()         │
│                                                               │
└─────────────────────────────────────────────────────────────┘

                           ↓↓↓ Query ↓↓↓

┌─────────────────────────────────────────────────────────────┐
│              CONTROLLER LAYER (ProductHistoryController)     │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  ├─ index()           → Display search page                 │
│  ├─ search()          → Find products by name/SKU/etc       │
│  ├─ show()            → Get complete product history        │
│  ├─ getHistoryData()  → Format data for DataTables         │
│  ├─ modal()           → Get modal view                      │
│  ├─ filter()          → Advanced filtering                  │
│  ├─ export()          → CSV export                          │
│  └─ getStats()        → Movement statistics                 │
│                                                               │
└─────────────────────────────────────────────────────────────┘

                           ↓↓↓ Business Logic ↓↓↓

┌─────────────────────────────────────────────────────────────┐
│           SERVICE LAYER (ProductHistoryService)              │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  ├─ recordStockEntry()       → Record supplier stock        │
│  ├─ recordStoreTransfer()     → Record warehouse transfer   │
│  ├─ recordSale()              → Record customer purchase    │
│  ├─ recordReturn()            → Record product return       │
│  ├─ recordRepair()            → Record repair action        │
│  ├─ recordStockRemoval()      → Record stock removal        │
│  ├─ recordSaleCancel()        → Record cancellation         │
│  ├─ getProductTimeline()      → Get formatted timeline      │
│  ├─ getProductSummary()       → Get statistics              │
│  ├─ getWarehouseTransactions()→ Get warehouse stats         │
│  └─ getProductNarrative()     → Get text narrative          │
│                                                               │
└─────────────────────────────────────────────────────────────┘

                           ↓↓↓ Database ↓↓↓

┌─────────────────────────────────────────────────────────────┐
│              MODEL LAYER (ProductHistory Model)              │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  Relationships:                                              │
│  ├─ product()        → belongsTo(Product)                   │
│  ├─ warehouseFrom()  → belongsTo(Warehouse)                 │
│  ├─ warehouseTo()    → belongsTo(Warehouse)                 │
│  ├─ user()           → belongsTo(User)                      │
│  └─ partner()        → belongsTo(Partner)                   │
│                                                               │
│  Scopes:                                                     │
│  ├─ forProduct()     → Filter by product_id                 │
│  ├─ byActionType()   → Filter by action_type                │
│  ├─ byWarehouse()    → Filter by warehouse                  │
│  ├─ byUser()         → Filter by user_id                    │
│  ├─ dateRange()      → Filter by date range                 │
│  └─ byStatus()       → Filter by status                     │
│                                                               │
└─────────────────────────────────────────────────────────────┘

                           ↓↓↓ Persist ↓↓↓

┌─────────────────────────────────────────────────────────────┐
│              DATABASE (product_histories Table)               │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  product_histories                                           │
│  ┌──────────────────────────────────────────────────────┐   │
│  │ id│product_id│from_id│to_id│user_id│partner_id│     │   │
│  │  1│    45    │   1   │  2  │  3    │   12    │  ...  │   │
│  │  2│    45    │   2   │ NULL│  3    │   18    │  ...  │   │
│  │  3│    45    │ NULL  │  2  │  3    │   1     │  ...  │   │
│  │  4│    45    │   2   │ NULL│  5    │ NULL    │  ...  │   │
│  │  5│    45    │   1   │  2  │  3    │   25    │  ...  │   │
│  │  6│    45    │   2   │ NULL│  3    │   1     │  ...  │   │
│  │                                                      │   │
│  │ Indexes:                                            │   │
│  │ └─ PRIMARY KEY (id)                                │   │
│  │ └─ INDEX (product_id)                              │   │
│  │ └─ INDEX (warehouse_from_id)                        │   │
│  │ └─ INDEX (warehouse_to_id)                          │   │
│  │ └─ INDEX (user_id)                                  │   │
│  │ └─ INDEX (partner_id)                               │   │
│  │ └─ INDEX (action_type)                              │   │
│  │ └─ INDEX (created_at)                               │   │
│  │ └─ INDEX (product_id + created_at)                  │   │
│  └──────────────────────────────────────────────────────┘   │
│                                                               │
└─────────────────────────────────────────────────────────────┘
```

## 🔄 Automatic Tracking Flow

```
PURCHASE PROCESS
════════════════════════════════════════════════════════════════

User Creates Purchase
    ↓
Creates PurchaseItem(s)
    ↓
PurchaseItemObserver triggered (created event)
    ↓
Automatically calls ProductHistory::create() with:
  - action_type: 'stock_entry'
  - warehouse_to_id: purchase warehouse
  - partner_id: supplier
  - purchase_price: item cost
  - quantity: item quantity
    ↓
ProductHistory record inserted
    ↓
System has complete audit trail ✓


SALES PROCESS
════════════════════════════════════════════════════════════════

User Creates Sale
    ↓
Creates SaleItem(s)
    ↓
SaleItemObserver triggered (created event)
    ↓
Automatically calls ProductHistory::create() with:
  - action_type: 'sale'
  - warehouse_from_id: sale warehouse
  - partner_id: customer
  - sale_price: item price
  - purchase_price: cost
  - quantity: item quantity
  - invoice_number: sale invoice
    ↓
ProductHistory record inserted
    ↓
System has complete audit trail ✓
```

## 📊 Data Flow Diagram

```
PRODUCT LIFECYCLE TRACKING
════════════════════════════════════════════════════════════════

                          TIME AXIS →

    ↓ Stock Entry         ↓ Transfer           ↓ Sale
    │                     │                    │
    ├─ Date: 15/05/2026  ├─ Date: 16/05/2026 ├─ Date: 17/05/2026
    ├─ Action: Purchase  ├─ Action: Transfer  ├─ Action: Sale
    ├─ Qty: 5            ├─ Qty: 2            ├─ Qty: 1
    ├─ From: Supplier    ├─ From: Store A     ├─ From: Store B
    ├─ To: Store A       ├─ To: Store B       ├─ To: Customer
    ├─ Price: 50€        ├─ Price: N/A        ├─ Price: 100€
    └─ Status: Active    └─ Status: Active    └─ Status: Sold
    
Product History Records:
├─ Record #1: { action: stock_entry, warehouse_to: Store A, qty: 5 }
├─ Record #2: { action: transfer, from: Store A, to: Store B, qty: 2 }
└─ Record #3: { action: sale, warehouse_from: Store B, customer: John, qty: 1 }

Complete Narrative:
"iPhone 13 entered Store A from supplier (5 units) →
 2 units transferred to Store B →
 1 unit sold to John Doe for 100€"
```

## 🎨 UI Component Structure

```
SEARCH PAGE
═════════════════════════════════════════════════════════════════

┌─────────────────────────────────────────────────────────────┐
│                    PRODUCT HISTORY                          │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  SEARCH SECTION                                              │
│  ┌─────────────────────────────────────────────────────┐   │
│  │ Search: [_______________________]                  │   │
│  │ Type: [▼ Name/SKU/Barcode/IMEI]                    │   │
│  │                              [Search Button]        │   │
│  └─────────────────────────────────────────────────────┘   │
│                                                               │
│  RESULTS SECTION (if search executed)                        │
│  ┌─────────────────────────────────────────────────────┐   │
│  │ No results found                                     │   │
│  │                              or                      │   │
│  │ ID│ Product     │ Category │ Price │ Actions        │   │
│  │──────────────────────────────────────────────────────│   │
│  │ 1 │iPhone 13   │ Phones   │ 50.00 │ [View]         │   │
│  │ 2 │iPhone 14   │ Phones   │ 75.00 │ [View]         │   │
│  │ 3 │Samsung S21 │ Phones   │ 45.00 │ [View]         │   │
│  └─────────────────────────────────────────────────────┘   │
│                                                               │
└─────────────────────────────────────────────────────────────┘


HISTORY TIMELINE PAGE
═════════════════════════════════════════════════════════════════

┌─────────────────────────────────────────────────────────────┐
│ iPhone 13                              [Export CSV] [Back]   │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│ STATS                                                        │
│ ┌────────────────────────────────────────────────────────┐  │
│ │ Total Movements: 6  │  Status: Sold  │  Last: 17/05   │  │
│ └────────────────────────────────────────────────────────┘  │
│                                                               │
│ [Timeline View] [Table View]                                │
│                                                               │
│ TIMELINE (active)                                            │
│ ┌────────────────────────────────────────────────────────┐  │
│ │                                                        │  │
│ │  📦 Stock Entry      (15/05/2026 10:30)               │  │
│ │  ├─ Warehouse: Store A                                │  │
│ │  ├─ Supplier: ABC Company                             │  │
│ │  ├─ Qty: 5, Price: 50.00€                             │  │
│ │                                                        │  │
│ │  ↓ (Timeline line)                                     │  │
│ │                                                        │  │
│ │  🔄 Transfer        (16/05/2026 14:15)                │  │
│ │  ├─ From: Store A → To: Store B                       │  │
│ │  ├─ User: Admin                                       │  │
│ │  ├─ Qty: 2                                            │  │
│ │                                                        │  │
│ │  ↓ (Timeline line)                                     │  │
│ │                                                        │  │
│ │  💰 Sale            (17/05/2026 09:45)                │  │
│ │  ├─ Store: Store B                                    │  │
│ │  ├─ Customer: John Doe (+355 692 123 456)             │  │
│ │  ├─ Qty: 1, Price: 100.00€                            │  │
│ │  ├─ Invoice: INV-001                                  │  │
│ │  └─ Status: Sold                                      │  │
│ │                                                        │  │
│ └────────────────────────────────────────────────────────┘  │
│                                                               │
└─────────────────────────────────────────────────────────────┘
```

## 🔌 Integration Points

```
EXISTING SYSTEM
═════════════════════════════════════════════════════════════════

Products Module
├─ Product Model
│  └─ Added: histories() relationship
│  └─ Added: purchaseItems() relationship
└─ Products Index View
   └─ Added: "View History" button

Sales Module
├─ SaleItem Model
│  └─ Observed by: SaleItemObserver
│     ├─ On create → recordProductHistory(sale)
│     ├─ On update → record quantity changes
│     └─ On delete → recordProductHistory(sale_cancel)
└─ Sale Controller
   └─ No changes needed (auto-tracking via observer)

Purchase Module
├─ PurchaseItem Model
│  └─ Observed by: PurchaseItemObserver
│     ├─ On create → recordProductHistory(stock_entry)
│     ├─ On update → record quantity changes
│     └─ On delete → recordProductHistory(stock_removal)
└─ Purchase Controller
   └─ No changes needed (auto-tracking via observer)

Admin Panel
├─ Sidebar
│  └─ Added: "Histori Produkti" menu item
└─ Layout
   └─ No changes needed
```

## 📈 Growth Projection

```
TYPICAL USAGE PATTERN
═════════════════════════════════════════════════════════════════

Daily Operations:
  100 products in stock
  × 0.5 movements per product per day (average)
  = 50 history records per day

Monthly:
  50 records × 30 days = 1,500 records

Yearly:
  50 records × 365 days = 18,250 records

With strategic indexing:
  ✓ Query time remains < 50ms even at 1,000,000 records
  ✓ Search queries < 200ms
  ✓ Export time < 5 seconds for 10,000 records
```

## 🔐 Security & Audit Trail

```
IMMUTABLE HISTORY
═════════════════════════════════════════════════════════════════

Original Record:
┌────────────────────────────────────────────────────────┐
│ id: 1                                                  │
│ product_id: 45                                         │
│ action_type: stock_entry                              │
│ warehouse_to_id: 1                                     │
│ user_id: 3                                             │
│ quantity: 5                                            │
│ purchase_price: 50.00                                  │
│ created_at: 2026-05-15 10:30:00                        │
│ updated_at: 2026-05-15 10:30:00                        │
└────────────────────────────────────────────────────────┘

NO EDITING OR DELETION:
- Records are created once and remain unchanged
- If correction needed, add new record explaining issue
- Complete audit trail maintained
- Regulatory compliance ready

Benefits:
✓ Immutable audit trail
✓ Regulatory compliance (GDPR, etc.)
✓ Complete traceability
✓ Dispute resolution capability
```

---

**This visual guide provides a complete understanding of the Product History system architecture, data flows, and integration points.**

