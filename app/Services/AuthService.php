<?php

namespace App\Services;

use App\DTOs\Auth\LoginDTO;
use App\DTOs\Auth\RegisterDTO;
use App\Events\Auth\UserLoggedIn;
use App\Events\Auth\UserLoggedOut;
use App\Exceptions\Auth\AccountSuspendedException;
use App\Exceptions\Auth\InvalidCredentialsException;
use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Laravel\Sanctum\PersonalAccessToken;

class AuthService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    /**
     * Register a new user.
     * Fires Laravel's Registered event for email verification support.
     * Assigns the default 'user' role automatically.
     *
     * @return array{user: User, token: string}
     */
    public function register(RegisterDTO $dto, Request $request): array
    {
        $user = $this->userRepository->create([
            'name'     => $dto->name,
            'email'    => $dto->email,
            'password' => $dto->password, // Hashed by model cast
            'status'   => 'active',
        ]);

        // Auto-assign the default 'user' role
        $defaultRole = \App\Models\Role::where('slug', 'user')->first();
        if ($defaultRole) {
            $user->roles()->syncWithoutDetaching([$defaultRole->id]);
        }

        // Fire Laravel's Registered event (enables email verification)
        event(new Registered($user));

        $token = $user->createToken(
            name:       'auth_token',
            abilities:  ['*'],
            expiresAt:  now()->addDays(config('sanctum.expiration', 1) ?? 1),
        )->plainTextToken;

        return ['user' => $user->load('roles.permissions'), 'token' => $token];
    }

    /**
     * Authenticate user credentials and issue a Sanctum token.
     *
     * @return array{user: User, token: string}
     * @throws InvalidCredentialsException
     * @throws AccountSuspendedException
     */
    public function login(LoginDTO $dto, Request $request): array
    {
        $user = $this->userRepository->findByEmail($dto->email);

        // Same error for wrong email OR wrong password — prevents user enumeration
        if (!$user || !Hash::check($dto->password, $user->password)) {
            throw new InvalidCredentialsException();
        }

        if ($user->status === 'suspended') {
            throw new AccountSuspendedException();
        }

        if ($user->status === 'inactive') {
            throw new InvalidCredentialsException('This account is inactive. Please contact support.');
        }

        // Revoke all previous tokens for this user (single session)
        $user->tokens()->delete();

        $token = $user->createToken(
            name:      'auth_token',
            abilities: ['*'],
            expiresAt: now()->addDays(config('sanctum.expiration', 1) ?? 1),
        )->plainTextToken;

        // Dispatch login event → AuditLogListener
        event(new UserLoggedIn($user, $request->ip(), $request->userAgent() ?? ''));

        return ['user' => $user->load('roles.permissions'), 'token' => $token];
    }

    /**
     * Revoke the current access token and log the event.
     */
    public function logout(User $user): void
    {
        // Revoke current token only
        $user->currentAccessToken()->delete();

        event(new UserLoggedOut($user));
    }

    /**
     * Revoke current token and issue a fresh one.
     * Used to extend sessions without re-authentication.
     *
     * @return array{user: User, token: string}
     */
    public function refresh(User $user): array
    {
        // Delete current token
        $user->currentAccessToken()->delete();

        // Issue a new one
        $token = $user->createToken(
            name:      'auth_token',
            abilities: ['*'],
            expiresAt: now()->addDays(config('sanctum.expiration', 1) ?? 1),
        )->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }

    /**
     * Send a password reset link to the given email.
     * Returns a generic success message regardless of whether
     * the email exists — prevents account enumeration.
     */
    public function forgotPassword(string $email): string
    {
        // Laravel's Password broker handles token creation and email dispatch
        Password::sendResetLink(['email' => $email]);

        // Always return the same message — security requirement
        return 'If an account with this email exists, a password reset link has been sent.';
    }

    /**
     * Reset the user's password using the provided token.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function resetPassword(string $token, string $email, string $password): string
    {
        $status = Password::reset(
            credentials: ['email' => $email, 'token' => $token, 'password' => $password],
            callback: function (User $user, string $password) {
                $user->forceFill(['password' => $password])->save();
                // Revoke all tokens after password reset — forces re-login
                $user->tokens()->delete();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return 'Password has been reset successfully. Please log in with your new password.';
    }
}
