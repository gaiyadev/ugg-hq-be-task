<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
/**
 * ForgotPasswordRequest
 *
 * Validates the email for password reset initiation.
 * Intentionally does NOT check if the email exists —
 * returning "email not found" would leak account existence.
 */
class ForgotPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Email address is required.',
            'email.email'    => 'Please provide a valid email address.',
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
