<?php

namespace App\Exceptions\Resource;

use Exception;

class ResourceNotFoundException extends Exception
{
    public function __construct(string $id)
    {
        parent::__construct("Resource with ID [{$id}] not found.", 404);
    }
}
