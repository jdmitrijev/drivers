<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripExpensesDriver extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_expense_id',
        'user_id',
        'amount',
    ];

    public function expense(): BelongsTo
    {
        return $this->belongsTo(TripExpense::class, 'trip_expense_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}


