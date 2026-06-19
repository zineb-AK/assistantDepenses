<?php

namespace App\Jobs;

use App\Enums\DepenseCategorie;
use App\Enums\RecuStatus;
use App\Models\Depense;
use App\Models\Recu;
use App\Services\ReceiptExtractionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ExtraireDepensesDuRecu implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(public Recu $recu) {}

    public function handle(ReceiptExtractionService $extractionService): void
    {
        try {
            $data = $extractionService->extract($this->recu->texte_source);

            $this->recu->update(['payload_ia' => $data]);

            foreach ($data['articles'] as $article) {
                Depense::create([
                    'recu_id' => $this->recu->id,
                    'libelle' => $article['libellé'],
                    'quantite' => (int) $article['quantité'],
                    'prix_unitaire' => (float) $article['prix_unitaire'],
                    'categorie' => DepenseCategorie::from($article['catégorie']),
                ]);
            }

            $this->recu->update(['statut' => RecuStatus::Processed]);
        } catch (\Throwable $e) {
            Log::error('ExtraireDepensesDuRecu failed', [
                'recu_id' => $this->recu->id,
                'error' => $e->getMessage(),
            ]);

            if ($this->attempts() >= $this->tries) {
                $this->recu->update(['statut' => RecuStatus::Failed]);
            }

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        $this->recu->update(['statut' => RecuStatus::Failed]);

        Log::critical('Job failed after all retries', [
            'recu_id' => $this->recu->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
