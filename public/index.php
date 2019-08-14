<?php

use Hypario\App;
use Hypario\Middlewares\DispatcherMiddleware;
use Hypario\Middlewares\ExceptionHandlerMiddleware;
use Hypario\Middlewares\NotFoundMiddleware;
use Hypario\Middlewares\RouterMiddleware;
use Middlewares\Whoops;

use function Http\Response\send;

define('ROOT', dirname(__DIR__));

require ROOT . '/vendor/autoload.php';

$app = new App(ROOT . '/config/config.php');

require ROOT . '/config/errors.php';
require ROOT . '/config/routes.php';

$app
    ->pipe(Whoops::class)
    ->pipe(ExceptionHandlerMiddleware::class)
    ->pipe(RouterMiddleware::class)
    ->pipe(DispatcherMiddleware::class)
    ->pipe(NotFoundMiddleware::class);

if (php_sapi_name() !== "cli") {
    $response = $app->run(\GuzzleHttp\Psr7\ServerRequest::fromGlobals());
    send($response);
}
