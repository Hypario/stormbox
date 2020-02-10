<?php

use App\ApiModule\ApiModule;
use App\WebModule\WebModule;
use Framework\App;
use Framework\Middlewares\{CsrfMiddleware,
    DispatcherMiddleware,
    ExceptionHandlerMiddleware,
    MethodMiddleware,
    RouterMiddleware,
    NotFoundMiddleware};
use GuzzleHttp\Psr7\ServerRequest;
use function Http\Response\send;
use Middlewares\Whoops;

define('ROOT', dirname(__DIR__));

require ROOT . '/vendor/autoload.php';

$app = new App(ROOT . '/config/config.php');

$app
    ->addModule(WebModule::class)
    ->addModule(ApiModule::class);

$app
    ->pipe(
        $app->getContainer()->get('env') === 'dev' ?
            Whoops::class :
            ExceptionHandlerMiddleware::class
    )
    ->pipe(MethodMiddleware::class)
    ->pipe(CsrfMiddleware::class)
    ->pipe(RouterMiddleware::class)
    ->pipe(DispatcherMiddleware::class)
    ->pipe(NotFoundMiddleware::class);

require ROOT . '/config/errors.php';

if (php_sapi_name() !== 'cli') {
    $response = $app->handle(ServerRequest::fromGlobals());
    send($response);
}
