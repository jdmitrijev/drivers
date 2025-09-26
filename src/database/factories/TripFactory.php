<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\Trip;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Trip>
 */
class TripFactory extends Factory
{
    protected $model = Trip::class;

    public function definition(): array
    {
        $start = now()->subDays(rand(0, 10))->toDateString();
        return [
            'from' => $this->faker->city(),
            'to' => $this->faker->city(),
            'team_id' => Team::factory(),
            'start_date' => $start,
            'end_date' => $start,
            'total_amount' => 0,
        ];
    }
}


