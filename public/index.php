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

/**
$router = new Router();
$renderer = new PHPRenderer('views');
$router->get('/', function() use ($renderer) {
    echo $renderer->render('index');
});

$route = $router->match($_SERVER['REQUEST_URI']);

if (!is_null($route)) {
    call_user_func_array($route->getHandler(),$route->getParams());
} else {
    throw new Exception('404 Not Found');
}
 **/
