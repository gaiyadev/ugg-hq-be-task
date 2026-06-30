<?php

namespace App\Exceptions\Resource;

use App\Enums\ResourceStatus;
use Exception;

class InvalidStatusTransitionException extends Exception
{
    public function __construct(ResourceStatus $from, ResourceStatus $to)
    {
        parent::__construct(
            "Cannot transition resource from [{$from->label()}] to [{$to->label()}].",
            422
        );
    }
}
