<?php

namespace App\Services;

use App\Models\Team;
use App\Models\User;
use App\Repositories\TeamRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TeamService
{
    public function __construct(private TeamRepository $repository)
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

    public function findOrFail(int $id): Team
    {
        $team = $this->repository->find($id);
        if (! $team) {
            abort(404, 'Team not found');
        }
        return $team;
    }

    public function create(array $data): Team
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): Team
    {
        $team = $this->findOrFail($id);
        return $this->repository->update($team, $data);
    }

    public function delete(int $id): void
    {
        $team = $this->findOrFail($id);
        $this->repository->delete($team);
    }

    public function addDriver(int $teamId, int $userId): void
    {
        $team = $this->findOrFail($teamId);
        $user = User::find($userId);
        if (! $user) {
            abort(404, 'User not found');
        }
        $this->repository->addDriver($team, $user);
    }

    public function removeDriver(int $teamId, int $userId): void
    {
        $team = $this->findOrFail($teamId);
        $user = User::find($userId);
        if (! $user) {
            abort(404, 'User not found');
        }
        $this->repository->removeDriver($team, $user);
    }
}


