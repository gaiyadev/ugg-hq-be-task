<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $userId = $this->route('id');

        return [
            'name'   => ['sometimes', 'string', 'min:2', 'max:100'],
            'email'  => [
                'sometimes', 'string', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'status' => ['sometimes', 'string', 'in:active,inactive,suspended'],
            'password' => ['sometimes', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()],
        ];
    }
}
