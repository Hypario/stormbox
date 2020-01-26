<?php

namespace Framework\Auth;


interface Auth
{
    /**
     * Return a user
     * @return mixed
     */
    public function getUser(): ?User;

}
