<?php

namespace App\Http\Requests\TripExpense;

use Illuminate\Foundation\Http\FormRequest;

class DestroyTripExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }
}


