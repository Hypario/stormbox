<?php namespace Framework\Session;

interface SessionInterface
{
    /**
     * Get an information in session
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null);

    /**
     * Add an information in session
     * @param string $key
     * @param $value
     * @return mixed
     */
    public function set(string $key, $value): void;

    /**
     * Delete an information in session
     * @param string $key
     * @return mixed
     */
    public function delete(string $key): void;
}
