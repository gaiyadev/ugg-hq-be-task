<?php

namespace App\Exceptions\Auth;

use Exception;

class AccountSuspendedException extends Exception
{
    public function __construct(string $message = 'Your account has been suspended. Please contact support.')
    {
        parent::__construct($message, 403);
    }
}
