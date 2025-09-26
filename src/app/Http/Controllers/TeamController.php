<?php

namespace App\Http\Controllers;

use App\Http\Requests\Team\DestroyTeamRequest;
use App\Http\Requests\Team\IndexTeamRequest;
use App\Http\Requests\Team\ShowTeamRequest;
use App\Http\Requests\Team\StoreTeamRequest;
use App\Http\Requests\Team\UpdateTeamRequest;
use App\Http\Requests\Team\AddDriverRequest;
use App\Http\Requests\Team\RemoveDriverRequest;
use App\Services\TeamService;
use Illuminate\Http\JsonResponse;

class TeamController extends Controller
{
    public function __construct(private TeamService $service)
    {
    }

    public function index(IndexTeamRequest $request): JsonResponse
    {
        return $this->tryCatch(function () use ($request) {
            $perPage = (int) ($request->validated()['per_page'] ?? 15);
            $search = $request->validated()['q'] ?? null;
            $page = isset($request->validated()['page']) ? (int) $request->validated()['page'] : null;
            $data = $this->service->paginate($perPage, $search, $page);
            return response()->json($data);
        });
    }

    public function show(ShowTeamRequest $request): JsonResponse
    {
        return $this->tryCatch(function () use ($request) {
            $id = (int) $request->route('team');
            $team = $this->service->findOrFail($id);
            return response()->json($team);
        });
    }

    public function store(StoreTeamRequest $request): JsonResponse
    {
        return $this->tryCatch(function () use ($request) {
            $team = $this->service->create($request->validated());
            return response()->json($team, 201);
        });
    }

    public function update(UpdateTeamRequest $request): JsonResponse
    {
        return $this->tryCatch(function () use ($request) {
            $id = (int) $request->route('team');
            $team = $this->service->update($id, $request->validated());
            return response()->json($team);
        });
    }

    public function destroy(DestroyTeamRequest $request): JsonResponse
    {
        return $this->tryCatch(function () use ($request) {
            $id = (int) $request->route('team');
            $this->service->delete($id);
            return response()->json(null, 204);
        });
    }

    public function addDriver(AddDriverRequest $request): JsonResponse
    {
        return $this->tryCatch(function () use ($request) {
            $teamId = (int) $request->route('team');
            $userId = (int) $request->validated()['user_id'];
            $this->service->addDriver($teamId, $userId);
            return response()->json(null, 204);
        });
    }

    public function removeDriver(RemoveDriverRequest $request): JsonResponse
    {
        return $this->tryCatch(function () use ($request) {
            $teamId = (int) $request->route('team');
            $userId = (int) $request->validated()['user_id'];
            $this->service->removeDriver($teamId, $userId);
            return response()->json(null, 204);
        });
    }
}


