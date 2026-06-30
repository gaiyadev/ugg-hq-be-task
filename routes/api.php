<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — v1
|--------------------------------------------------------------------------
| All routes are prefixed with /api/v1 via bootstrap/app.php routing.
| Controllers are in App\Http\Controllers\Api\V1\
|
| Auth        → AuthController
| Users       → UserController
| Roles       → RoleController
| Permissions → PermissionController
| Resources   → ResourceController
| Audit Logs  → AuditLogController
| Dashboard   → DashboardController
|--------------------------------------------------------------------------
*/

// ─── Health check (public) ────────────────────────────────────────────────────
Route::get('/health', fn() => response()->json(['status' => 'ok', 'version' => '1.0.0']));

// ─── Auth (public + rate limited) ─────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('/register', [\App\Http\Controllers\Api\V1\AuthController::class, 'register'])
        ->middleware('throttle:auth');

    Route::post('/login', [\App\Http\Controllers\Api\V1\AuthController::class, 'login'])
        ->middleware('throttle:auth');

    Route::post('/forgot-password', [\App\Http\Controllers\Api\V1\AuthController::class, 'forgotPassword'])
        ->middleware('throttle:auth');

    Route::post('/reset-password', [\App\Http\Controllers\Api\V1\AuthController::class, 'resetPassword'])
        ->middleware('throttle:auth');
});

// ─── Protected routes (require Sanctum auth) ─────────────────────────────────
Route::middleware(['auth:sanctum'])->group(function () {

    // ── Auth (authenticated actions) ─────────────────────────────────────
    Route::prefix('auth')->group(function () {
        Route::post('/logout',  [\App\Http\Controllers\Api\V1\AuthController::class, 'logout']);
        Route::post('/refresh', [\App\Http\Controllers\Api\V1\AuthController::class, 'refresh']);
        Route::get('/me',       [\App\Http\Controllers\Api\V1\AuthController::class, 'me']);
    });

    // ── Dashboard ─────────────────────────────────────────────────────────
    Route::get('/dashboard', [\App\Http\Controllers\Api\V1\DashboardController::class, 'index'])
        ->middleware('permission:dashboard.view');

    // ── Users ─────────────────────────────────────────────────────────────
    Route::prefix('users')->group(function () {
        Route::get('/',                [\App\Http\Controllers\Api\V1\UserController::class, 'index'])
            ->middleware('permission:users.view');
        Route::post('/',               [\App\Http\Controllers\Api\V1\UserController::class, 'store'])
            ->middleware('permission:users.create');
        Route::get('/{id}',            [\App\Http\Controllers\Api\V1\UserController::class, 'show'])
            ->middleware('permission:users.view');
        Route::put('/{id}',            [\App\Http\Controllers\Api\V1\UserController::class, 'update'])
            ->middleware('permission:users.update');
        Route::delete('/{id}',         [\App\Http\Controllers\Api\V1\UserController::class, 'destroy'])
            ->middleware('permission:users.delete');
        Route::post('/{id}/roles',     [\App\Http\Controllers\Api\V1\UserController::class, 'assignRole'])
            ->middleware('permission:roles.assign');
        Route::delete('/{id}/roles/{roleId}', [\App\Http\Controllers\Api\V1\UserController::class, 'removeRole'])
            ->middleware('permission:roles.assign');
    });

    // ── Roles ─────────────────────────────────────────────────────────────
    Route::prefix('roles')->group(function () {
        Route::get('/',                    [\App\Http\Controllers\Api\V1\RoleController::class, 'index'])
            ->middleware('permission:roles.view');
        Route::post('/',                   [\App\Http\Controllers\Api\V1\RoleController::class, 'store'])
            ->middleware('permission:roles.create');
        Route::get('/{id}',                [\App\Http\Controllers\Api\V1\RoleController::class, 'show'])
            ->middleware('permission:roles.view');
        Route::put('/{id}',                [\App\Http\Controllers\Api\V1\RoleController::class, 'update'])
            ->middleware('permission:roles.update');
        Route::delete('/{id}',             [\App\Http\Controllers\Api\V1\RoleController::class, 'destroy'])
            ->middleware('permission:roles.delete');
        Route::post('/{id}/permissions',   [\App\Http\Controllers\Api\V1\RoleController::class, 'syncPermissions'])
            ->middleware('permission:permissions.assign');
    });

    // ── Permissions ───────────────────────────────────────────────────────
    Route::prefix('permissions')->group(function () {
        Route::get('/',        [\App\Http\Controllers\Api\V1\PermissionController::class, 'index'])
            ->middleware('permission:permissions.view');
        Route::get('/grouped', [\App\Http\Controllers\Api\V1\PermissionController::class, 'grouped'])
            ->middleware('permission:permissions.view');
    });

    // ── Resources ─────────────────────────────────────────────────────────
    Route::prefix('resources')->group(function () {
        Route::get('/',                    [\App\Http\Controllers\Api\V1\ResourceController::class, 'index'])
            ->middleware('permission:resources.view');
        Route::post('/',                   [\App\Http\Controllers\Api\V1\ResourceController::class, 'store'])
            ->middleware('permission:resources.create');
        Route::get('/{id}',                [\App\Http\Controllers\Api\V1\ResourceController::class, 'show'])
            ->middleware('permission:resources.view');
        Route::put('/{id}',                [\App\Http\Controllers\Api\V1\ResourceController::class, 'update'])
            ->middleware('permission:resources.update');
        Route::delete('/{id}',             [\App\Http\Controllers\Api\V1\ResourceController::class, 'destroy'])
            ->middleware('permission:resources.delete');
        Route::post('/{id}/submit',        [\App\Http\Controllers\Api\V1\ResourceController::class, 'submit'])
            ->middleware('permission:resources.update');
        Route::post('/{id}/approve',       [\App\Http\Controllers\Api\V1\ResourceController::class, 'approve'])
            ->middleware('permission:resources.approve');
        Route::post('/{id}/reject',        [\App\Http\Controllers\Api\V1\ResourceController::class, 'reject'])
            ->middleware('permission:resources.approve');
        Route::get('/{id}/verify-signature', [\App\Http\Controllers\Api\V1\ResourceController::class, 'verifySignature'])
            ->middleware('permission:resources.view');
    });

    // ── Audit Logs ────────────────────────────────────────────────────────
    Route::prefix('audit-logs')->group(function () {
        Route::get('/',      [\App\Http\Controllers\Api\V1\AuditLogController::class, 'index'])
            ->middleware('permission:audit.view');
        Route::get('/{id}',  [\App\Http\Controllers\Api\V1\AuditLogController::class, 'show'])
            ->middleware('permission:audit.view');
    });
});
