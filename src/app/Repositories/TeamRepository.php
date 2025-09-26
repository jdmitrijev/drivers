<?php

namespace App\Repositories;

use App\Models\Team;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use App\Models\User;

class TeamRepository
{
    public function paginate(int $perPage = 15, ?string $search = null, ?int $page = null): LengthAwarePaginator
    {
        $query = Team::query()->with('drivers');
        if ($search) {
            $query->where('name', 'like', '%'.$search.'%');
        }
        return $query->orderBy('name')->paginate($perPage, ['*'], 'page', $page);
    }

    public function all(): Collection
    {
        return Team::with('drivers')->orderBy('name')->get();
    }

    public function find(int $id): ?Team
    {
        return Team::with('drivers')->find($id);
    }

    public function create(array $data): Team
    {
        return Team::create($data);
    }

    public function update(Team $team, array $data): Team
    {
        $team->update($data);
        return $team;
    }

    public function delete(Team $team): void
    {
        $team->delete();
    }

    public function addDriver(Team $team, User $user): void
    {
        $team->addDriver($user);
    }

    public function removeDriver(Team $team, User $user): void
    {
        $team->removeDriver($user);
    }
}


