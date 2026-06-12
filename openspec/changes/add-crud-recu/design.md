## Context

This project transforms raw supplier receipt text into structured expense records via AI extraction. The auth scaffold exists (Laravel Breeze). This change adds the receipt CRUD layer — the first user-facing feature. Receipt submission must feel instant: save to DB with `pending` status, dispatch an async job, and return immediately.

## Goals / Non-Goals

**Goals:**
- Authenticated users can create, list, view, and delete their own receipts
- Each receipt displays its status (`pending`/`processed`/`failed`) with French labels and colored badges
- Receipt show view lists extracted `Depense` rows (libelle, quantite, prix_unitaire, categorie)
- Deleting a receipt cascade-deletes all its depenses
- Submission dispatches `ExtraireDepensesDuRecu` to the queue
- N+1 prevention: eager-load depenses on index and show

**Non-Goals:**
- AI extraction logic (handled by `ExtraireDepensesDuRecu` job — separate change)
- Expense editing or update (out of scope for v1)
- Receipt pagination (v1 assumes < 100 receipts per user)
- Image upload (deferred to bonus feature)

## Decisions

### Decision: Scoped queries via `auth()->user()->recus()` instead of Policies
A Policy is correct but adds ceremony for a single-model app. Scoped queries on the `User` relationship are simpler and equally secure. If authorization rules grow complex later, extract into a `ReceiptPolicy`.

### Decision: Soft deletes NOT used
Receipt and depense deletion is permanent (no undo needed). The model uses hard deletes for simplicity. If soft deletes become necessary, add them later.

### Decision: Flash messages via `with()` for create and delete
Standard Laravel `->with('success', '...')` approach. Keeps things simple without session drivers or event-based flash.

### Decision: French UI from day one
All labels, status badges, buttons, and flash messages use French. Enums expose a `label(): string` method returning French display strings.

### Decision: `StatutRecu` enum values are English in DB, French in UI
DB stores `pending`/`processed`/`failed`. The `label()` method maps to French. This avoids encoding-dependent issues with French characters in enum cases and keeps DB values predictable.

## Risks / Trade-offs

- **[Risk] Job dispatch on create couples CRUD to queue** → Mitigation: dispatch is fire-and-forget. If queue is down, receipt still saves with `pending` status. The Job retries later via `$tries` / `backoff`.
- **[Risk] cascade delete removes depenses without confirmation** → Mitigation: show a confirmation page/modal before delete. Standard Laravel `DELETE` form with `@method('DELETE')` + confirmation dialog.
- **[Trade-off] No pagination on receipt index** → Acceptable for v1. Add `paginate()` when users exceed ~50 receipts. Eager-loading (`with('depenses')`) still protects against N+1.
- **[Trade-off] `texte_source` as TEXT (not VARCHAR)** → Allows future OCR paste or long receipts. Add `max:10000` validation to prevent abuse.
