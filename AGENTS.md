# AGENTS.md — Assistant Dépenses

> **Discipline: Plan before Build.**
> No agent writes a single line of implementation code before a written plan has been produced, reviewed, and explicitly approved.

---

## Project Overview

**Assistant Dépenses** is a Laravel 12 monolith that transforms raw supplier receipts into structured, categorised expense records.

- **User:** Paste receipt text → AI extracts line items → stored, browsable, filterable
- **Stack:** Laravel 12 · PHP 8.3 · `laravel/ai` SDK · Groq API · MySQL · Laravel Queues (database driver) · Blade + Tailwind CSS (via Vite) · Pest
- **No React. No Inertia. Blade only.**

---

## Data Models

> OpenSpec must use these exact columns when generating migrations and specs.

### Table: `recus`

| Column | Type | Notes |
|---|---|---|
| `id` | `bigint` PK | auto-increment |
| `user_id` | `foreignId` | FK → `users.id`, cascade delete |
| `texte_source` | `text` | raw receipt input pasted by user |
| `statut` | `string` | cast: `RecuStatus` enum |
| `payload_ia` | `json` | cast: `array` — raw Groq response stored as-is |
| `created_at` / `updated_at` | `timestamps` | |

### Table: `depenses`

| Column | Type | Notes |
|---|---|---|
| `id` | `bigint` PK | auto-increment |
| `recu_id` | `foreignId` | FK → `recus.id`, cascade delete |
| `libelle` | `string` | extracted article label |
| `quantite` | `integer` | default 1 if missing in receipt |
| `prix_unitaire` | `decimal(10,2)` | default 0.00 if missing |
| `categorie` | `string` | cast: `DepenseCategorie` enum |
| `created_at` / `updated_at` | `timestamps` | |

### Eloquent Relations

```
User      hasMany  Recu
Recu      hasMany  Depense
Recu      belongsTo User
Depense   belongsTo Recu
```

---

## PHP Enums

```php
// app/Enums/RecuStatus.php
enum RecuStatus: string {
    case Pending   = 'pending';
    case Processed = 'processed';
    case Failed    = 'failed';
}

// app/Enums/DepenseCategorie.php
enum DepenseCategorie: string {
    case Alimentaire = 'alimentaire';
    case Boissons    = 'boissons';
    case Hygiene     = 'hygiene';
    case Entretien   = 'entretien';
    case Autre       = 'autre';
}
```

Display labels (for Blade views):
- `pending` → **En attente**
- `processed` → **Traité**
- `failed` → **Échoué**
- `alimentaire` → **Alimentaire**, `boissons` → **Boissons**, `hygiene` → **Hygiène**, `entretien` → **Entretien**, `autre` → **Autre**

---

## AI JSON Contract

> This is fixed. The `laravel/ai` SDK enforces this schema. Never deviate.

```json
{
  "articles": [
    {
      "libellé": "string",
      "quantité": "integer",
      "prix_unitaire": "number",
      "catégorie": "enum: alimentaire | boissons | hygiène | entretien | autre"
    }
  ],
  "total_estimé": "number",
  "devise": "string"
}
```

Fallback rules (injected in Job system prompt):
- Missing `quantité` → default `1`
- Missing `prix_unitaire` → default `0`
- Default `devise` → `"MAD"`
- Ambiguous category → `"autre"`

---

## Architecture Rules (Non-Negotiables)

| Concern | Rule |
|---|---|
| AI calls | Always via `laravel/ai` SDK — never raw `Http::post()` to Groq |
| Validation | Always in a `FormRequest` class — never `$request->validate()` inline |
| Async | Every AI call dispatched as a `Job` — controller dispatches, never waits |
| Type safety | Enums declared in `$casts` on models — never raw strings |
| N+1 | `with('depenses')` on every collection query — verified with Debugbar |
| Failure | Failed job sets `status = failed` on `Recu` and logs raw error — never silent |
| Schema | JSON contract enforced by SDK — no `json_decode` without schema validation |
| Queue driver | `database` (not `sync`) — configured in `.env` as `QUEUE_CONNECTION=database` |

---

## Feature Specs (for OpenSpec)

OpenSpec must generate one file per feature in `specs/`. Each spec must include: routes, controller methods, Form Request rules, Job logic, Blade views, and migration columns if new.

| File | Feature | User Stories |
|---|---|---|
| `specs/01_auth.md` | Authentication (register / login / logout) | US1 |
| `specs/02_receipts_crud.md` | Receipt list, submit, show, delete | US2 · US3 · US4 · US5 |
| `specs/03_ai_extraction_queue.md` | Job · AI extraction · structured output · status tracking | US6 · US7 |
| `specs/04_expenses_dashboard.md` | Expense list with category filter | US8 |
| `specs/05_image_upload_bonus.md` | Image upload + multimodal extraction (bonus) | Bonus |

---

## User Stories Reference

