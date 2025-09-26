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

            $perDriverBase = floor($data['amount'] * 100 / count($drivers)) / 100;
            $remainder = round($data['amount'] - ($perDriverBase * count($drivers)), 2);

            foreach ($drivers as $index => $driverId) {
                $amount = $perDriverBase + (($index === 0) ? $remainder : 0);
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

            $perDriverBase = floor($data['amount'] * 100 / count($drivers)) / 100;
            $remainder = round($data['amount'] - ($perDriverBase * count($drivers)), 2);

            foreach ($drivers as $index => $driverId) {
                $amount = $perDriverBase + (($index === 0) ? $remainder : 0);
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
}


