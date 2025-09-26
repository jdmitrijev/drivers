<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\TripExpense;
use App\Models\TripExpensesDriver;
use Illuminate\Contracts\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $trips = Trip::with('team.drivers')->orderByDesc('id')->get();
        return view('reports.index', [
            'trips' => $trips,
            'selectedTrip' => null,
            'drivers' => [],
            'rows' => [],
            'totals' => [
                'total' => 0,
                'perDriver' => [],
            ],
        ]);
    }

    public function show(Trip $trip): View
    {
        $trip->load(['team.drivers', 'expenses.expenseType', 'expenses.driverShares']);

        $drivers = $trip->team->drivers->values();

        $rows = [];
        $driverTotals = [];
        foreach ($drivers as $driver) {
            $driverTotals[$driver->id] = 0.0;
        }

        $grandTotal = 0.0;

        foreach ($trip->expenses as $expense) {
            $label = $expense->expenseType?->name ?? ('Expense #' . $expense->expense_id);
            $amount = (float) $expense->amount;
            $grandTotal = round($grandTotal + $amount, 2);

            $cells = [];
            foreach ($drivers as $driver) {
                $share = $expense->driverShares->firstWhere('user_id', $driver->id);
                $value = $share ? (float) $share->amount : 0.0;
                $driverTotals[$driver->id] = round($driverTotals[$driver->id] + $value, 2);
                $cells[$driver->id] = $value;
            }

            $rows[] = [
                'label' => $label,
                'amount' => $amount,
                'perDriver' => $cells,
            ];
        }

        return view('reports.index', [
            'trips' => Trip::orderByDesc('id')->get(),
            'selectedTrip' => $trip,
            'drivers' => $drivers,
            'rows' => $rows,
            'totals' => [
                'total' => $grandTotal,
                'perDriver' => $driverTotals,
            ],
        ]);
    }
}


