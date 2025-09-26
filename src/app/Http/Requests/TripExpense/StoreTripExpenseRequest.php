<?php

namespace App\Http\Requests\TripExpense;

use Illuminate\Foundation\Http\FormRequest;

class StoreTripExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'expense_id' => ['required', 'integer', 'exists:expense_types,id'],
            'amount' => ['required', 'numeric', 'min:0'],
        ];
    }
}


