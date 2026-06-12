## Context

Depenses currently exist as a child relationship of Recu — visible only on the receipt show page. Users want a cross-receipt view to monitor spending by category. No new data model is needed; the existing `Depense` model, `DepenseCategorie` enum, and the `belongsTo Recu` relation already support this. The implementation is a controller method + view + route.

## Goals / Non-Goals

**Goals:**
- Authenticated users can view all their depenses across all receipts on one page
- Category filter via `?categorie=` query param, defaulting to "all"
- Show receipt context (receipt ID, date) alongside each depense
- Display category totals (sum of `prix_unitaire * quantite` per category)
- Eager-load `recu` relation to avoid N+1 on the list

**Non-Goals:**
- Editing or deleting individual depenses (out of scope for v1)
- Date range filtering (deferred)
- Export (deferred)
- Pagination (v1 assumes manageable data volume; add later if needed)

## Decisions

### Decision: `DepenseController@index` with scoped query
All depenses are fetched through `auth()->user()->recus()->with('depenses')` and then collapsed, or directly via `Depense::whereIn('recu_id', ...)`. Using `Depense::whereHas('recu', fn($q) => $q->where('user_id', auth()->id()))` with eager-loaded `recu` is simpler and provides direct depense model access for filtering.

### Decision: Category filter via query param, not separate route
`GET /depenses?categorie=alimentaire` keeps routing simple. An invalid or missing category value shows all depenses. The filter uses the `DepenseCategorie` enum for validation.

### Decision: Category totals computed in controller
A simple collection of `->selectRaw('categorie, SUM(quantite * prix_unitaire) as total')->groupBy('categorie')` gives per-category totals. Lightweight, no extra queries.

## Risks / Trade-offs

- **[Risk] No pagination** → Acceptable for v1. Add `paginate(50)` if performance degrades.
- **[Risk] Invalid category param silently ignored** → Mitigation: validate against `DepenseCategorie` cases, return empty result or all if invalid.
- **[Trade-off] No date filter yet** → The `created_at` on depenses can be used later for a date range filter.
