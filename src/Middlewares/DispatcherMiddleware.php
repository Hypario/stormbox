<?php

namespace Hypario\Middlewares;

use GuzzleHttp\Psr7\Response;
use Hypario\Route;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DispatcherMiddleware implements MiddlewareInterface
{

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // get the route from the RouterMiddleware
        $route = $request->getAttribute(Route::class);
        if (is_null($route)) {
            return $handler->handle($request);
        }

        // get the handler from the route
        $callback = $this->container->get($route->getHandler());
        // callback cannot be null
        if (is_callable($callback)) {
            $response = $callback($request);
            if (is_string($response)) {
                return new Response(200, [], $response);
            }
            return $response;
        }
        return $handler->handle($request);
    }
}
