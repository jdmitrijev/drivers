<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_with_q_and_page_and_per_page(): void
    {
        Team::factory()->create(['name' => 'Alpha Team']);
        Team::factory()->create(['name' => 'Beta Squad']);

        $response = $this->getJson('/api/v1/teams?q=Alpha&page=1&per_page=1');

        $response->assertOk();
        $response->assertJsonFragment(['name' => 'Alpha Team']);
    }

    public function test_store_creates_team(): void
    {
        $payload = ['name' => 'New Team', 'max_members' => 3];

        $response = $this->postJson('/api/v1/teams', $payload);

        $response->assertCreated();
        $this->assertDatabaseHas('teams', $payload);
    }

    public function test_show_returns_single_resource(): void
    {
        $team = Team::factory()->create(['name' => 'Show Me']);

        $response = $this->getJson('/api/v1/teams/'.$team->id);

        $response->assertOk();
        $response->assertJsonFragment(['id' => $team->id, 'name' => 'Show Me']);
    }

    public function test_update_modifies_resource(): void
    {
        $team = Team::factory()->create(['name' => 'Old Name']);

        $response = $this->putJson('/api/v1/teams/'.$team->id, [
            'name' => 'Updated Name',
            'max_members' => 4,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'name' => 'Updated Name',
            'max_members' => 4,
        ]);
    }

    public function test_destroy_deletes_resource(): void
    {
        $team = Team::factory()->create();

        $response = $this->deleteJson('/api/v1/teams/'.$team->id);

        $response->assertNoContent();
        $this->assertDatabaseMissing('teams', [
            'id' => $team->id,
        ]);
    }

    public function test_add_and_remove_driver(): void
    {
        $team = Team::factory()->create(['max_members' => 2]);
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $add1 = $this->postJson('/api/v1/teams/'.$team->id.'/drivers', [
            'user_id' => $user1->id,
        ]);
        $add1->assertNoContent();

        $add2 = $this->postJson('/api/v1/teams/'.$team->id.'/drivers', [
            'user_id' => $user2->id,
        ]);
        $add2->assertNoContent();

        $this->assertDatabaseHas('teams_drivers', [
            'team_id' => $team->id,
            'user_id' => $user1->id,
        ]);

        $this->assertDatabaseHas('teams_drivers', [
            'team_id' => $team->id,
            'user_id' => $user2->id,
        ]);

        $remove = $this->deleteJson('/api/v1/teams/'.$team->id.'/drivers', [
            'user_id' => $user1->id,
        ]);
        $remove->assertNoContent();

        $this->assertDatabaseMissing('teams_drivers', [
            'team_id' => $team->id,
            'user_id' => $user1->id,
        ]);
    }
}


