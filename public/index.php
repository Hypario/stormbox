<?php

use Hypario\App;
use Hypario\Middlewares\DispatcherMiddleware;
use Hypario\Middlewares\NotFoundMiddleware;
use Hypario\Middlewares\RouterMiddleware;
use Middlewares\Whoops;

define('ROOT', dirname(__DIR__));

require ROOT . '/config/errors.php';

require ROOT .'/vendor/autoload.php';

$app = new App(ROOT . '/config/config.php');

$app->pipe(Whoops::class)
    ->pipe(RouterMiddleware::class)
    ->pipe(DispatcherMiddleware::class)
    ->pipe(NotFoundMiddleware::class);

if (php_sapi_name() !== "cli") {
    $response = $app->run(\GuzzleHttp\Psr7\ServerRequest::fromGlobals());
    \Http\Response\send($response);
}
