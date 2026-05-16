# Product History Views

This directory contains all Blade views for the Product History module.

## Files

### index.blade.php
Main search page for finding products and accessing their history.

**Features:**
- Product search with multiple search types
- Real-time search results
- Modal integration for quick history viewing
- Responsive design

**Route:** `/product-history/`

---

### show.blade.php
Full product history timeline page with complete movement details.

**Features:**
- Product information and statistics
- Timeline view (default)
- Table view (togglable)
- Pagination support
- Export functionality
- Responsive design

**Route:** `/product-history/product/{product}`

**Parameters:**
- `product` - Product model instance

---

### modal.blade.php
Partial view for displaying product history in a modal dialog.

**Features:**
- Compact timeline display
- Suitable for modal integration
- No pagination (shows all records)
- Quick reference view

**Route:** Included via AJAX in `/product-history/product/{product}/modal`

**Parameters:**
- `product` - Product model instance
- `histories` - Collection of ProductHistory records

---

## View Data

### index.blade.php

**Variables passed from controller:**
- `warehouses` - Collection of warehouse options for filtering

**User interactions:**
- Search input for product names
- Dropdown for search type selection
- Search button to trigger AJAX search
- View buttons to open history modals

---

### show.blade.php

**Variables passed from controller:**
- `product` - Product model with relationships
- `histories` - Paginated collection of ProductHistory records
- `timeline` - Full collection for timeline display
- `stats` - Array with movement statistics

**Array structure:**
```php
$stats = [
    'total_movements' => int,
    'current_status' => string,
    'last_movement' => ProductHistory object|null,
]
```

**View toggles:**
- Timeline view (default) - Vertical timeline with detailed cards
- Table view - Compact table format for quick scanning

---

### modal.blade.php

**Variables passed from controller:**
- `product` - Product model
- `histories` - Collection of ProductHistory records

**Styling:**
- Uses Bootstrap grid system
- Timeline components with custom CSS
- Responsive badges and icons
- Print-friendly layout

---

## Styling

### Timeline Styling

```css
.timeline-item          - Container for each timeline item
.timeline-line          - Vertical line connecting items
.timeline-dot           - Circle marking each event
.timeline-content       - Content area for event details
.text-sm                - Small text utility class
```

### Colors and Badges

**Action type colors:**
- success - Stock entry (Hyrje Stoku)
- info - Store transfer (Transferim)
- warning - Product return (Kthim)
- primary - Sale (Shitje)
- danger - Sale cancellation (Anullim)
- secondary - Repair/Service (Riparim)
- dark - Stock removal (Heqje)

**Status colors:**
- success - Active (Aktiv)
- warning - Returned (I Kthyer)
- primary - Sold (I Shitur)
- info - Repaired (I Riparuar)
- danger - Removed (I Hequr)

---

## JavaScript Integration

### index.blade.php

**Event handlers:**
- `searchBtn.click()` - Trigger product search
- `searchInput.keypress()` - Enter key search
- `viewProductHistory()` - Open history modal

**API calls:**
- GET `/product-history/search` - Search products

---

### show.blade.php

**Event handlers:**
- `#timelineView.click()` - Switch to timeline view
- `#tableView.click()` - Switch to table view

**Functionality:**
- View toggle between timeline and table
- Button state management
- Container visibility switching

---

## Responsive Behavior

### Mobile (< 768px)
- Single column layout
- Full-width search boxes
- Compact badges
- Touch-friendly buttons
- Stacked timeline items

### Tablet (768px - 1024px)
- Two-column layout where applicable
- Optimized spacing
- Readable font sizes

### Desktop (> 1024px)
- Full multi-column layout
- Expanded details
- Optimal spacing and readability

---

## Internationalization

All text is in Albanian (Albanian language support). Key terms:

- "Histori Produkti" - Product History
- "Kërkimi i Produkteve" - Product Search
- "Kronologjia e Lëvizjeve" - Movement Timeline
- "Hyrje Stoku" - Stock Entry
- "Shitje" - Sale
- "Transferim Magazine" - Store Transfer
- "Kthim Produkti" - Product Return
- "Riparim/Servis" - Repair/Service
- "Heqje Stoku" - Stock Removal
- "Anullim Shitje" - Sale Cancellation

---

## Performance Considerations

1. **Pagination**: History records use pagination (50 items per page)
2. **Eager Loading**: Relationships are eager-loaded to prevent N+1 queries
3. **Indexes**: Database indexes on frequently queried columns
4. **Caching**: Consider implementing view caching for frequently accessed products

---

## Accessibility

- Semantic HTML structure
- ARIA labels on interactive elements
- Keyboard navigation support
- Color-independent information (using icons and text)
- High contrast badges and text

---

## Customization

### Add New Action Type
1. Update the action type in the migration/model
2. Add color and icon mappings in ProductHistory model
3. Update Albanian text in model attributes
4. Update views to handle new type

### Change Timeline Styling
Edit the `<style>` sections in:
- `show.blade.php` - Main timeline styles
- `modal.blade.php` - Modal timeline styles

### Add New Search Type
1. Update search form in `index.blade.php`
2. Add search logic in ProductHistoryController::search()
3. Update ProductHistory scope if needed

---

## Known Limitations

1. Modal view doesn't paginate (shows all records)
2. Export only CSV format (PDF coming soon)
3. No real-time notifications yet
4. History records are immutable (design choice)

---

## Future Enhancements

- Advanced filtering UI
- Custom date ranges with calendar picker
- Batch operations
- Print-friendly templates
- PDF export
- Email notifications
- Real-time activity stream
- Advanced analytics charts

---

**Last Updated**: May 16, 2026
