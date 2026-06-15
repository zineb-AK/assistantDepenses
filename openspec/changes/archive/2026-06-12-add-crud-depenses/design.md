## Context

Depenses currently exist as a child relationship of Recu — visible only on the receipt show page. Users want a cross-receipt view to monitor spending by category. No new data model is needed; the existing `Depense` model, `DepenseCategorie` enum, and the `belongsTo Recu` relation already support this.

## Goals / Non-Goals

**Goals:**
- Authenticated users can view all their depenses across all receipts on one page
- Category filter via `?categorie=` query param, defaulting to "all"
- Show receipt context (receipt ID, date) alongside each depense
- Display category totals (sum of `prix_unitaire * quantite` per category)
- Eager-load `recu` relation to avoid N+1

**Non-Goals:**
- Editing or deleting individual depenses
- Date range filtering
- Pagination (v1 assumes manageable data volume)

## Decisions

### Decision: `DepenseController@index` with scoped query
Using `Depense::whereHas('recu', fn($q) => $q->where('user_id', auth()->id()))` with eager-loaded `recu` provides direct depense model access for filtering while enforcing data isolation.

### Decision: Category filter via query param
`GET /depenses?categorie=alimentaire` keeps routing simple. Invalid category values are silently ignored (show all).

### Decision: Category totals computed via DB query
`selectRaw('categorie, SUM(quantite * prix_unitaire) as total')->groupBy('categorie')` with optional `where` for filter. Lightweight, single query.

## Risks / Trade-offs

- **[Risk] No pagination** → Acceptable for v1. Add `paginate(50)` if performance degrades.
- **[Risk] Invalid category param** → Mitigation: validate against `DepenseCategorie::tryFrom()`, ignore if invalid.
