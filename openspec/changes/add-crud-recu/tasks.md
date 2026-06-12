## 1. Migrations & Models

- [x] 1.1 Create `create_recus_table` migration with columns: id, user_id (FK→users, cascade), texte_source (text), statut (string, default 'pending'), payload_ia (json, nullable), created_at/updated_at
- [x] 1.2 Create `create_depenses_table` migration with columns: id, recu_id (FK→recus, cascade), libelle (string), quantite (integer, default 1), prix_unitaire (decimal 10,2, default 0.00), categorie (string), created_at/updated_at
- [x] 1.3 Create `RecuStatus` enum (Pending= 'pending', Processed='processed', Failed='failed') with `label(): string` French display method
- [x] 1.4 Create `DepenseCategorie` enum (Alimentaire, Boissons, Hygiene, Entretien, Autre) with `label(): string` French display method
- [x] 1.5 Create `Recu` model with `$fillable`, `$casts` (statut→RecuStatus, payload_ia→array), `belongsTo User`, `hasMany Depense`
- [x] 1.6 Create `Depense` model with `$fillable`, `$casts` (categorie→DepenseCategorie), `belongsTo Recu`

## 2. Controllers & Validation

- [x] 2.1 Create `StoreRecuRequest` FormRequest with: `texte_source` required, string, min:10, max:10000, authorize() returns `auth()->check()`
- [x] 2.2 Create `RecuController` with `index()` — fetch `auth()->user()->recus()->with('depenses')->latest()->get()`
- [x] 2.3 Add `create()` method returning the receipt form view
- [x] 2.4 Add `store(StoreRecuRequest $request)` — create recu with `statut = pending`, dispatch `ExtraireDepensesDuRecu`, redirect with flash
- [x] 2.5 Add `show(Recu $recu)` — load `$recu->load('depenses')`, verify ownership (404 if not owner)
- [x] 2.6 Add `destroy(Recu $recu)` — verify ownership, delete, redirect with flash
- [x] 2.7 Create `DepenseController` (stub for future feature — no routes yet)

## 3. Routes

- [x] 3.1 Register resourceful routes for `recus` inside `auth` middleware group: index, create, store, show, destroy

## 4. Blade Views

- [x] 4.1 Create `recus/index.blade.php` — table with statut badges (color-coded), depense count, Voir/Supprimer links, empty state
- [x] 4.2 Create `recus/create.blade.php` — form with textarea, validation errors, submit button "Analyser le reçu"
- [x] 4.3 Create `recus/show.blade.php` — receipt text display, statut badge, depenses table, back link
- [x] 4.4 Create shared flash message partial for success/error notifications

## 5. Job Stub

- [x] 5.1 Create `ExtraireDepensesDuRecu` job stub with `handle()` — accepts `Recu`, placeholder (full AI extraction in separate change)
- [x] 5.2 Dispatch job in `store()` method

## 6. Tests

- [x] 6.1 Test receipt list requires authentication
- [x] 6.2 Test receipt creation with valid data — asserts recu created, job dispatched, flash message
- [x] 6.3 Test receipt creation validation — short text (<10 chars) and long text (>10000 chars)
- [x] 6.4 Test receipt show — owner can view, non-owner gets 404
- [x] 6.5 Test receipt deletion — owner can delete, cascade verified, non-owner gets 404
- [x] 6.6 Test that unauthenticated users are redirected to login for all receipt routes
