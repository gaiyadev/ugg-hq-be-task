<?php

namespace App\Exceptions\Permission;

use Exception;

class InsufficientPermissionException extends Exception
{
    public function __construct(string $permission = '')
    {
        $message = $permission
            ? "You do not have the required permission: [{$permission}]."
            : 'You do not have permission to perform this action.';

        parent::__construct($message, 403);
    }
}
