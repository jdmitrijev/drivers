<?php

namespace App\Http\Requests\Team;

use Illuminate\Foundation\Http\FormRequest;

class RemoveDriverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }
}


