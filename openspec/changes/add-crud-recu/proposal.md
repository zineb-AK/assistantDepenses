## Why

Users need to submit supplier receipts and view extracted expenses. Currently there is no way to create, browse, or delete receipts — the app has no UI beyond authentication. This change delivers the core receipt management flow: submit raw text, see processing status, view extracted line items, and remove receipts.

## What Changes

- **Receipt submission form** — authenticated user pastes receipt text and submits
- **Receipt list** — table showing all user's receipts with status label and expense count
- **Receipt detail view** — full receipt text, status, and extracted expenses
- **Receipt deletion** — delete a receipt and cascade-delete its expenses
- **Validation** — `StoreRecuRequest` with `min:10, max:10000` on `texte_source`
- **Async dispatch** — submission dispatches `ExtraireDepensesDuRecu` job immediately
- **Flash messages** — success/error feedback after create and delete actions
- **French UI** — all labels, messages, and status badges in French

## Capabilities

### New Capabilities
- `receipts-crud`: Create, list, show, and delete receipts with status tracking, expense display, and async AI extraction dispatch

### Modified Capabilities

*(None — first feature capability)*

## Impact

- **New files:** `app/Http/Controllers/RecuController.php`, `app/Http/Requests/StoreRecuRequest.php`, `resources/views/recus/index.blade.php`, `resources/views/recus/create.blade.php`, `resources/views/recus/show.blade.php`, `database/migrations/*_create_recus_table.php`, `database/migrations/*_create_depenses_table.php`
- **New routes:** `GET /recus`, `GET /recus/create`, `POST /recus`, `GET /recus/{recu}`, `DELETE /recus/{recu}`
- **Dependencies:** Laravel Breeze (auth scaffold), `laravel/ai` SDK, database queue driver
- **Eloquent:** `Recu` and `Depense` models with enums (`RecuStatus`, `DepenseCategorie`)
- **Async:** `ExtraireDepensesDuRecu` job dispatched on submission (extraction logic is a separate change)
