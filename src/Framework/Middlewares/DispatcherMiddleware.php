<?php

namespace Framework\Middlewares;

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
        // get the matched route from the RouterMiddleware
        $route = $request->getAttribute(Route::class);
        if (is_null($route)) {
            return $handler->handle($request);
        }

        // get the handler from the route
        $callback = $route->getHandler();
        if (!is_array($callback)) {
            $callback = [$callback];
        }

        // go throught all the middlewares specified in the route
        // and the module at the end
        return (new CombinedMiddleware($this->container, $callback))->process($request, $handler);
    }
}
