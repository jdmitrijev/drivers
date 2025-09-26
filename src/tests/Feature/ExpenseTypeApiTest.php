<?php

namespace Tests\Feature;

use App\Models\ExpenseType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseTypeApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_with_q_and_page_and_per_page(): void
    {
        ExpenseType::factory()->create(['name' => 'Fuel (EFS)']);
        ExpenseType::factory()->create(['name' => 'Insurance (Truck)']);

        $response = $this->getJson('/api/v1/expense-types?q=Fuel&page=1&per_page=1');

        $response->assertOk();
        $response->assertJsonFragment(['name' => 'Fuel (EFS)']);
    }

    public function test_store_creates_expense_type(): void
    {
        $payload = ['name' => 'New Expense Type'];

        $response = $this->postJson('/api/v1/expense-types', $payload);

        $response->assertCreated();
        $this->assertDatabaseHas('expense_types', $payload);
    }

    public function test_show_returns_single_resource(): void
    {
        $expenseType = ExpenseType::factory()->create(['name' => 'Show Me']);

        $response = $this->getJson('/api/v1/expense-types/'.$expenseType->id);

        $response->assertOk();
        $response->assertJsonFragment(['id' => $expenseType->id, 'name' => 'Show Me']);
    }

    public function test_update_modifies_resource(): void
    {
        $expenseType = ExpenseType::factory()->create(['name' => 'Old Name']);

        $response = $this->putJson('/api/v1/expense-types/'.$expenseType->id, [
            'name' => 'Updated Name',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('expense_types', [
            'id' => $expenseType->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_destroy_deletes_resource(): void
    {
        $expenseType = ExpenseType::factory()->create();

        $response = $this->deleteJson('/api/v1/expense-types/'.$expenseType->id);

        $response->assertNoContent();
        $this->assertDatabaseMissing('expense_types', [
            'id' => $expenseType->id,
        ]);
    }
}


