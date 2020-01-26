<?php

namespace Framework\Auth;

/**
 * Interface User
 * @package Framework\Auth
 */
interface User {

    /**
     * @return string
     */
    public function getUsername(): string;

    /**
     * @return array
     */
    public function getRoles(): array;

}
