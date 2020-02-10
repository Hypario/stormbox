<?php

namespace App\WebModule;

use App\WebModule\Actions\IndexAction;
use Framework\Module;
use Framework\Renderer\RendererInterface;
use Hypario\Router\Router;

class WebModule extends Module
{

    public function __construct(RendererInterface $renderer, Router $router)
    {
        $renderer->addPath(__DIR__ . '/views', 'web');
        $router->get('/', IndexAction::class);
    }

}
