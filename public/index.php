<?php

use Hypario\App;
use Hypario\Middlewares\DispatcherMiddleware;
use Hypario\Middlewares\NotFoundMiddleware;
use Hypario\Middlewares\RouterMiddleware;
use Middlewares\Whoops;

chdir(dirname(__DIR__));

require 'vendor/autoload.php';

$app = new App('config/config.php');

$app->pipe(Whoops::class)
    ->pipe(RouterMiddleware::class)
    ->pipe(DispatcherMiddleware::class)
    ->pipe(NotFoundMiddleware::class);

if (php_sapi_name() !== "cli") {
    $response = $app->run(\GuzzleHttp\Psr7\ServerRequest::fromGlobals());
    \Http\Response\send($response);
}
