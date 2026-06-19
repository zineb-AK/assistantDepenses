# OpenSpec — Feature 03: Expenses Dashboard

**Version:** 1.0.0
**Status:** Approved
**Covers:** US8
**Author:** Human Architect + OpenCode (AI)

---

## 1. Proposal

### Problem

Si Brahim's core need is not just to process receipts — it's to *understand* where his money goes. After receipts are processed, the extracted `Depense` records sit in the database but are invisible to him. He needs a single view that aggregates all his expenses, lets him filter by category, and presents the data cleanly.

### Solution

A `DepenseController@index` view that:
1. Loads all `Depense` records belonging to the authenticated user (via the `Recu` relationship).
2. Supports filtering by `categorie` via a query string parameter (`?categorie=alimentaire`).
3. Uses **Eager Loading** (`with('recu')`) to avoid N+1 queries when displaying the source receipt for each expense.
4. Displays category as a formatted, colour-coded badge (driven by the `DepenseCategorie` enum's `label()` and `badgeClass()` methods — defined in Feature 02).
5. Shows a per-category total and a grand total.

### What this feature is NOT

- Not a receipt management view (Feature 01).
- Not responsible for triggering extraction (Features 01 + 02).
- Not a charts/analytics dashboard (out of scope for this brief).

---

## 2. Specification

### 2.1 Route

```php
// routes/web.php — inside auth middleware group
Route::get('/depenses', [DepenseController::class, 'index'])->name('depenses.index');
```

No `resource()` helper needed — expenses are read-only in this view. They are created by the Job (Feature 02) and deleted by cascading delete when a `Recu` is deleted (Feature 01).

### 2.2 Controller — `App\Http\Controllers\DepenseController`

**The N+1 problem this feature must avoid:**

```php
// WRONG — triggers one query per depense to load its recu
$depenses = Depense::all();
foreach ($depenses as $d) {
    echo $d->recu->texte_brut; // N+1 here
}

// CORRECT — loads all recus in a single IN query
$depenses = Depense::with('recu')->...->get();
```

**Full controller method:**

```php
namespace App\Http\Controllers;

use App\Enums\DepenseCategorie;
use App\Models\Depense;
use Illuminate\Http\Request;

class DepenseController extends Controller
{
    public function index(Request $request)
    {
        // Validate the filter — only accepted enum values pass through
        $categorie = null;
        if ($request->filled('categorie')) {
            $categorie = DepenseCategorie::tryFrom($request->input('categorie'));
            // tryFrom returns null for invalid values — no 500 from bad URLs
        }

        $query = Depense::query()
            ->whereHas('recu', fn ($q) => $q->where('user_id', auth()->id()))
            ->with('recu')          // ← Eager load: prevents N+1
            ->latest('depenses.created_at');

        if ($categorie !== null) {
            $query->where('categorie', $categorie->value);
        }

        $depenses    = $query->get();
        $categories  = DepenseCategorie::cases(); // For the filter UI
        $grandTotal  = $depenses->sum(fn ($d) => $d->quantite * $d->prix_unitaire);

        // Per-category totals (for summary row)
        $totalParCategorie = $depenses->groupBy(fn ($d) => $d->categorie->value)
            ->map(fn ($group) => $group->sum(fn ($d) => $d->quantite * $d->prix_unitaire));

        return view('depenses.index', compact(
            'depenses',
            'categories',
            'categorie',
            'grandTotal',
            'totalParCategorie',
        ));
    }
}
```

**Why `whereHas` instead of a join?** Si Brahim's expenses must be scoped to his own receipts. `whereHas('recu', fn($q) => $q->where('user_id', auth()->id()))` is readable, leverages Eloquent's relationship definition, and is correct even if `recu_id` is somehow shared (it won't be, but defensive scoping costs nothing).

**Why `tryFrom` instead of `from`?** `DepenseCategorie::from('invalid')` throws a `ValueError`. A user manually editing the URL should not see a 500 page — `tryFrom` returns `null` for unknown values, and the query simply runs unfiltered.

### 2.3 Eager Loading — N+1 Verification Checklist

Before marking this feature done, the developer must open Debugbar on `/depenses` and confirm:

- [ ] Total queries ≤ 3 (auth user lookup + depenses query + recus eager-load).
- [ ] No query has a pattern like `WHERE recu_id = ?` repeated N times in the query log.
- [ ] Adding `?categorie=alimentaire` to the URL does not increase the query count.

If Debugbar shows N+1, the fix is always the same: ensure `->with('recu')` is present on the query builder chain before `->get()`.

### 2.4 View — `depenses/index.blade.php`

#### Filter Bar

```blade
<form method="GET" action="{{ route('depenses.index') }}" class="flex flex-wrap gap-2 mb-6">
    <a href="{{ route('depenses.index') }}"
       class="px-3 py-1.5 rounded-full text-sm font-medium
              {{ is_null($categorie) ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
        Toutes
    </a>

    @foreach($categories as $cat)
        <a href="{{ route('depenses.index', ['categorie' => $cat->value]) }}"
           class="px-3 py-1.5 rounded-full text-sm font-medium {{ $cat->badgeClass() }}
                  {{ $categorie?->value === $cat->value ? 'ring-2 ring-offset-1 ring-gray-400' : '' }}">
            {{ $cat->label() }}
        </a>
    @endforeach
</form>
```

> Filter links are plain `<a>` tags, not a `<form>`. This avoids a POST for a read operation, keeps the URL bookmarkable, and requires zero JavaScript.

#### Expenses Table

```blade
<div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left font-semibold text-gray-600">Libellé</th>
                <th class="px-4 py-3 text-center font-semibold text-gray-600">Qté</th>
                <th class="px-4 py-3 text-right font-semibold text-gray-600">Prix unit.</th>
                <th class="px-4 py-3 text-right font-semibold text-gray-600">Total ligne</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-600">Catégorie</th>
                <th class="px-4 py-3 text-left font-semibold text-gray-600">Reçu du</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 bg-white">
            @forelse($depenses as $depense)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $depense->libelle }}</td>
                    <td class="px-4 py-3 text-center text-gray-700">{{ $depense->quantite }}</td>
                    <td class="px-4 py-3 text-right text-gray-700">
                        {{ number_format($depense->prix_unitaire, 2) }} MAD
                    </td>
                    <td class="px-4 py-3 text-right font-semibold text-gray-900">
                        {{ number_format($depense->quantite * $depense->prix_unitaire, 2) }} MAD
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                     {{ $depense->categorie->badgeClass() }}">
                            {{ $depense->categorie->label() }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">
                        {{ $depense->recu->created_at->format('d/m/Y') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-10 text-center text-gray-400">
                        @if($categorie)
                            Aucune dépense dans la catégorie « {{ $categorie->label() }} ».
                            <a href="{{ route('depenses.index') }}" class="underline">Voir toutes.</a>
                        @else
                            Aucune dépense pour l'instant. Soumettez un reçu pour commencer.
                        @endif
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($depenses->isNotEmpty())
        <tfoot class="bg-gray-50 border-t-2 border-gray-300">
            <tr>
                <td colspan="3" class="px-4 py-3 text-right font-semibold text-gray-700">
                    Total{{ $categorie ? ' (' . $categorie->label() . ')' : ' général' }}
                </td>
                <td class="px-4 py-3 text-right font-bold text-gray-900">
                    {{ number_format($grandTotal, 2) }} MAD
                </td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
        @endif
    </table>
</div>
```

#### Empty State

The `@forelse` / `@empty` block handles the empty state inline. The empty message varies:
- When a category filter is active: explain why the list is empty and offer a link to reset.
- When no filter: prompt the user to submit their first receipt.

This follows the design principle: *An empty screen is an invitation to act.*

### 2.5 Navigation

Add a "Dépenses" link to `layouts/app.blade.php` navigation, with active state:

```blade
<a href="{{ route('depenses.index') }}"
   class="{{ request()->routeIs('depenses.*') ? 'text-indigo-600 font-semibold' : 'text-gray-600 hover:text-gray-900' }}">
    Dépenses
</a>
```

### 2.6 Enum-Driven Architecture — Why This Matters

The `DepenseCategorie` enum (defined in Feature 02) is doing significant work in this view:

- `$cat->value` → the raw database string used for filtering
- `$cat->label()` → the human-readable French label displayed in the badge and filter
- `$cat->badgeClass()` → the Tailwind CSS classes for colour-coding

No string mapping, no switch statement in the view, no risk of a typo causing a white badge. If a new category is added in the future, the enum is the only place to update — the view and controller adapt automatically.

### 2.7 Pagination (Scalability Note)

For Si Brahim's current volume (tens of receipts per month), `->get()` is fine. If the record count grows significantly, replace `->get()` with `->paginate(50)` and add `{{ $depenses->appends(request()->query())->links() }}` below the table. The filter query string is preserved via `appends()`. This change requires no architecture rework.

---

## 3. Tasks

| # | Task | Owner | Done? |
|---|---|---|---|---|
| T03-1 | Create `DepenseController` with `index()` method | OpenCode | ✅ |
| T03-2 | Register `/depenses` route in `routes/web.php` | OpenCode | ✅ |
| T03-3 | Build `depenses/index.blade.php` with filter bar + table + empty state | OpenCode | ✅ |
| T03-4 | Add "Dépenses" link with active state to `layouts/app.blade.php` nav | OpenCode | ✅ |
| T03-5 | **Human verification:** Open Debugbar on `/depenses` — confirm ≤ 3 queries, zero N+1 | Human | ☐ |
| T03-6 | **Human verification:** Apply `?categorie=alimentaire` filter — confirm only alimentaire rows show, total updates correctly | Human | ☐ |
| T03-7 | **Human verification:** Apply `?categorie=invalid_value` to URL manually — confirm no 500, page loads unfiltered | Human | ☐ |
| T03-8 | **Human verification:** Empty state shows when no depenses match filter — confirm call-to-action link works | Human | ☐ |
| T03-9 | Commit: `[AI] feat(depenses): filterable dashboard with enum badges and N+1-safe eager loading` | Human | ✅ |
