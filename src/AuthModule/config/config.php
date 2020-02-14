<?php

use App\AuthModule\ForbiddenMiddleware;
use function Hypario\object;

return [
    'auth.login' => '/login',
    'auth.logout' => '/logout',

    ForbiddenMiddleware::class => object()->constructor(\Hypario\get('auth.login'))

];
