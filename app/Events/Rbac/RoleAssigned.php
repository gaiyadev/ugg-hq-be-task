<?php

namespace App\Events\Rbac;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoleAssigned
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly User $targetUser,
        public readonly Role $role,
        public readonly User $actor,
    ) {}
}
