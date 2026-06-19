<?php

namespace Tests\Feature;

use App\Ai\Agents\ReceiptExtractionAgent;
use App\Enums\RecuStatus;
use App\Jobs\ExtraireDepensesDuRecu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Ai;
use Tests\TestCase;

class ExtractionTest extends TestCase
{
    use RefreshDatabase;

    public function test_extracts_depenses_from_receipt_and_marks_as_processed(): void
    {
        Ai::fakeAgent(ReceiptExtractionAgent::class, [[
            'articles' => [
                [
                    'libellé' => 'Huile végétale 1L',
                    'quantité' => 3,
                    'prix_unitaire' => 18.50,
                    'catégorie' => 'alimentaire',
                ],
                [
                    'libellé' => 'Eau Sidi Ali 1.5L',
                    'quantité' => 6,
                    'prix_unitaire' => 4.00,
                    'catégorie' => 'boissons',
                ],
            ],
            'total_estimé' => 79.50,
            'devise' => 'MAD',
        ]]);

        $user = User::factory()->create();
        $recu = $user->recus()->create([
            'texte_source' => 'Huile 3x18.5 Sidi Ali 6x4',
            'statut' => RecuStatus::Pending,
        ]);

        ExtraireDepensesDuRecu::dispatchSync($recu);

        $recu->refresh();

        $this->assertEquals(RecuStatus::Processed, $recu->statut);
        $this->assertCount(2, $recu->depenses);
        $this->assertEquals('Huile végétale 1L', $recu->depenses->first()->libelle);
        $this->assertEquals('alimentaire', $recu->depenses->first()->categorie->value);
    }

    public function test_marks_receipt_as_failed_when_ai_call_throws(): void
    {
        $user = User::factory()->create();
        $recu = $user->recus()->create([
            'texte_source' => 'Test receipt',
            'statut' => RecuStatus::Pending,
        ]);

        $job = new ExtraireDepensesDuRecu($recu);
        $job->failed(new \RuntimeException('Groq API unreachable'));

        $recu->refresh();

        $this->assertEquals(RecuStatus::Failed, $recu->statut);
    }
}
