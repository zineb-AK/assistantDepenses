## Why

Users need to browse all their expenses across receipts in one place, not receipt by receipt. Currently depenses are only visible inside each receipt's show page. A dedicated expense list with category filtering gives the shop owner quick visibility on spending by category (alimentaire, boissons, hygiène, etc.).

## What Changes

- **Expense list page** — shows all of the user's depenses grouped or listed with receipt context
- **Category filter** — dropdown to filter by `DepenseCategorie` enum values
- **Total by category** — aggregate display showing spending per category
- **DepenseController** — implement `index()` with optional category filter, eager-load receipts for N+1 safety

## Capabilities

### New Capabilities
- `expenses-dashboard`: Dedicated expense list with category filtering, receipt context, and category totals

### Modified Capabilities

*(None — first version of this feature)*

## Impact

- **Modified:** `app/Http/Controllers/DepenseController.php` — add `index()` with filter logic
- **New:** `resources/views/depenses/index.blade.php` — expense table with filter form
- **New:** `resources/views/depenses/_category_totals.blade.php` — partial for category aggregation
- **Routes:** `GET /depenses` with optional `?categorie=` query param
