<?php

namespace Framework\Exception;

/**
 * Handle all the Exceptions known
 * Class KnownException
 * @package Framework\Exception
 */
class KnownException extends \Exception
{
    private bool $json = false;

    public function __construct(int $code = 0, ?string $message = null, bool $json = false)
    {
        parent::__construct($message, $code, null);
        $this->json = $json;
    }

    /**
     * @return bool
     */
    public function isJson(): bool
    {
        return $this->json;
    }
}
