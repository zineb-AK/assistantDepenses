<?php

namespace Tests\Feature;

use App\Enums\DepenseCategorie;
use App\Enums\RecuStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepensesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('depenses.index'))->assertRedirect(route('login'));
    }

    public function test_shows_all_user_depenses_across_receipts(): void
    {
        $user = User::factory()->create();
        $recu = $user->recus()->create(['texte_source' => 'Receipt 1', 'statut' => RecuStatus::Processed]);
        $recu->depenses()->create(['libelle' => 'Farine', 'quantite' => 10, 'prix_unitaire' => 5.00, 'categorie' => 'alimentaire']);
        $recu->depenses()->create(['libelle' => 'Huile', 'quantite' => 5, 'prix_unitaire' => 12.00, 'categorie' => 'alimentaire']);

        $recu2 = $user->recus()->create(['texte_source' => 'Receipt 2', 'statut' => RecuStatus::Processed]);
        $recu2->depenses()->create(['libelle' => 'Savon', 'quantite' => 3, 'prix_unitaire' => 8.50, 'categorie' => 'hygiene']);

        $response = $this->actingAs($user)->get(route('depenses.index'));

        $response->assertStatus(200);
        $response->assertSee('Farine');
        $response->assertSee('Huile');
        $response->assertSee('Savon');
    }

    public function test_category_filter_filters_correctly(): void
    {
        $user = User::factory()->create();
        $recu = $user->recus()->create(['texte_source' => 'Receipt', 'statut' => RecuStatus::Processed]);
        $recu->depenses()->create(['libelle' => 'Farine', 'quantite' => 10, 'prix_unitaire' => 5.00, 'categorie' => 'alimentaire']);
        $recu->depenses()->create(['libelle' => 'Savon', 'quantite' => 3, 'prix_unitaire' => 8.50, 'categorie' => 'hygiene']);

        $response = $this->actingAs($user)->get(route('depenses.index', ['categorie' => 'alimentaire']));

        $response->assertSee('Farine');
        $response->assertDontSee('Savon');
    }

    public function test_invalid_category_filter_shows_all(): void
    {
        $user = User::factory()->create();
        $recu = $user->recus()->create(['texte_source' => 'Receipt', 'statut' => RecuStatus::Processed]);
        $recu->depenses()->create(['libelle' => 'Farine', 'quantite' => 10, 'prix_unitaire' => 5.00, 'categorie' => 'alimentaire']);
        $recu->depenses()->create(['libelle' => 'Savon', 'quantite' => 3, 'prix_unitaire' => 8.50, 'categorie' => 'hygiene']);

        $response = $this->actingAs($user)->get(route('depenses.index', ['categorie' => 'invalid']));

        $response->assertSee('Farine');
        $response->assertSee('Savon');
    }

    public function test_data_isolation(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $recu = $user->recus()->create(['texte_source' => 'Mine', 'statut' => RecuStatus::Processed]);
        $recu->depenses()->create(['libelle' => 'Farine', 'quantite' => 10, 'prix_unitaire' => 5.00, 'categorie' => 'alimentaire']);

        $recu2 = $other->recus()->create(['texte_source' => 'Theirs', 'statut' => RecuStatus::Processed]);
        $recu2->depenses()->create(['libelle' => 'Huile', 'quantite' => 5, 'prix_unitaire' => 12.00, 'categorie' => 'alimentaire']);

        $response = $this->actingAs($user)->get(route('depenses.index'));

        $response->assertSee('Farine');
        $response->assertDontSee('Huile');
    }

    public function test_category_totals_displayed(): void
    {
        $user = User::factory()->create();
        $recu = $user->recus()->create(['texte_source' => 'Receipt', 'statut' => RecuStatus::Processed]);
        $recu->depenses()->create(['libelle' => 'Farine', 'quantite' => 10, 'prix_unitaire' => 5.00, 'categorie' => 'alimentaire']);
        $recu->depenses()->create(['libelle' => 'Riz', 'quantite' => 2, 'prix_unitaire' => 15.00, 'categorie' => 'alimentaire']);
        $recu->depenses()->create(['libelle' => 'Savon', 'quantite' => 3, 'prix_unitaire' => 8.50, 'categorie' => 'hygiene']);

        $response = $this->actingAs($user)->get(route('depenses.index'));

        $response->assertSee('80,00 MAD');
        $response->assertSee('25,50 MAD');
    }
}
