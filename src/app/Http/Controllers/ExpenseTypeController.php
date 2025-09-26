<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpenseType\DestroyExpenseTypeRequest;
use App\Http\Requests\ExpenseType\IndexExpenseTypeRequest;
use App\Http\Requests\ExpenseType\ShowExpenseTypeRequest;
use App\Http\Requests\ExpenseType\StoreExpenseTypeRequest;
use App\Http\Requests\ExpenseType\UpdateExpenseTypeRequest;
use App\Services\ExpenseTypeService;
use Illuminate\Http\JsonResponse;

class ExpenseTypeController extends Controller
{
    public function __construct(private ExpenseTypeService $service)
    {
    }

    public function index(IndexExpenseTypeRequest $request): JsonResponse
    {
        return $this->tryCatch(function () use ($request) {
            $perPage = (int) ($request->validated()['per_page'] ?? 15);
            $search = $request->validated()['q'] ?? null;
            $page = isset($request->validated()['page']) ? (int) $request->validated()['page'] : null;
            $data = $this->service->paginate($perPage, $search, $page);
            return response()->json($data);
        });
    }

    public function show(ShowExpenseTypeRequest $request): JsonResponse
    {
        return $this->tryCatch(function () use ($request) {
            $id = (int) $request->route('expense_type');
            $expenseType = $this->service->findOrFail($id);
            return response()->json($expenseType);
        });
    }

    public function store(StoreExpenseTypeRequest $request): JsonResponse
    {
        return $this->tryCatch(function () use ($request) {
            $expenseType = $this->service->create($request->validated());
            return response()->json($expenseType, 201);
        });
    }

    public function update(UpdateExpenseTypeRequest $request): JsonResponse
    {
        return $this->tryCatch(function () use ($request) {
            $id = (int) $request->route('expense_type');
            $expenseType = $this->service->update($id, $request->validated());
            return response()->json($expenseType);
        });
    }

    public function destroy(DestroyExpenseTypeRequest $request): JsonResponse
    {
        return $this->tryCatch(function () use ($request) {
            $id = (int) $request->route('expense_type');
            $this->service->delete($id);
            return response()->json(null, 204);
        });
    }
}


