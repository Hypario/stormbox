<?php

namespace App\ApiModule;


use App\ApiModule\Actions\DownloadApiAction;
use App\ApiModule\Actions\FileApiAction;
use App\ApiModule\Actions\UploadApiAction;
use Framework\Auth\LoggedInMiddleware;
use Framework\Module;
use Hypario\Router\Router;
use Psr\Container\ContainerInterface;

class ApiModule extends Module
{

    public function __construct(ContainerInterface $container)
    {
        $router = $container->get(Router::class);
        $router->post('/api/upload', [LoggedInMiddleware::class, UploadApiAction::class]);
        $router->post('/api/download', DownloadApiAction::class);
        $router->post('/api/files', FileApiAction::class);
    }

}
