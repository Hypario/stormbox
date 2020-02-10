<?php


namespace App\AuthModule;


use App\AuthModule\Action\LoginAction;
use Framework\Module;
use Framework\Renderer\RendererInterface;
use Hypario\Router\Router;
use Psr\Container\ContainerInterface;

class AuthModule extends Module
{

    const DEFINITIONS = __DIR__ . '/config/config.php';

    const MIGRATIONS = __DIR__ . '/db/migrations';

    const SEEDS = __DIR__ . '/db/seeds';

    public function __construct(ContainerInterface $container, Router $router, RendererInterface $renderer)
    {
        $renderer->addPath('auth', __DIR__ . '/views');
        $router->get($container->get('auth.login'), LoginAction::class, 'auth.login');
        /**
        $router->get($container->get('auth.login'), LoginAttemptAction::class);
         **/
        // routes to sign in, sign up and logout
    }

}
