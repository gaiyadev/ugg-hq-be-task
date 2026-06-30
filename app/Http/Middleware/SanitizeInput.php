<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SanitizeInput Middleware
 *
 * Strips leading/trailing whitespace from all string inputs.
 * Converts empty strings to null to prevent empty-string storage.
 *
 * Applied globally to all API routes (registered in bootstrap/app.php).
 *
 * Note: XSS prevention is handled by Laravel's output escaping and
 * validation rules. This middleware focuses on input normalization only.
 */
class SanitizeInput
{
    /**
     * Fields that should NOT be sanitized (passwords, tokens, etc.).
     *
     * @var array<string>
     */
    private array $excluded = [
        'password',
        'password_confirmation',
        'current_password',
        'token',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $input = $request->all();
        $this->sanitize($input);
        $request->merge($input);

        return $next($request);
    }

    /**
     * Recursively trim and nullify empty strings.
     *
     * @param  array<string, mixed>  $data
     */
    private function sanitize(array &$data): void
    {
        foreach ($data as $key => &$value) {
            if (in_array($key, $this->excluded, true)) {
                continue;
            }

            if (is_array($value)) {
                $this->sanitize($value);
            } elseif (is_string($value)) {
                $value = trim($value);
                if ($value === '') {
                    $value = null;
                }
            }
        }
    }
}
