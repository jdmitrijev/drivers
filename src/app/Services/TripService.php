<?php

namespace App\Services;

use App\Models\ExpenseType;
use App\Models\Team;
use App\Models\Trip;
use App\Models\TripExpense;
use App\Models\TripExpensesDriver;
use App\Repositories\TripExpenseRepository;
use App\Repositories\TripRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class TripService
{
    public function __construct(
        private readonly TripRepository $tripRepository,
        private readonly TripExpenseRepository $tripExpenseRepository,
    ) {}

    // Methods expected by TripController
    public function paginate(int $perPage = 15, ?string $search = null, ?int $page = null)
    {
        return $this->tripRepository->paginate($perPage, $search, $page);
    }

    public function findOrFail(int $id): Trip
    {
        $trip = $this->tripRepository->find($id);
        if (!$trip) {
            throw new ModelNotFoundException('Trip not found');
        }
        return $trip;
    }

    public function create(array $data): Trip
    {
        return $this->createTrip($data);
    }

    public function update(int $tripId, array $data): Trip
    {
        return $this->updateTrip($tripId, $data);
    }

    public function delete(int $tripId): void
    {
        $this->deleteTrip($tripId);
    }

    // Existing API
    public function createTrip(array $data): Trip
    {
        return $this->tripRepository->create($data);
    }

    public function updateTrip(int $tripId, array $data): Trip
    {
        $trip = $this->tripRepository->find($tripId);
        if (!$trip) {
            throw new ModelNotFoundException('Trip not found');
        }
        $this->tripRepository->update($trip, $data);
        return $trip;
    }

    public function deleteTrip(int $tripId): void
    {
        $trip = $this->tripRepository->find($tripId);
        if (!$trip) {
            throw new ModelNotFoundException('Trip not found');
        }
        $this->tripRepository->delete($trip);
    }

    public function listTrips(?string $q, int $page, int $perPage)
    {
        return $this->tripRepository->paginate($perPage, $q, $page);
    }

    public function addExpense(int $tripId, array $data): TripExpense
    {
        $trip = $this->tripRepository->find($tripId);
        if (!$trip) {
            throw new ModelNotFoundException('Trip not found');
        }

        $expenseType = ExpenseType::findOrFail($data['expense_id']);

        return DB::transaction(function () use ($trip, $data) {
            $expense = $this->tripExpenseRepository->create([
                'trip_id' => $trip->id,
                'expense_id' => $data['expense_id'],
                'amount' => $data['amount'],
            ]);

            $team = Team::findOrFail($trip->team_id);
            $drivers = $team->drivers()->pluck('users.id')->all();

            if (empty($drivers)) {
                throw new ModelNotFoundException('No drivers assigned to team');
            }

            $shares = $this->calculateDriverShares($trip, (float) $data['amount'], $drivers, null);

            foreach ($shares as $driverId => $amount) {
                TripExpensesDriver::create([
                    'trip_expense_id' => $expense->id,
                    'user_id' => $driverId,
                    'amount' => $amount,
                ]);
            }

            $trip->total_amount = round($trip->total_amount + $data['amount'], 2);
            $trip->save();

            return $expense;
        });
    }

    public function updateExpense(int $tripId, int $expenseId, array $data): TripExpense
    {
        $trip = $this->tripRepository->find($tripId);
        if (!$trip) {
            throw new ModelNotFoundException('Trip not found');
        }
        $expense = $this->tripExpenseRepository->find($expenseId);
        if (!$expense) {
            throw new ModelNotFoundException('Trip expense not found');
        }

        return DB::transaction(function () use ($trip, $expense, $data) {
            // adjust trip total by removing old amount and adding new
            $trip->total_amount = round($trip->total_amount - $expense->amount + $data['amount'], 2);
            $trip->save();

            $this->tripExpenseRepository->update($expense, [
                'amount' => $data['amount'],
            ]);

            // recompute driver shares
            $expense->driverShares()->delete();

            $team = Team::findOrFail($trip->team_id);
            $drivers = $team->drivers()->pluck('users.id')->all();

            $shares = $this->calculateDriverShares($trip, (float) $data['amount'], $drivers, $expense->id);

            foreach ($shares as $driverId => $amount) {
                TripExpensesDriver::create([
                    'trip_expense_id' => $expense->id,
                    'user_id' => $driverId,
                    'amount' => $amount,
                ]);
            }

            return $expense;
        });
    }

    public function deleteExpense(int $tripId, int $expenseId): void
    {
        $trip = $this->tripRepository->find($tripId);
        if (!$trip) {
            throw new ModelNotFoundException('Trip not found');
        }
        $expense = $this->tripExpenseRepository->find($expenseId);
        if (!$expense) {
            throw new ModelNotFoundException('Trip expense not found');
        }

        DB::transaction(function () use ($trip, $expense) {
            $trip->total_amount = round($trip->total_amount - $expense->amount, 2);
            $trip->save();

            $expense->driverShares()->delete();
            $this->tripExpenseRepository->delete($expense);
        });
    }

    /**
     * Calculate per-driver shares for a given amount, ensuring the difference between
     * any two drivers' cumulative totals on the trip is at most 0.01 after allocation.
     * The extra cents are given to the drivers with the lowest cumulative totals first
     * (ties broken by ascending user_id).
     *
     * @param Trip $trip
     * @param float $amount
     * @param array<int,int> $driverIds
     * @param int|null $excludeExpenseId When updating, exclude this expense from historical totals
     * @return array<int,float> Map of user_id => amount
     */
    private function calculateDriverShares(Trip $trip, float $amount, array $driverIds, ?int $excludeExpenseId = null): array
    {
        $numDrivers = count($driverIds);
        if ($numDrivers === 0) {
            return [];
        }

        $amountCents = (int) round($amount * 100);
        $baseShareCents = intdiv($amountCents, $numDrivers);
        $remainderCents = $amountCents % $numDrivers;

        // Get current cumulative totals per driver for this trip (in cents), excluding the expense if provided
        $totals = array_fill_keys($driverIds, 0);

        $query = DB::table('trip_expenses_drivers as ted')
            ->join('trip_expenses as te', 'te.id', '=', 'ted.trip_expense_id')
            ->where('te.trip_id', '=', $trip->id)
            ->when($excludeExpenseId !== null, function ($q) use ($excludeExpenseId) {
                $q->where('te.id', '!=', $excludeExpenseId);
            })
            ->selectRaw('ted.user_id, SUM(ted.amount) as total_amount')
            ->groupBy('ted.user_id')
            ->get();

        foreach ($query as $row) {
            $uid = (int) $row->user_id;
            if (array_key_exists($uid, $totals)) {
                $totals[$uid] = (int) round(((float) $row->total_amount) * 100);
            }
        }

        // Determine which drivers get the remainder cents: sort by current total asc, then by user_id asc
        $sortedDriverIds = array_keys($totals);
        usort($sortedDriverIds, function ($left, $right) use ($totals) {
            if ($totals[$left] === $totals[$right]) {
                return $left <=> $right;
            }
            return $totals[$left] <=> $totals[$right];
        });

        $result = [];

        // Assign base share to all
        foreach ($driverIds as $userId) {
            $result[$userId] = $baseShareCents;
        }

        // Distribute remainder cents to the lowest totals first
        for ($i = 0; $i < $remainderCents; $i++) {
            $userId = $sortedDriverIds[$i % $numDrivers];
            $result[$userId] += 1;
        }

        // Convert back to dollars
        foreach ($result as $userId => $cents) {
            $result[$userId] = round($cents / 100, 2);
        }

        return $result;
    }
}


