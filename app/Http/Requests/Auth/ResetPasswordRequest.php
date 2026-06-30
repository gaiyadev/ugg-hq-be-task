<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
/**
 * ResetPasswordRequest
 *
 * Validates the token + new password for password reset completion.
 * Token validation (expiry, single-use) is handled in AuthService.
 */
class ResetPasswordRequest extends FormRequest
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
        return [
            'token'    => ['required', 'string'],
            'email'    => ['required', 'string', 'email'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'token.required'     => 'Reset token is required.',
            'email.required'     => 'Email address is required.',
            'password.required'  => 'New password is required.',
            'password.confirmed' => 'Passwords do not match.',
        ];
    }

        protected function failedValidation(Validator $validator)
    {
        // Get the first error message
        $firstError = $validator->errors()->first();

        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => $firstError,
            ], 422)
        );
    }

    public function wantsJson(): bool
    {
        return true;
    }
}
