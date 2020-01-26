<?php

namespace Framework\Exception;

use Throwable;

class CsrfInvalidException extends \Exception
{

    /**
     * CsrfInvalidException constructor.
     * Error code to define
     */
    public function __construct()
    {
        parent::__construct("L'action demandée ne viens pas de notre site, action refusé", 0, null);
    }

}
