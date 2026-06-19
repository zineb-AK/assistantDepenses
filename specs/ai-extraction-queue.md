# OpenSpec — Feature 02: AI Extraction via Queue

**Version:** 1.0.0
**Status:** Approved
**Covers:** US6, US7
**Author:** Human Architect + OpenCode (AI)

---

## 1. Proposal

### Problem

Calling the Groq API takes 2–8 seconds. If the controller waits for the response, Si Brahim sees a frozen browser tab every time he submits a receipt. This is unacceptable. Separately, raw JSON decoding (`json_decode`) is fragile — a slightly malformed response silently returns `null` and corrupts the database.

### Solution

Two architectural decisions solve these two distinct problems:

1. **Queue + Job** — The controller dispatches `ExtraireDepensesDuRecu` and returns immediately. The actual Groq call happens in a background worker process. The user sees "En attente" and the status updates via polling (Feature 01).

2. **`laravel/ai` structured output** — The SDK enforces the JSON contract at the SDK level via a schema class. If Groq returns something that doesn't match the schema, the SDK throws before we touch the database. No `json_decode` gambling.

### What this feature is NOT

- Not responsible for the HTTP form submission (that is Feature 01).
- Not responsible for displaying expenses in a filterable dashboard (that is Feature 03).

---

## 2. Specification

### 2.1 Queue Configuration

**Driver:** `database` (no Redis required, keeps infra minimal)

```php
// .env
QUEUE_CONNECTION=database

// Generate the jobs table
php artisan queue:table
php artisan migrate
```

**Running the worker (development):**
```bash
php artisan queue:work --tries=3 --backoff=10
```

**Production:** Supervisor process pointing to `php artisan queue:work --sleep=3 --tries=3 --max-time=3600`

**Why 3 tries?** The Groq API can timeout transiently. 3 attempts with 10s backoff gives a real chance of success on flaky connections without burning API credits indefinitely.

### 2.2 Data Model — `depenses` Table

| Column | Type | Notes |
|---|---|---|
| `id` | `bigIncrements` | PK |
| `recu_id` | `foreignId` | `constrained('recus')->cascadeOnDelete()` |
| `libelle` | `string` | Mapped from `libellé` in JSON |
| `quantite` | `unsignedInteger` | Mapped from `quantité` |
| `prix_unitaire` | `decimal(10,2)` | Mapped from `prix_unitaire` |
| `categorie` | `string` | Backed by `DepenseCategorie` enum |
| `created_at` / `updated_at` | `timestamps` | Standard |

**Eloquent Model: `App\Models\Depense`**

```php
protected $casts = [
    'categorie'     => DepenseCategorie::class,
    'prix_unitaire' => 'decimal:2',
];

protected $fillable = [
    'recu_id', 'libelle', 'quantite', 'prix_unitaire', 'categorie',
];

// Computed accessor
public function totalLigne(): float
{
    return $this->quantite * $this->prix_unitaire;
}

// Relationship
public function recu(): BelongsTo
{
    return $this->belongsTo(Recu::class);
}
```

**Enum: `App\Enums\DepenseCategorie`**

```php
enum DepenseCategorie: string
{
    case Alimentaire = 'alimentaire';
    case Boissons    = 'boissons';
    case Hygiene     = 'hygiène';
    case Entretien   = 'entretien';
    case Autre       = 'autre';

    public function label(): string
    {
        return match($this) {
            self::Alimentaire => 'Alimentaire',
            self::Boissons    => 'Boissons',
            self::Hygiene     => 'Hygiène',
            self::Entretien   => 'Entretien',
            self::Autre       => 'Autre',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::Alimentaire => 'bg-amber-100 text-amber-800',
            self::Boissons    => 'bg-blue-100 text-blue-800',
            self::Hygiene     => 'bg-pink-100 text-pink-800',
            self::Entretien   => 'bg-purple-100 text-purple-800',
            self::Autre       => 'bg-gray-100 text-gray-700',
        };
    }
}
```

### 2.3 The JSON Contract

This is the fixed schema. It does not change. Every component of the system that touches extraction data must reference this definition.

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

**Why store `total_estimé` and `devise`?** They are stored in `payload_ia` (on the `Recu` model, cast as `array`) for auditability. They are not stored as separate columns because the source of truth for totals is the sum of `depenses.prix_unitaire * depenses.quantite` — the AI estimate is advisory.

