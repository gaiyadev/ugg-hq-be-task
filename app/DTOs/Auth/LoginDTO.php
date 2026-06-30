<?php

namespace App\DTOs\Auth;

/**
 * Typed transfer object for login credentials.
 * Prevents raw array passing between controller and service.
 */
final readonly class LoginDTO
{
    public function __construct(
        public string $email,
        public string $password,
    ) {}

    /**
     * @param  array<string, string>  $validated  From LoginRequest::validated()
     */
    public static function fromRequest(array $validated): self
    {
        return new self(
            email:    $validated['email'],
            password: $validated['password'],
        );
    }
}
