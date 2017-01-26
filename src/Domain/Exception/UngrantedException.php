<?php

namespace Netosoft\DomainBundle\Domain\Exception;

use Exception;

class UngrantedException extends DomainException
{
    public function __construct($message = "You aren't authorized to this action", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
