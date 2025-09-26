<?php

namespace App\Http\Controllers;

use App\Http\Requests\Trip\DestroyTripRequest;
use App\Http\Requests\Trip\IndexTripRequest;
use App\Http\Requests\Trip\ShowTripRequest;
use App\Http\Requests\Trip\StoreTripRequest;
use App\Http\Requests\Trip\UpdateTripRequest;
use App\Services\TripService;
use Illuminate\Http\JsonResponse;

class TripController extends Controller
{
    public function __construct(private TripService $service)
    {
    }

    public function index(IndexTripRequest $request): JsonResponse
    {
        return $this->tryCatch(function () use ($request) {
            $perPage = (int) ($request->validated()['per_page'] ?? 15);
            $search = $request->validated()['q'] ?? null;
            $page = isset($request->validated()['page']) ? (int) $request->validated()['page'] : null;
            $data = $this->service->paginate($perPage, $search, $page);
            return response()->json($data);
        });
    }

    public function show(ShowTripRequest $request): JsonResponse
    {
        return $this->tryCatch(function () use ($request) {
            $id = (int) $request->route('trip');
            $trip = $this->service->findOrFail($id);
            return response()->json($trip);
        });
    }

    public function store(StoreTripRequest $request): JsonResponse
    {
        return $this->tryCatch(function () use ($request) {
            $trip = $this->service->create($request->validated());
            return response()->json($trip, 201);
        });
    }

    public function update(UpdateTripRequest $request): JsonResponse
    {
        return $this->tryCatch(function () use ($request) {
            $id = (int) $request->route('trip');
            $trip = $this->service->update($id, $request->validated());
            return response()->json($trip);
        });
    }

    public function destroy(DestroyTripRequest $request): JsonResponse
    {
        return $this->tryCatch(function () use ($request) {
            $id = (int) $request->route('trip');
            $this->service->delete($id);
            return response()->json(null, 204);
        });
    }
}


