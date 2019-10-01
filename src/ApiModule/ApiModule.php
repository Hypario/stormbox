<?php

namespace App\ApiModule;


use App\ApiModule\Actions\DownloadApiAction;
use App\ApiModule\Actions\FileApiAction;
use App\ApiModule\Actions\IndexAction;
use App\ApiModule\Actions\UploadApiAction;
use Framework\Module;
use Hypario\Router;
use Psr\Container\ContainerInterface;

class ApiModule extends Module
{

    public function __construct(ContainerInterface $container)
    {
        $router = $container->get(Router::class);
        $router->get('/', IndexAction::class, 'index');
        $router->post('/api/upload', UploadApiAction::class);
        $router->post('/api/download', DownloadApiAction::class);
        $router->post('/api/files', FileApiAction::class);
    }

}
