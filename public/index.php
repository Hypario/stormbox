<?php

use App\ApiModule\ApiModule;
use Framework\App;
use Framework\Middlewares\DispatcherMiddleware;
use Framework\Middlewares\ExceptionHandlerMiddleware;
use Framework\Middlewares\NotFoundMiddleware;
use Framework\Middlewares\RouterMiddleware;
use GuzzleHttp\Psr7\ServerRequest;
use Middlewares\Whoops;

use function Http\Response\send;

define('ROOT', dirname(__DIR__));

require ROOT . '/vendor/autoload.php';

$app = new App(ROOT . '/config/config.php');

require ROOT . '/config/errors.php';

$app->addModule(ApiModule::class);

$app
    ->pipe(Whoops::class)
    ->pipe(ExceptionHandlerMiddleware::class)
    ->pipe(RouterMiddleware::class)
    ->pipe(DispatcherMiddleware::class)
    ->pipe(NotFoundMiddleware::class);

if (php_sapi_name() !== "cli") {
    $response = $app->handle(ServerRequest::fromGlobals());
    send($response);
}
