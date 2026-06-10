# AGENTS.md — Assistant Dépenses

> **Discipline: Plan before Build.**
> No agent writes a single line of implementation code before a written plan has been produced, reviewed, and explicitly approved. This is non-negotiable. Skipping the plan phase is the fastest way to build the wrong thing correctly.

---

## Project Context

**Assistant Dépenses** is a Laravel monolith that helps Si Brahim — a local grocer — transform chaotic supplier receipts into clean, categorised expense records. The core value is: paste raw text → AI extracts structured line items → data is stored, browsable, and filterable. The UX is Blade + Tailwind; the intelligence is Groq (via `laravel/ai`); the glue that keeps the UI responsive while Groq thinks is Laravel Queues.

---

## Agents

### 1. OpenCode — Lead Developer Agent

**Role:** Primary coding agent. Owns every PHP, Blade, migration, and config file. Executes the Plan → Build loop for each feature.

**Responsibilities:**
- Read the relevant `specs/` file before touching any code.
- Produce a written **Plan** (file list, class names, method signatures, migration columns, relationships) and wait for human approval before entering Build mode.
- Follow Laravel conventions strictly: Form Requests for validation, Eloquent Casts for typed columns, Policies for authorisation, Jobs for async work.
- Write commit messages that honestly disclose AI involvement (see Commit Convention below).
- Never call the Groq API directly — always go through the `laravel/ai` SDK abstraction.
- Check for N+1 queries using Debugbar before marking any feature done.

**System Prompt (paste into OpenCode session):**
```
You are a senior Laravel architect working on "Assistant Dépenses", a Blade-only monolith (no React, no Inertia).
Your stack: Laravel 11, laravel/ai SDK, Groq API, Laravel Queues (database driver), Tailwind CSS via Vite, Pest for tests.

RULES:
1. Plan before Build. Output a markdown plan with file paths, class skeletons, and migration columns. Wait for GO before writing implementation.
2. Validation lives in Form Request classes only. Never validate in controllers.
3. Async work lives in Jobs only. Controllers dispatch; they never call the AI directly.
4. Use PHP Enums for `status` (pending/processed/failed) and `categorie` (alimentaire/boissons/hygiène/entretien/autre). Cast them via $casts in models.
5. Every controller that returns a collection must eager-load relationships. No exceptions.
6. When extracting from a receipt, the JSON contract is fixed (see specs/02). Never deviate from it.
7. On failure (API unreachable, schema mismatch), set status = failed and log the raw error. Never silently swallow exceptions.
8. Commit messages must include [AI] tag when AI wrote the code.
```

---

### 2. Groq / `laravel/ai` — Extraction Agent

**Role:** Stateless AI worker. Receives raw receipt text, returns a strictly-shaped JSON object representing the extracted articles.

**Responsibilities:**
- Consume the prompt constructed by `ExtraireDepensesDuRecu` Job.
- Return **only** the JSON contract defined in `specs/02_ai_extraction_queue.md`. No prose, no markdown fences, no apologies.
- Respect the `structured output` schema enforced by the `laravel/ai` SDK — the SDK validates the response shape so a bad Groq response never reaches the database.

**System Prompt (injected in Job):**
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

---

### 3. Human Architect (You)

**Role:** Decision authority. Approves Plans before Build starts. Reviews specs, merges OpenSpec files, runs `php artisan queue:work` during development, validates UI in browser.

**Responsibilities:**
- Review each Plan produced by OpenCode before saying GO.
- Verify that Debugbar shows 0 N+1 queries on list views.
- Validate that failed receipts display a clear error state (not a blank page).
- Sign off on commits before pushing to the public repo.

---

## Plan → Build Protocol

```
1. Human points OpenCode at a spec file: "Implement specs/01_receipts_crud.md"
2. OpenCode reads the spec and outputs a PLAN:
   - List of files to create/modify
   - Migration columns with types
   - Class names, method signatures (no implementation)
   - Blade view structure (no HTML yet)
3. Human reviews → says GO (or requests changes)
4. OpenCode enters Build mode: writes full implementation file by file
5. Human runs: php artisan migrate && php artisan queue:work
6. Human tests in browser; OpenCode fixes issues
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

Prefix options: `[AI]`, `[AI-assisted]`, `[Human]`
Always mention what was human-verified, not just what AI wrote.

---

## Repository Structure (target)

```
assistant-depenses/
├── AGENTS.md                  ← This file (first commit)
├── specs/
│   ├── 01_receipts_crud.md
│   ├── 02_ai_extraction_queue.md
│   └── 03_expenses_dashboard.md
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
    ├── create_recus_table.php
    └── create_depenses_table.php
```

---

## Non-Negotiables (Architecture Guardrails)

| Concern | Rule |
|---|---|
| AI calls | Always via `laravel/ai` SDK — never raw `Http::post()` to Groq |
| Validation | Always in `StoreRecuRequest` — never `$request->validate()` inline |
| Async | Every AI call dispatched as a Job — controller never waits |
| Type safety | Enums cast in `$casts` — never raw strings for status/category |
| N+1 | `with('depenses')` on every collection query — Debugbar confirms |
| Failure | Failed job → `status = failed` on the `Recu` — never silent |
| Schema | JSON contract is fixed — SDK enforces it — no `json_decode` gambling |
