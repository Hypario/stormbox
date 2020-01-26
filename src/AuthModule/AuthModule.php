<?php


namespace App\AuthModule;


use Framework\Module;
use Hypario\Router\Router;

class AuthModule extends Module
{

    public const DEFINITIONS = './config/config.php';

    const MIGRATIONS = __DIR__ . '/db/migrations';

    const SEEDS = __DIR__ . '/db/seeds';

    public function __construct(Router $router)
    {
        // routes to sign in, sign up and logout
    }

}
