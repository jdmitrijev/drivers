<?php

namespace App\Repositories;

use App\Models\Trip;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TripRepository
{
    public function paginate(int $perPage = 15, ?string $search = null, ?int $page = null): LengthAwarePaginator
    {
        $query = Trip::query()->with('team');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('from', 'like', '%'.$search.'%')
                  ->orWhere('to', 'like', '%'.$search.'%');
            });
        }
        return $query->orderByDesc('id')->paginate($perPage, ['*'], 'page', $page);
    }

    public function all(): Collection
    {
        return Trip::with('team')->orderByDesc('id')->get();
    }

    public function find(int $id): ?Trip
    {
        return Trip::with(['team', 'expenses.driverShares', 'expenses.expenseType'])->find($id);
    }

    public function create(array $data): Trip
    {
        return Trip::create($data);
    }

    public function update(Trip $trip, array $data): Trip
    {
        $trip->update($data);
        return $trip;
    }

    public function delete(Trip $trip): void
    {
        $trip->delete();
    }
}


