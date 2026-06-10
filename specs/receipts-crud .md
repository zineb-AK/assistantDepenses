# OpenSpec — Feature 01: Receipts CRUD

**Version:** 1.0.0
**Status:** Approved
**Covers:** US1, US2, US3, US4, US5
**Author:** Human Architect + OpenCode (AI)

---

## 1. Proposal

### Problem

Si Brahim has no way to submit a receipt, track its processing state, or consult the list of past receipts. Without this layer, the AI extraction (Feature 02) has nothing to attach to and the user has no feedback loop.

### Solution

A standard Laravel monolith CRUD for `Recu` (Receipt) records, implemented entirely in Blade + Tailwind. The critical UX constraint: submitting a receipt must **never block the page**. The Blade form posts synchronously (standard HTTP), the controller creates the record and dispatches the background job, then redirects immediately — the job runs in the background. The index page shows live status by polling a lightweight JSON endpoint every few seconds via vanilla JS `fetch`.

### What this feature is NOT

- Not a SPA. No Vue, no React, no Livewire.
- Not responsible for the AI extraction itself (that is Feature 02).
- Not responsible for expense listing or filtering (that is Feature 03).

---

## 2. Specification

### 2.1 Data Model — `recus` Table

| Column | Type | Notes |
|---|---|---|
| `id` | `bigIncrements` | PK |
| `user_id` | `foreignId` | `constrained()->cascadeOnDelete()` |
| `texte_brut` | `longText` | Raw receipt text as pasted by the user |
| `payload_ia` | `json` / `nullable` | Raw JSON returned by Groq, stored as-is for debugging |
| `statut` | `string` | Backed by `RecuStatus` enum, default `'pending'` |
| `created_at` / `updated_at` | `timestamps` | Standard |

**Eloquent Model: `App\Models\Recu`**

```php
// Relationships
public function depenses(): HasMany  // hasMany(Depense::class)
public function user(): BelongsTo

// Casts
protected $casts = [
    'payload_ia' => 'array',
    'statut'     => RecuStatus::class,
];
```

**Enum: `App\Enums\RecuStatus`**

```php
enum RecuStatus: string
{
    case Pending   = 'pending';
    case Processed = 'processed';
    case Failed    = 'failed';

    public function label(): string
    {
        return match($this) {
            self::Pending   => 'En attente',
            self::Processed => 'Traité',
            self::Failed    => 'Échoué',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Pending   => 'bg-yellow-100 text-yellow-800',
            self::Processed => 'bg-green-100 text-green-800',
            self::Failed    => 'bg-red-100 text-red-800',
        };
    }
}
```

### 2.2 Routes

```php
// routes/web.php — all behind auth middleware
Route::middleware('auth')->group(function () {
    Route::resource('recus', RecuController::class)
         ->only(['index', 'create', 'store', 'show', 'destroy']);

    // Lightweight polling endpoint — returns JSON only
    Route::get('recus/{recu}/status', [RecuController::class, 'status'])
         ->name('recus.status');
});
```

### 2.3 Form Request — `App\Http\Requests\StoreRecuRequest`

**Why a Form Request?** Validation must fail fast, before a Job is dispatched and before any API credit is consumed. A `StoreRecuRequest` also guarantees this rule is enforced regardless of how many places submit a receipt in the future.

```php
public function authorize(): bool
{
    return true; // Auth middleware already enforces login
}

public function rules(): array
{
    return [
        'texte_brut' => ['required', 'string', 'min:20', 'max:5000'],
    ];
}

public function messages(): array
{
    return [
        'texte_brut.required' => 'Le texte du reçu est obligatoire.',
        'texte_brut.min'      => 'Le reçu semble trop court (minimum 20 caractères).',
        'texte_brut.max'      => 'Le texte dépasse la limite autorisée (5 000 caractères).',
    ];
}
```

### 2.4 Controller — `App\Http\Controllers\RecuController`

**Philosophy:** Thin controller. It creates the record, dispatches the job, and gets out of the way.

```php
// index()  — list all recus for auth user, with depenses count
//            eager-load: Recu::with('depenses')->where('user_id', auth()->id())
//            order: latest() first

// create() — return view('recus.create')

// store()  — inject StoreRecuRequest
//            1. Create Recu with statut=pending, texte_brut, user_id
//            2. Dispatch ExtraireDepensesDuRecu::dispatch($recu)
//            3. Flash success: 'Reçu en cours de traitement.'
//            4. Redirect to recus.index

// show()   — find Recu by id, authorize user owns it
//            eager-load depenses
//            return view('recus.show', compact('recu'))

// destroy()— find Recu, authorize, delete (cascades to depenses via FK)
//            flash: 'Reçu supprimé.'
//            redirect to recus.index

// status() — JSON only, no view
//            return response()->json([
//                'statut'          => $recu->statut->value,
//                'statut_label'    => $recu->statut->label(),
//                'nb_depenses'     => $recu->depenses()->count(),
//            ]);
```

**Authorization pattern:** Use a simple inline check (`abort_if($recu->user_id !== auth()->id(), 403)`) or a `RecuPolicy`. The spec does not mandate a Policy for this scope, but OpenCode should create one if the project grows beyond 2 resource controllers.

