<?php

use App\AuthModule\ForbiddenMiddleware;
use function Hypario\get;
use function Hypario\object;

return [
    'auth.login' => '/login',
    'auth.logout' => '/logout',
    'auth.register' =>'/register',
    'auth.activate' => '/activate/{code:[a-zA-Z0-9]+}',
    'auth.totp' => '/totp',
    'auth.distotp' =>'/totp/remove',
    'auth.loginTotp' => '/login/totp',

    'password.algo' => PASSWORD_ARGON2ID,

    ForbiddenMiddleware::class => object()->constructor(get('auth.login'))

];
