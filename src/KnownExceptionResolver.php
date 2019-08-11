<?php


namespace Hypario;


class KnownExceptionResolver
{

    /**
     * All the possible errors
     * @var array
     */
    private $knownErrors = [];

    /**
     * return the error according to the code
     * @param int $code
     * @return string|null
     */
    public function getMessage(int $code): ?string
    {
        return array_key_exists($code, $this->knownErrors) ? $this->knownErrors[$code] : null;
    }

    /**
     * @param int $code
     * @param string $message
     */
    public function register(int $code, string $message) {
        $this->knownErrors[$code] = $message;
    }

}
