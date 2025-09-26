<?php

namespace Tests\Feature;

use App\Models\ExpenseType;
use App\Models\Team;
use App\Models\Trip;
use App\Models\TripExpense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_with_q_and_page_and_per_page(): void
    {
        $team = Team::factory()->create();
        Trip::factory()->create(['from' => 'Chicago', 'to' => 'New York', 'team_id' => $team->id, 'start_date' => now()->toDateString()]);
        Trip::factory()->create(['from' => 'Dallas', 'to' => 'Austin', 'team_id' => $team->id, 'start_date' => now()->toDateString()]);

        $response = $this->getJson('/api/v1/trips?q=Chicago&page=1&per_page=1');

        $response->assertOk();
        $response->assertJsonFragment(['from' => 'Chicago']);
    }

    public function test_store_creates_trip(): void
    {
        $team = Team::factory()->create();

        $payload = [
            'from' => 'LA',
            'to' => 'SF',
            'team_id' => $team->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->toDateString(),
        ];

        $response = $this->postJson('/api/v1/trips', $payload);

        $response->assertCreated();
        $this->assertDatabaseHas('trips', [
            'from' => 'LA',
            'to' => 'SF',
            'team_id' => $team->id,
        ]);
    }

    public function test_show_returns_single_resource(): void
    {
        $team = Team::factory()->create();
        $trip = Trip::factory()->create(['from' => 'X', 'to' => 'Y', 'team_id' => $team->id, 'start_date' => now()->toDateString()]);

        $response = $this->getJson('/api/v1/trips/'.$trip->id);

        $response->assertOk();
        $response->assertJsonFragment(['id' => $trip->id, 'from' => 'X', 'to' => 'Y']);
    }

    public function test_update_modifies_trip(): void
    {
        $team = Team::factory()->create();
        $trip = Trip::factory()->create(['from' => 'A', 'to' => 'B', 'team_id' => $team->id, 'start_date' => now()->toDateString()]);

        $response = $this->putJson('/api/v1/trips/'.$trip->id, [
            'to' => 'C',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('trips', [
            'id' => $trip->id,
            'to' => 'C',
        ]);
    }

    public function test_destroy_deletes_trip(): void
    {
        $team = Team::factory()->create();
        $trip = Trip::factory()->create(['team_id' => $team->id, 'from' => 'a', 'to' => 'b', 'start_date' => now()->toDateString()]);

        $response = $this->deleteJson('/api/v1/trips/'.$trip->id);

        $response->assertNoContent();
        $this->assertDatabaseMissing('trips', [
            'id' => $trip->id,
        ]);
    }

    public function test_add_expense_splits_odd_cent_first_driver_gets_extra(): void
    {
        $team = Team::factory()->create(['max_members' => 2]);
        $driver1 = User::factory()->create();
        $driver2 = User::factory()->create();
        $team->addDriver($driver1);
        $team->addDriver($driver2);

        $trip = Trip::factory()->create([
            'team_id' => $team->id,
            'from' => 'A',
            'to' => 'B',
            'start_date' => now()->toDateString(),
        ]);

        $expenseType = ExpenseType::factory()->create();

        $payload = [
            'expense_id' => $expenseType->id,
            'amount' => 100.01,
        ];

        $response = $this->postJson('/api/v1/trips/'.$trip->id.'/expenses', $payload);

        $response->assertCreated();

        $this->assertDatabaseHas('trip_expenses', [
            'trip_id' => $trip->id,
            'expense_id' => $expenseType->id,
            'amount' => 100.01,
        ]);

        $expenseId = $response->json('id');

        $this->assertDatabaseHas('trip_expenses_drivers', [
            'trip_expense_id' => $expenseId,
            'user_id' => $driver1->id,
            'amount' => 50.01,
        ]);

        $this->assertDatabaseHas('trip_expenses_drivers', [
            'trip_expense_id' => $expenseId,
            'user_id' => $driver2->id,
            'amount' => 50.00,
        ]);

        $this->assertDatabaseHas('trips', [
            'id' => $trip->id,
            'total_amount' => 100.01,
        ]);
    }

    public function test_update_expense_recomputes_shares_and_trip_total(): void
    {
        $team = Team::factory()->create(['max_members' => 2]);
        $d1 = User::factory()->create();
        $d2 = User::factory()->create();
        $team->addDriver($d1);
        $team->addDriver($d2);
        $trip = Trip::factory()->create(['team_id' => $team->id, 'from' => 'X', 'to' => 'Y', 'start_date' => now()->toDateString()]);
        $expenseType = ExpenseType::factory()->create();

        $create = $this->postJson('/api/v1/trips/'.$trip->id.'/expenses', [
            'expense_id' => $expenseType->id,
            'amount' => 10.00,
        ]);
        $expenseId = $create->json('id');

        $update = $this->putJson('/api/v1/trips/'.$trip->id.'/expenses/'.$expenseId, [
            'amount' => 1.01,
        ]);
        $update->assertOk();

        $this->assertDatabaseHas('trip_expenses_drivers', [
            'trip_expense_id' => $expenseId,
            'user_id' => $d1->id,
            'amount' => 0.51,
        ]);
        $this->assertDatabaseHas('trip_expenses_drivers', [
            'trip_expense_id' => $expenseId,
            'user_id' => $d2->id,
            'amount' => 0.50,
        ]);

        $this->assertDatabaseHas('trips', [
            'id' => $trip->id,
            'total_amount' => 1.01,
        ]);
    }

    public function test_delete_expense_updates_trip_total(): void
    {
        $team = Team::factory()->create(['max_members' => 2]);
        $d1 = User::factory()->create();
        $d2 = User::factory()->create();
        $team->addDriver($d1);
        $team->addDriver($d2);
        $trip = Trip::factory()->create(['team_id' => $team->id, 'from' => 'X', 'to' => 'Y', 'start_date' => now()->toDateString()]);
        $expenseType = ExpenseType::factory()->create();

        $e1 = $this->postJson('/api/v1/trips/'.$trip->id.'/expenses', [
            'expense_id' => $expenseType->id,
            'amount' => 20.00,
        ]);
        $e2 = $this->postJson('/api/v1/trips/'.$trip->id.'/expenses', [
            'expense_id' => $expenseType->id,
            'amount' => 5.00,
        ]);

        $this->assertDatabaseHas('trips', [
            'id' => $trip->id,
            'total_amount' => 25.00,
        ]);

        $expenseId = $e2->json('id');
        $del = $this->deleteJson('/api/v1/trips/'.$trip->id.'/expenses/'.$expenseId);
        $del->assertNoContent();

        $this->assertDatabaseHas('trips', [
            'id' => $trip->id,
            'total_amount' => 20.00,
        ]);
    }
}


