<?php

namespace App\AccountModule;

use App\AccountModule\Action\AccountAction;
use App\AccountModule\Action\AccountEditAction;
use Framework\Auth\LoggedInMiddleware;
use Framework\Module;
use Framework\Renderer\RendererInterface;
use Hypario\Router\Router;

class AccountModule extends Module
{

    public function __construct(Router $router, RendererInterface $renderer)
    {
        $renderer->addPath(__DIR__ . '/views', 'account');
        $router->get('/profile', [LoggedInMiddleware::class, AccountAction::class], 'profile');
        $router->post('/profile', [LoggedInMiddleware::class, AccountEditAction::class]);
    }

}
