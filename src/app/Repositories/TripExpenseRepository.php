<?php

namespace App\Repositories;

use App\Models\TripExpense;

class TripExpenseRepository
{
    public function find(int $id): ?TripExpense
    {
        return TripExpense::with(['driverShares', 'expenseType'])->find($id);
    }

    public function create(array $data): TripExpense
    {
        return TripExpense::create($data);
    }

    public function update(TripExpense $expense, array $data): TripExpense
    {
        $expense->update($data);
        return $expense;
    }

    public function delete(TripExpense $expense): void
    {
        $expense->delete();
    }
}


