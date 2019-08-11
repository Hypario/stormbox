<?php

namespace Hypario;

use Throwable;

/**
 * Handle all the exceptions known by the API
 * Class KnownExceptions
 * @package Hypario
 */
class KnownException extends \Exception
{

    public function __construct(int $code = 0)
    {
        parent::__construct("", $code, null);
    }

}
