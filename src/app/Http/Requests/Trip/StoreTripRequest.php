<?php

namespace App\Http\Requests\Trip;

use Illuminate\Foundation\Http\FormRequest;

class StoreTripRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from' => ['required', 'string', 'max:255'],
            'to' => ['required', 'string', 'max:255'],
            'team_id' => ['required', 'integer', 'exists:teams,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['sometimes', 'nullable', 'date', 'after_or_equal:start_date'],
        ];
    }
}


