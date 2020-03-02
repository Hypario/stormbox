<?php


namespace App\AuthModule;


use App\AuthModule\Action\LoginAction;
use App\AuthModule\Action\LoginAttemptAction;
use App\AuthModule\Action\LoginTotpAction;
use App\AuthModule\Action\LogoutAction;
use App\AuthModule\Action\SignupAction;
use App\AuthModule\Action\TotpAction;
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

        $router->get($container->get('auth.register'), SignupAction::class, 'auth.register');
        $router->post($container->get('auth.register'), SignupAction::class);

        $router->get($container->get('auth.totp'), TotpAction::class, 'auth.totp');
        $router->post($container->get('auth.totp'), TotpAction::class);

        $router->delete($container->get('auth.distotp'), TotpAction::class, 'auth.distotp');

        $router->get($container->get('auth.loginTotp'), LoginTotpAction::class, "auth.loginTotp");
        $router->post($container->get('auth.loginTotp'), LoginTotpAction::class);
    }

}
