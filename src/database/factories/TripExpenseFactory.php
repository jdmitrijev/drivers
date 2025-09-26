<?php

namespace Database\Factories;

use App\Models\ExpenseType;
use App\Models\Trip;
use App\Models\TripExpense;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TripExpense>
 */
class TripExpenseFactory extends Factory
{
    protected $model = TripExpense::class;

    public function definition(): array
    {
        return [
            'trip_id' => Trip::factory(),
            'expense_id' => ExpenseType::factory(),
            'amount' => $this->faker->randomFloat(2, 1, 500),
        ];
    }
}


