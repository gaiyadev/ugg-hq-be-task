<?php

namespace App\Providers;

use App\Repositories\AuditLogRepository;
use App\Repositories\Interfaces\AuditLogRepositoryInterface;
use App\Repositories\Interfaces\PermissionRepositoryInterface;
use App\Repositories\Interfaces\ResourceRepositoryInterface;
use App\Repositories\Interfaces\RoleRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\PermissionRepository;
use App\Repositories\ResourceRepository;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

/**
 * RepositoryServiceProvider
 *
 * Binds all repository interfaces to their concrete implementations.
 * This is the heart of Dependency Injection in the repository pattern —
 * services depend on interfaces (contracts), not Eloquent classes directly.
 *
 * To swap a repository (e.g. for Redis caching or external API),
 * only change the binding here — zero changes in services or controllers.
 */
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        UserRepositoryInterface::class       => UserRepository::class,
        RoleRepositoryInterface::class       => RoleRepository::class,
        PermissionRepositoryInterface::class => PermissionRepository::class,
        ResourceRepositoryInterface::class   => ResourceRepository::class,
        AuditLogRepositoryInterface::class   => AuditLogRepository::class,
    ];

    public function register(): void
    {
        foreach ($this->bindings as $interface => $implementation) {
            $this->app->bind($interface, $implementation);
        }
    }

    public function boot(): void
    {
        //
    }
}
