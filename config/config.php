<?php

use App\AuthModule\DatabaseAuth;
use Framework\Auth\Auth;
use Framework\Cookie\{CookieInterface, PHPCookie};
use Framework\Database\DatabaseFactory;
use Framework\Middlewares\CsrfMiddleware;
use Keven\Flysystem\Concatenate\Append;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Psr\Container\ContainerInterface;
use Framework\Renderer\{RendererInterface, TwigRendererFactory};
use Framework\Session\{PHPSession, SessionInterface};
use Framework\TwigExtensions\{FlashExtension, RouterTwigExtension, CsrfExtension, FormExtension};

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
    'database.schema' => 'stormbox',

    'twig.extensions' => [
        RouterTwigExtension::class,
        FlashExtension::class,
        FormExtension::class
    ],

    RendererInterface::class => factory(TwigRendererFactory::class),

    SessionInterface::class => PHPSession::class,

    'uploadDirectory' => ROOT . "/uploads",

    'adapter' => 'local',
    Filesystem::class => function (ContainerInterface $c) {
        if ($c->get('adapter') == "local") {
            return null;
        }
        return new Filesystem($c->get('adapter'));
    },

    PDO::class => factory(DatabaseFactory::class),

    Auth::class => DatabaseAuth::class

];
