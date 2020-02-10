<?php

use Framework\Database\DatabaseFactory;
use Framework\Middlewares\CsrfMiddleware;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Framework\Renderer\{RendererInterface, TwigRendererFactory};
use Framework\Session\{PHPSession, SessionInterface};
use Framework\TwigExtensions\RouterTwigExtension;
use Framework\TwigExtensions\CsrfExtension;

use function Hypario\{
    factory, object
};

return [

    'env' => 'dev',
    'views.path' => ROOT . '/views',

    'database.host' => 'localhost',
    'database.username' => 'root',
    'database.password' => 'root',
    'database.name' => 'root',
    'database.schema' => 'Framework',

    'twig.extensions' => [
        RouterTwigExtension::class,
        CsrfExtension::class
    ],

    RendererInterface::class => factory(TwigRendererFactory::class),

    SessionInterface::class => PHPSession::class,
    CsrfMiddleware::class => object()->constructor(SessionInterface::class),

    Filesystem::class => function () {
        $adapter = new Local(ROOT . '/uploads');
        return new Filesystem($adapter);
    },

    PDO::class => factory(DatabaseFactory::class),

];