### 2.5 Views

#### `recus/index.blade.php`

Displays a table with columns: **Date**, **Aperçu** (first 60 chars of `texte_brut`), **Statut** (badge), **Dépenses** (count), **Actions** (Voir / Supprimer).

The **non-blocking status update** is handled as follows:

```html
<!-- Each pending row gets a data attribute -->
<tr data-recu-id="{{ $recu->id }}"
    data-status="{{ $recu->statut->value }}"
    data-poll-url="{{ route('recus.status', $recu) }}">
```

```javascript
// resources/js/poll-status.js  (vanilla JS, no framework)
// Runs on DOMContentLoaded.
// 1. Find all rows where data-status === 'pending'
// 2. Every 3 seconds, fetch each row's data-poll-url
// 3. On response: update the badge text + class, update depenses count
// 4. If statut !== 'pending', stop polling that row
// 5. No page reload needed

document.addEventListener('DOMContentLoaded', () => {
    const pendingRows = [...document.querySelectorAll('[data-status="pending"]')];

    pendingRows.forEach(row => {
        const interval = setInterval(async () => {
            const res  = await fetch(row.dataset.pollUrl);
            const data = await res.json();

            // Update badge
            const badge = row.querySelector('.status-badge');
            badge.textContent = data.statut_label;
            // (badge class update handled by swapping Tailwind classes via JS)

            // Update count
            row.querySelector('.depenses-count').textContent = data.nb_depenses;

            // Stop polling when no longer pending
            if (data.statut !== 'pending') clearInterval(interval);
        }, 3000);
    });
});
```

> **Why polling and not WebSockets?** Si Brahim's hosting is likely shared or a basic VPS. Laravel Reverb or Pusher adds infrastructure complexity. Polling every 3 seconds is invisible to the user, costs nothing extra, and requires zero additional services.

#### `recus/create.blade.php`

- `<form method="POST" action="{{ route('recus.store') }}">` with `@csrf`
- `<textarea name="texte_brut">` with placeholder: *"Collez ici le texte de votre reçu fournisseur…"*
- `@error('texte_brut')` blade directive for inline validation errors
- Submit button: "Lancer l'extraction"
- On POST success, the user is **redirected immediately** — they never see a spinner waiting for Groq.

#### `recus/show.blade.php`

Two sections:
1. **Texte source** — `<pre class="whitespace-pre-wrap">{{ $recu->texte_brut }}</pre>`
2. **Dépenses extraites** — table with columns: Libellé, Quantité, Prix unitaire, Catégorie (formatted label), Total ligne
3. If `$recu->statut === RecuStatus::Failed`: show a red alert box ("L'extraction a échoué. Vous pouvez soumettre à nouveau ce reçu.")
4. If `$recu->statut === RecuStatus::Pending`: show a yellow info box ("Extraction en cours…") — no JS polling needed here, a manual page refresh is acceptable on the detail view.

### 2.6 Session Flash Messages

```php
// In controller, before redirect:
session()->flash('success', 'Reçu en cours de traitement.');
session()->flash('error',   'Reçu supprimé.');
```

```blade
{{-- In layouts/app.blade.php --}}
@if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded mb-4">
        {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded mb-4">
        {{ session('error') }}
    </div>
@endif
```

### 2.7 Authentication (US1)

Use Laravel's built-in scaffolding:

```bash
composer require laravel/breeze --dev
php artisan breeze:install blade
```

Breeze generates: login, register, logout, password reset — all in Blade. No customisation required for this feature. The `auth` middleware on the route group is sufficient.

---

## 3. Tasks

| # | Task | Owner | Done? |
|---|---|---|---|
| T01-1 | Run `php artisan breeze:install blade` and verify auth routes | OpenCode | ☐ |
| T01-2 | Create `RecuStatus` enum in `app/Enums/` | OpenCode | ☐ |
| T01-3 | Write migration `create_recus_table` with all columns above | OpenCode | ☐ |
| T01-4 | Write `Recu` Eloquent model with casts, fillable, relationships | OpenCode | ☐ |
| T01-5 | Write `StoreRecuRequest` with rules and messages | OpenCode | ☐ |
| T01-6 | Write `RecuController` with all 6 methods (index/create/store/show/destroy/status) | OpenCode | ☐ |
| T01-7 | Register routes in `routes/web.php` | OpenCode | ☐ |
| T01-8 | Build `recus/index.blade.php` with status badges table | OpenCode | ☐ |
| T01-9 | Build `recus/create.blade.php` with form and error display | OpenCode | ☐ |
| T01-10 | Build `recus/show.blade.php` with two-section layout | OpenCode | ☐ |
| T01-11 | Add flash message partials to `layouts/app.blade.php` | OpenCode | ☐ |
| T01-12 | Write `poll-status.js` and import in Vite (`app.js`) | OpenCode | ☐ |
| T01-13 | **Human verification:** Submit a receipt, confirm redirect is instant, confirm badge updates without page reload | Human | ☐ |
| T01-14 | **Human verification:** Debugbar — index page shows exactly 2 queries (users + recus with depenses count) | Human | ☐ |
| T01-15 | Commit: `[AI] feat(recus): CRUD scaffold with polling status update` | Human | ☐ |
