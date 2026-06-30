<?php

namespace App\DTOs\Auth;

/**
 * Typed transfer object for user registration.
 */
final readonly class RegisterDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
    ) {}

    /**
     * @param  array<string, string>  $validated
     */
    public static function fromRequest(array $validated): self
    {
        return new self(
            name:     $validated['name'],
            email:    $validated['email'],
            password: $validated['password'],
        );
    }
}
