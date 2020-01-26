<?php

namespace Framework\Exception;

/**
 * Handle all the Exceptions known
 * Class KnownException
 * @package Framework\Exception
 */
class KnownException extends \Exception
{
    public function __construct(int $code = 0)
    {
        parent::__construct("", $code, null);
    }
}
