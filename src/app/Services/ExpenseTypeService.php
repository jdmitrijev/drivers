<?php

namespace App\Services;

use App\Models\ExpenseType;
use App\Repositories\ExpenseTypeRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ExpenseTypeService
{
    public function __construct(private ExpenseTypeRepository $repository)
    {
    }

    public function paginate(int $perPage = 15, ?string $search = null, ?int $page = null): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage, $search, $page);
    }

    public function all(): Collection
    {
        return $this->repository->all();
    }

    public function findOrFail(int $id): ExpenseType
    {
        $expenseType = $this->repository->find($id);
        if (! $expenseType) {
            abort(404, 'Expense type not found');
        }
        return $expenseType;
    }

    public function create(array $data): ExpenseType
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): ExpenseType
    {
        $expenseType = $this->findOrFail($id);
        return $this->repository->update($expenseType, $data);
    }

    public function delete(int $id): void
    {
        $expenseType = $this->findOrFail($id);
        $this->repository->delete($expenseType);
    }
}


