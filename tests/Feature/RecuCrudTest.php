<?php

namespace Tests\Feature;

use App\Enums\RecuStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class RecuCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_on_receipt_list(): void
    {
        $this->get(route('recus.index'))->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_to_login_on_receipt_create(): void
    {
        $this->get(route('recus.create'))->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_to_login_on_receipt_store(): void
    {
        $this->post(route('recus.store'), ['texte_source' => 'Test receipt'])->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_to_login_on_receipt_show(): void
    {
        $this->get(route('recus.show', 1))->assertRedirect(route('login'));
    }

    public function test_guest_is_redirected_to_login_on_receipt_destroy(): void
    {
        $this->delete(route('recus.destroy', 1))->assertRedirect(route('login'));
    }

    public function test_user_can_create_a_receipt(): void
    {
        Queue::fake();

        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('recus.store'), [
            'texte_source' => 'Reçu fournisseur: 10kg farine, 5L huile',
        ]);

        $response->assertRedirect(route('recus.index'));
        $response->assertSessionHas('success', 'Reçu créé avec succès');

        $this->assertDatabaseHas('recus', [
            'user_id' => $user->id,
            'texte_source' => 'Reçu fournisseur: 10kg farine, 5L huile',
            'statut' => RecuStatus::Pending->value,
        ]);

        Queue::assertPushed(\App\Jobs\ExtraireDepensesDuRecu::class);
    }

    public function test_receipt_creation_fails_with_short_text(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from(route('recus.create'))->post(route('recus.store'), [
            'texte_source' => 'Court',
        ]);

        $response->assertRedirect(route('recus.create'));
        $response->assertSessionHasErrors('texte_source');
        $this->assertDatabaseCount('recus', 0);
    }

    public function test_receipt_creation_fails_with_long_text(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('recus.store'), [
            'texte_source' => str_repeat('a', 10001),
        ]);

        $response->assertSessionHasErrors('texte_source');
        $this->assertDatabaseCount('recus', 0);
    }

    public function test_user_can_view_own_receipt(): void
    {
        $user = User::factory()->create();
        $recu = $user->recus()->create([
            'texte_source' => 'Test receipt',
            'statut' => RecuStatus::Pending,
        ]);

        $response = $this->actingAs($user)->get(route('recus.show', $recu));

        $response->assertStatus(200);
        $response->assertSee($recu->texte_source);
    }

    public function test_user_cannot_view_another_users_receipt(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $recu = $owner->recus()->create([
            'texte_source' => 'Test receipt',
            'statut' => RecuStatus::Pending,
        ]);

        $this->actingAs($other)->get(route('recus.show', $recu))->assertStatus(404);
    }

    public function test_user_can_delete_own_receipt(): void
    {
        $user = User::factory()->create();
        $recu = $user->recus()->create([
            'texte_source' => 'Test receipt',
            'statut' => RecuStatus::Pending,
        ]);

        $response = $this->actingAs($user)->delete(route('recus.destroy', $recu));

        $response->assertRedirect(route('recus.index'));
        $response->assertSessionHas('success', 'Reçu supprimé avec succès');
        $this->assertDatabaseMissing('recus', ['id' => $recu->id]);
    }

    public function test_user_cannot_delete_another_users_receipt(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $recu = $owner->recus()->create([
            'texte_source' => 'Test receipt',
            'statut' => RecuStatus::Pending,
        ]);

        $this->actingAs($other)->delete(route('recus.destroy', $recu))->assertStatus(404);
        $this->assertDatabaseHas('recus', ['id' => $recu->id]);
    }

    public function test_deleting_receipt_cascade_deletes_depenses(): void
    {
        $user = User::factory()->create();
        $recu = $user->recus()->create([
            'texte_source' => 'Test receipt',
            'statut' => RecuStatus::Pending,
        ]);
        $depense = $recu->depenses()->create([
            'libelle' => 'Farine',
            'quantite' => 10,
            'prix_unitaire' => 5.00,
            'categorie' => 'alimentaire',
        ]);

        $this->actingAs($user)->delete(route('recus.destroy', $recu));

        $this->assertDatabaseMissing('recus', ['id' => $recu->id]);
        $this->assertDatabaseMissing('depenses', ['id' => $depense->id]);
    }

    public function test_user_sees_only_own_receipts_on_index(): void
    {
        $user = User::factory()->create();
        $user->recus()->create([
            'texte_source' => 'My receipt',
            'statut' => RecuStatus::Pending,
        ]);

        $otherUser = User::factory()->create();
        $otherUser->recus()->create([
            'texte_source' => 'Other user receipt',
            'statut' => RecuStatus::Pending,
        ]);

        $response = $this->actingAs($user)->get(route('recus.index'));

        $response->assertSee('En attente');
        $response->assertViewHas('recus', fn ($recus) => $recus->count() === 1);
    }
}
