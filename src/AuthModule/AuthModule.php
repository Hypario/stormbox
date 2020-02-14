<?php


namespace App\AuthModule;


use App\AuthModule\Action\LoginAction;
use App\AuthModule\Action\LoginAttemptAction;
use App\AuthModule\Action\LogoutAction;
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
        $renderer->addPath(__DIR__ . '/views', 'auth');
        $router->get($container->get('auth.login'), LoginAction::class, 'auth.login');
        $router->post($container->get('auth.login'), LoginAttemptAction::class);
        $router->post($container->get('auth.logout'), LogoutAction::class, 'auth.logout');
        // routes to sign in, sign up and logout
    }

}
