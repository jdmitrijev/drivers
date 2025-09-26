<?php

namespace Database\Seeders;

use App\Models\ExpenseType;
use App\Models\Team;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Log::info('DataSeeder: start');
        if ($this->command) {
            $this->command->info('DataSeeder: start');
        }
        // Ensure expense types exist (should already be seeded by ExpenseTypeSeeder)
        $expenseTypeIds = ExpenseType::pluck('id')->all();
        if (empty($expenseTypeIds)) {
            $this->call([
                ExpenseTypeSeeder::class,
            ]);
            $expenseTypeIds = ExpenseType::pluck('id')->all();
        }

        // Create users
        User::factory(50)->create();
        Log::info('DataSeeder: created users', ['count' => 50]);
        if ($this->command) {
            $this->command->info('DataSeeder: created 50 users');
        }

        // Create teams
        $teams = Team::factory(25)->create();
        Log::info('DataSeeder: created teams', ['count' => 25]);
        if ($this->command) {
            $this->command->info('DataSeeder: created 25 teams');
        }

		// Assign two drivers to each team via API
		$userIds = User::pluck('id')->all();
		$teams->each(function (Team $team) use ($userIds) {
			if (count($userIds) < 2) {
				Log::warning('DataSeeder: not enough users to assign to team', ['team_id' => $team->id]);
				return;
			}
			$keys = array_rand($userIds, 2);
			$selectedUserIds = is_array($keys) ? [$userIds[$keys[0]], $userIds[$keys[1]]] : [$userIds[$keys], $userIds[$keys]];
			$assigned = 0;
			$failed = 0;
			foreach ($selectedUserIds as $uid) {
				$request = Request::create(
					"/api/v1/teams/{$team->id}/drivers",
					'POST',
					['user_id' => $uid]
				);
				$response = app()->handle($request);
				$status = $response->getStatusCode();
				if (in_array($status, [200, 201, 204], true)) {
					$assigned++;
				} else {
					$failed++;
					Log::warning('DataSeeder: failed to assign driver to team', [
						'team_id' => $team->id,
						'user_id' => $uid,
						'status' => $status,
						'body' => method_exists($response, 'getContent') ? $response->getContent() : null,
					]);
				}
			}
			Log::info('DataSeeder: team drivers assignment result', [
				'team_id' => $team->id,
				'assigned' => $assigned,
				'failed' => $failed,
			]);
			if ($this->command) {
				$this->command->info("DataSeeder: team {$team->id} drivers -> assigned {$assigned}, failed {$failed}");
			}
		});

		// Create 1-3 trips per team
		$teams->each(function (Team $team) use ($expenseTypeIds) {
            $tripCount = random_int(1, 3);
            Log::info('DataSeeder: creating trips for team', ['team_id' => $team->id, 'trip_count' => $tripCount]);
            if ($this->command) {
                $this->command->info("DataSeeder: team {$team->id} -> {$tripCount} trips");
            }
            Trip::factory($tripCount)
                ->for($team)
                ->create()
                ->each(function (Trip $trip) use ($expenseTypeIds) {
                    // Create at least 10 expenses per trip via API route
                    $numExpenses = max(10, random_int(10, 15));
                    $success = 0;
                    $failed = 0;
                    for ($i = 0; $i < $numExpenses; $i++) {
                        $expenseId = $expenseTypeIds[array_rand($expenseTypeIds)];
                        $amount = random_int(100, 50000) / 100; // 1.00 - 500.00

                        $request = Request::create(
                            "/api/v1/trips/{$trip->id}/expenses",
                            'POST',
                            [
                                'expense_id' => $expenseId,
                                'amount' => $amount,
                            ]
                        );

                        // Handle the request through the kernel so it goes through routing, validation, etc.
                        $response = app()->handle($request);
                        $status = $response->getStatusCode();
                        if ($status === 201) {
                            $success++;
                        } else {
                            $failed++;
                            Log::warning('DataSeeder: failed to create expense', [
                                'trip_id' => $trip->id,
                                'status' => $status,
                                'body' => method_exists($response, 'getContent') ? $response->getContent() : null,
                            ]);
                        }
                    }
                    Log::info('DataSeeder: trip expenses created', [
                        'trip_id' => $trip->id,
                        'attempted' => $numExpenses,
                        'created' => $success,
                        'failed' => $failed,
                    ]);
                    if ($this->command) {
                        $this->command->info("DataSeeder: trip {$trip->id} expenses -> created {$success}, failed {$failed}");
                    }
                });
        });
        Log::info('DataSeeder: done');
        if ($this->command) {
            $this->command->info('DataSeeder: done');
        }
    }
}