**US1** – Register / Login / Logout (Laravel Breeze or manual Auth)
**US2** – Authenticated user sees receipt list: statut label + depense count
**US3** – Submit receipt text → dispatches Job → shows "En cours de traitement" immediately (no page freeze)
**US4** – Show receipt: texte_source + statut + list of depenses (libelle, quantite, prix_unitaire, categorie)
**US5** – Delete receipt + cascaded depenses
**US6** – AI extracts articles in structured output guaranteed by `laravel/ai` SDK — one `Depense` row per article
**US7** – Status transitions: `pending` → `processed` (or `failed` with visible error state)
**US8** – Filterable expense list by `categorie`

---

## Repository Structure

```
assistant-depenses/
├── AGENTS.md
├── specs/
│   ├── 01_auth.md
│   ├── 02_receipts_crud.md
│   ├── 03_ai_extraction_queue.md
│   ├── 04_expenses_dashboard.md
│   └── 05_image_upload_bonus.md
├── app/
│   ├── Enums/
│   │   ├── RecuStatus.php
│   │   └── DepenseCategorie.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── RecuController.php
│   │   │   └── DepenseController.php
│   │   └── Requests/
│   │       └── StoreRecuRequest.php
│   ├── Jobs/
│   │   └── ExtraireDepensesDuRecu.php
│   ├── Models/
│   │   ├── Recu.php
│   │   └── Depense.php
│   └── AI/
│       └── ReceiptExtractionSchema.php
├── resources/views/
│   ├── recus/
│   │   ├── index.blade.php
│   │   ├── create.blade.php
│   │   └── show.blade.php
│   └── depenses/
│       └── index.blade.php
└── database/migrations/
    ├── xxxx_create_recus_table.php
    └── xxxx_create_depenses_table.php
```

---

## Agents

### 1. OpenCode — Lead Developer Agent

**Role:** Primary coding agent. Owns every PHP, Blade, migration, and config file.

**System Prompt (paste into OpenCode session):**
```
You are a senior Laravel architect working on "Assistant Dépenses", a Blade-only monolith (no React, no Inertia).

Stack: Laravel 12, PHP 8.3, laravel/ai SDK, Groq API, Laravel Queues (database driver), MySQL, Tailwind CSS via Vite, Pest for tests.

RULES:
1. Plan before Build. Output a markdown plan: file paths, class skeletons, migration columns, method signatures. Wait for GO before writing implementation.
2. Validation lives in FormRequest classes only. Never validate inline in controllers.
3. Every AI call is dispatched as a Job. Controllers dispatch; they never call AI directly.
4. Use PHP Enums for `statut` (RecuStatus) and `categorie` (DepenseCategorie). Cast them in $casts.
5. Every controller returning a collection must eager-load with `with()`. No N+1.
6. The JSON contract is fixed (see specs/03). Never deviate from it.
7. On failure (API unreachable, schema mismatch): set statut = failed, log raw error. Never swallow exceptions silently.
8. Queue driver is `database`. Never use `sync`.
9. Commit messages must include [AI] tag when AI wrote the code.
```

### 2. Groq / `laravel/ai` — Extraction Agent

**Role:** Stateless AI worker. Receives raw receipt text, returns strictly-shaped JSON.

**System Prompt (injected in ExtraireDepensesDuRecu Job):**
```
Tu es un assistant de comptabilité pour une épicerie marocaine.
On te donne le texte brut d'un reçu fournisseur — potentiellement en darija, en français, en arabe, avec des abréviations et des montants mal formatés.

Ta tâche : extraire tous les articles et retourner UNIQUEMENT un objet JSON valide, sans texte avant ni après, sans balises Markdown.

Le JSON doit respecter exactement ce schéma :
{
  "articles": [
    {
      "libellé": "string",
      "quantité": "integer",
      "prix_unitaire": "number",
      "catégorie": "enum: alimentaire | boissons | hygiène | entretien | autre"
    }
  ],
  "total_estimé": "number",
  "devise": "string"
}

Règles :
- Si une quantité est manquante, déduis-la à 1.
- Si un prix unitaire est manquant, laisse 0.
- La devise par défaut est MAD.
- Choisis la catégorie la plus proche. En cas de doute, utilise "autre".
- Ne génère rien d'autre que le JSON.
```

### 3. Human Architect (You)

- Review each Plan before saying GO
- Verify Debugbar shows 0 N+1 on list views
- Confirm failed receipts show visible error state (not blank page)
- Run `php artisan queue:work` during development

---

## Plan → Build Protocol

```
1. Point OpenCode at a spec: "Implement specs/02_receipts_crud.md"
2. OpenCode reads spec → outputs PLAN (files, migrations, class names, method signatures — no implementation)
3. Human reviews → GO (or requests changes)
4. OpenCode builds: full implementation file by file
5. Human runs: php artisan migrate && php artisan queue:work
6. Human tests in browser → OpenCode fixes
7. Commit with [AI] tag
```

---

## Commit Convention

```
[AI] feat(receipts): add StoreRecuRequest with min/max text validation

Plan reviewed and approved before implementation.
AI-assisted: OpenCode generated the Form Request and controller method.
Human verified: N+1 check passed, flash messages working.
```

Prefix options: `[AI]` · `[AI-assisted]` · `[Human]`
Always mention what was human-verified.
