<?php

namespace App\Http\Controllers;

use App\Http\Requests\TripExpense\DestroyTripExpenseRequest;
use App\Http\Requests\TripExpense\StoreTripExpenseRequest;
use App\Http\Requests\TripExpense\UpdateTripExpenseRequest;
use App\Services\TripService;
use Illuminate\Http\JsonResponse;

class TripExpenseController extends Controller
{
    public function __construct(private TripService $service)
    {
    }

    public function store(StoreTripExpenseRequest $request): JsonResponse
    {
        return $this->tryCatch(function () use ($request) {
            $tripId = (int) $request->route('trip');
            $expense = $this->service->addExpense($tripId, $request->validated());
            return response()->json($expense, 201);
        });
    }

    public function update(UpdateTripExpenseRequest $request): JsonResponse
    {
        return $this->tryCatch(function () use ($request) {
            $tripId = (int) $request->route('trip');
            $expenseId = (int) $request->route('expense');
            $expense = $this->service->updateExpense($tripId, $expenseId, $request->validated());
            return response()->json($expense);
        });
    }

    public function destroy(DestroyTripExpenseRequest $request): JsonResponse
    {
        return $this->tryCatch(function () use ($request) {
            $tripId = (int) $request->route('trip');
            $expenseId = (int) $request->route('expense');
            $this->service->deleteExpense($tripId, $expenseId);
            return response()->json(null, 204);
        });
    }
}


