<?php

namespace Hypario\Middlewares;

use GuzzleHttp\Psr7\Response;
use Hypario\ActionInterface;
use Hypario\KnownException;
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
     * @throws KnownException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route = $request->getAttribute(Route::class);
        if (is_null($route)) {
            return $handler->handle($request);
        }

        $callback = $this->container->get($route->getHandler());
        if (is_null($callback)) {
            return $handler->handle($request);
        } elseif (is_callable($callback)) {
            /**
             * @var ActionInterface $callback;
             */
            $response = $callback($request);
            if (is_string($response)) {
                return new Response(200, [], $response);
            }
            return $response;
        }
        return $handler->handle($request);
    }
}
