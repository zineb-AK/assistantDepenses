## 1. Depense Controller

- [x] 1.1 Implement `DepenseController@index` — fetch user's depenses via `Depense::whereHas('recu', fn($q) => $q->where('user_id', auth()->id()))`, eager-load `recu`, order by `created_at` desc
- [x] 1.2 Add category filter logic — if `request('categorie')` is a valid `DepenseCategorie` value, apply `->where('categorie', $value)`
- [x] 1.3 Compute category totals — `selectRaw('categorie, SUM(quantite * prix_unitaire) as total')->groupBy('categorie')`
- [x] 1.4 Pass filtered depenses and totals to `depenses.index` view

## 2. Routes

- [x] 2.1 Add `GET /depenses` route pointing to `DepenseController@index` inside the `auth` middleware group, named `depenses.index`

## 3. Blade View

- [x] 3.1 Create `resources/views/depenses/index.blade.php` — category filter dropdown, totals summary section, depenses table with libelle/quantite/prix_unitaire/categorie/recu link, empty state

## 4. Tests

- [x] 4.1 Test expense list requires authentication
- [x] 4.2 Test expense list shows all user's depenses across receipts with eager-loaded recu
- [x] 4.3 Test category filter filters correctly
- [x] 4.4 Test invalid category filter shows all depenses
- [x] 4.5 Test expense list data isolation (user cannot see another user's depenses)
- [x] 4.6 Test category totals are computed and displayed