### 2.4 Schema Class — `App\AI\ReceiptExtractionSchema`

The `laravel/ai` SDK accepts a schema definition that it uses to constrain the model's output (structured output mode). This class is the PHP representation of the JSON contract above.

```php
namespace App\AI;

use Laravel\AI\Schema\Schema;
use Laravel\AI\Schema\Property;

class ReceiptExtractionSchema
{
    public static function make(): Schema
    {
        return Schema::object('receipt_extraction', [
            Property::array('articles',
                Schema::object('article', [
                    Property::string('libellé')->description('Nom du produit'),
                    Property::integer('quantité')->description('Quantité achetée'),
                    Property::number('prix_unitaire')->description('Prix par unité en MAD'),
                    Property::enum('catégorie', [
                        'alimentaire',
                        'boissons',
                        'hygiène',
                        'entretien',
                        'autre',
                    ])->description('Catégorie du produit'),
                ])
            ),
            Property::number('total_estimé')->description('Total estimé du reçu'),
            Property::string('devise')->description('Devise, ex: MAD'),
        ]);
    }
}
```

> **Why a dedicated class?** The schema is a contract, not configuration. Keeping it in its own class means it can be imported by the Job, by the test fake, and by any future endpoint without duplication. If the contract changes, there is exactly one place to update.

### 2.5 Job — `App\Jobs\ExtraireDepensesDuRecu`

```bash
php artisan make:job ExtraireDepensesDuRecu
```

**Full logic flow:**

```php
namespace App\Jobs;

use App\AI\ReceiptExtractionSchema;
use App\Enums\DepenseCategorie;
use App\Enums\RecuStatus;
use App\Models\Depense;
use App\Models\Recu;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Laravel\AI\Facades\AI;

class ExtraireDepensesDuRecu implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(public Recu $recu) {}

    public function handle(): void
    {
        try {
            $response = AI::structured(
                schema: ReceiptExtractionSchema::make(),
                prompt: $this->buildPrompt(),
                model: config('ai.groq.model', 'llama-3.3-70b-versatile'),
            );

            // Store raw payload for audit
            $this->recu->update(['payload_ia' => $response]);

            // Persist each article as a Depense
            foreach ($response['articles'] as $article) {
                Depense::create([
                    'recu_id'       => $this->recu->id,
                    'libelle'       => $article['libellé'],
                    'quantite'      => (int) $article['quantité'],
                    'prix_unitaire' => (float) $article['prix_unitaire'],
                    'categorie'     => DepenseCategorie::from($article['catégorie']),
                ]);
            }

            $this->recu->update(['statut' => RecuStatus::Processed]);

        } catch (\Throwable $e) {
            Log::error('ExtraireDepensesDuRecu failed', [
                'recu_id' => $this->recu->id,
                'error'   => $e->getMessage(),
            ]);

            // Mark as failed only on final attempt
            if ($this->attempts() >= $this->tries) {
                $this->recu->update(['statut' => RecuStatus::Failed]);
            }
        }
    }

    private function buildPrompt(): string
    {
        return <<<PROMPT
Voici le texte d'un reçu fournisseur marocain :

---
{$this->recu->texte_brut}
---

Extrais tous les articles et retourne le JSON structuré demandé.
PROMPT;
    }

    public function failed(\Throwable $exception): void
    {
        // Called by Laravel after all retries exhausted
        $this->recu->update(['statut' => RecuStatus::Failed]);
        Log::critical('Job failed after all retries', [
            'recu_id' => $this->recu->id,
            'error'   => $exception->getMessage(),
        ]);
    }
}
```

### 2.6 `laravel/ai` SDK Configuration

```php
// config/ai.php
return [
    'default' => 'groq',
    'providers' => [
        'groq' => [
            'driver'  => 'openai_compatible',
            'url'     => 'https://api.groq.com/openai/v1',
            'key'     => env('GROQ_API_KEY'),
            'model'   => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
        ],
    ],
];
```

```bash
# .env additions
GROQ_API_KEY=gsk_...
GROQ_MODEL=llama-3.3-70b-versatile
```

### 2.7 Status Lifecycle

```
[Controller: store()]
        │
        ▼
  Recu created (statut = pending)
        │
        ▼
  Job dispatched to queue
        │
        ▼
  HTTP redirect → user sees "En attente"
        │
        ▼ (background worker)
  Job::handle() runs
        ├── SUCCESS → statut = processed, Depenses created
        └── FAILURE (after 3 tries) → statut = failed, log error
```

