<?php

namespace App\Http\Requests\ExpenseType;

use Illuminate\Foundation\Http\FormRequest;

class IndexExpenseTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:200'],
            'q' => ['sometimes', 'string', 'max:255'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }
}


