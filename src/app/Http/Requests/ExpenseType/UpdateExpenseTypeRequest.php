<?php

namespace App\Http\Requests\ExpenseType;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExpenseTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
        ];
    }
}


