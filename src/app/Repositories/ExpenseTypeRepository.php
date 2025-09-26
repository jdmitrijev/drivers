<?php

namespace App\Repositories;

use App\Models\ExpenseType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ExpenseTypeRepository
{
    public function paginate(int $perPage = 15, ?string $search = null, ?int $page = null): LengthAwarePaginator
    {
        $query = ExpenseType::query();
        if ($search) {
            $query->where('name', 'like', '%'.$search.'%');
        }
        return $query->orderBy('name')->paginate($perPage, ['*'], 'page', $page);
    }

    public function all(): Collection
    {
        return ExpenseType::orderBy('name')->get();
    }

    public function find(int $id): ?ExpenseType
    {
        return ExpenseType::find($id);
    }

    public function create(array $data): ExpenseType
    {
        return ExpenseType::create($data);
    }

    public function update(ExpenseType $expenseType, array $data): ExpenseType
    {
        $expenseType->update($data);
        return $expenseType;
    }

    public function delete(ExpenseType $expenseType): void
    {
        $expenseType->delete();
    }
}


