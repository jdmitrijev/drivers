<?php

namespace App\Http\Requests\TripExpense;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTripExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'expense_id' => ['sometimes', 'integer', 'exists:expense_types,id'],
            'amount' => ['sometimes', 'numeric', 'min:0'],
        ];
    }
}


