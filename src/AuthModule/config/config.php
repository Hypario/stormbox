<?php

use App\AuthModule\ForbiddenMiddleware;
use function Hypario\get;
use function Hypario\object;

return [
    'auth.login' => '/login',
    'auth.logout' => '/logout',
    'auth.register' =>'/register',

    ForbiddenMiddleware::class => object()->constructor(get('auth.login'))

];
