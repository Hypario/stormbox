<?php

namespace Framework;

/**
 * Handle all the exceptions known by the API
 * Class KnownExceptions
 * @package Framework
 */
class KnownException extends \Exception
{

    public function __construct(int $code = 0)
    {
        parent::__construct("", $code, null);
    }

}
