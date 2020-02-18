<?php

namespace App\AuthModule;

class User implements \Framework\Auth\User {

    public $id;
    public $username;
    public $email;
    public $password;
    public $totpKey;

    /**
     * @inheritDoc
     */
    public function getUsername(): string
    {
        return $this->getUsername();
    }

    /**
     * @inheritDoc
     */
    public function getRoles(): array
    {
        return [];
    }
}