**User-facing states:**

| `statut` value | Displayed label | UI treatment |
|---|---|---|
| `pending` | En attente | Yellow badge, polling active |
| `processed` | Traité | Green badge, depenses count shown |
| `failed` | Échoué | Red badge, retry hint shown |

### 2.8 Bonus — Pest Test with SDK Fake

```php
// tests/Feature/ExtractionTest.php

use App\Enums\RecuStatus;
use App\Jobs\ExtraireDepensesDuRecu;
use App\Models\Recu;
use App\Models\User;
use Laravel\AI\Facades\AI;

it('extracts depenses from a receipt and marks it as processed', function () {
    // Arrange
    AI::fake([
        'articles' => [
            [
                'libellé'       => 'Huile végétale 1L',
                'quantité'      => 3,
                'prix_unitaire' => 18.50,
                'catégorie'     => 'alimentaire',
            ],
            [
                'libellé'       => 'Eau Sidi Ali 1.5L',
                'quantité'      => 6,
                'prix_unitaire' => 4.00,
                'catégorie'     => 'boissons',
            ],
        ],
        'total_estimé' => 79.50,
        'devise'       => 'MAD',
    ]);

    $user = User::factory()->create();
    $recu = Recu::factory()->for($user)->create([
        'texte_brut' => 'Huile 3x18.5 Sidi Ali 6x4',
        'statut'     => 'pending',
    ]);

    // Act
    ExtraireDepensesDuRecu::dispatchSync($recu);

    // Assert
    $recu->refresh();
    expect($recu->statut)->toBe(RecuStatus::Processed);
    expect($recu->depenses)->toHaveCount(2);
    expect($recu->depenses->first()->libelle)->toBe('Huile végétale 1L');
    expect($recu->depenses->first()->categorie->value)->toBe('alimentaire');
});

it('marks the receipt as failed when the AI call throws', function () {
    AI::fake()->throws(new \RuntimeException('Groq API unreachable'));

    $user = User::factory()->create();
    $recu = Recu::factory()->for($user)->create(['statut' => 'pending']);

    // Simulate all retries exhausted
    $job = new ExtraireDepensesDuRecu($recu);
    $job->failed(new \RuntimeException('Groq API unreachable'));

    $recu->refresh();
    expect($recu->statut)->toBe(RecuStatus::Failed);
});
```

> **Why `dispatchSync`?** Tests run synchronously. `dispatchSync` bypasses the queue driver and runs the job inline — no worker needed, no async complexity. The `AI::fake()` replaces the real Groq call with a deterministic response. The test is fast, reproducible, and requires no network.

---

## 3. Tasks

| # | Task | Owner | Done? |
|---|---|---|---|---|
| T02-1 | Run `php artisan queue:table && php artisan migrate` | OpenCode | ✅ |
| T02-2 | Create `DepenseCategorie` enum in `app/Enums/` | OpenCode | ✅ |
| T02-3 | Write migration `create_depenses_table` with all columns | OpenCode | ✅ |
| T02-4 | Write `Depense` Eloquent model with casts, fillable, relationship | OpenCode | ✅ |
| T02-5 | Create `App\AI\ReceiptExtractionSchema` class (or equivalent Agent pattern) | OpenCode | ✅ |
| T02-6 | Install and configure `laravel/ai` package with Groq provider | OpenCode | ✅ |
| T02-7 | Write `ExtraireDepensesDuRecu` Job with full handle/failed logic | OpenCode | ✅ |
| T02-8 | Add `ExtraireDepensesDuRecu::dispatch($recu)` in `RecuController::store()` | OpenCode | ✅ |
| T02-9 | Write Pest tests (happy path + failure path) | OpenCode | ✅ |
| T02-10 | **Human verification:** Run `php artisan queue:work`, submit a real receipt with Groq key, confirm `statut` transitions to `processed` | Human | ☐ |
| T02-11 | **Human verification:** Disconnect internet, submit receipt, confirm `statut` transitions to `failed` after 3 retries | Human | ☐ |
| T02-12 | **Human verification:** Run `php artisan test` — all tests pass with no real API call | Human | ✅ |
| T02-13 | Commit: `[AI] feat(extraction): async job + laravel/ai structured output + Pest tests` | Human | ✅ |
