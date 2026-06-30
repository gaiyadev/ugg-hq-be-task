<?php

namespace App\Events\Resource;

use App\Models\Resource;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ResourceDeleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Resource $resource,
        public readonly User $actor,
    ) {}
}
