<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TripExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id',
        'expense_id',
        'amount',
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function expenseType(): BelongsTo
    {
        return $this->belongsTo(ExpenseType::class, 'expense_id');
    }

    public function driverShares(): HasMany
    {
        return $this->hasMany(TripExpensesDriver::class);
    }
}


