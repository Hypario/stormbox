<?php

use Hypario\App;
use Hypario\Middlewares\DispatcherMiddleware;
use Hypario\Middlewares\NotFoundMiddleware;
use Hypario\Middlewares\RouterMiddleware;
use Middlewares\Whoops;

// pratique foireuse, tu risque d'exposer l'arborescence entière de ton site,
// même dans le cas où ../ est "protégé"
chdir(dirname(__DIR__));
// fais un define du dossier parent, et utilise-le comme préfixe pour ton
// require et tout le reste

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
