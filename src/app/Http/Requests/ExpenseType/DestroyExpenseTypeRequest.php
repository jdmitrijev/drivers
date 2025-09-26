<?php

namespace App\Http\Requests\ExpenseType;

use Illuminate\Foundation\Http\FormRequest;

class DestroyExpenseTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // id comes from route parameter
        ];
    }
}


