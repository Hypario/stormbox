<?php

namespace Framework\Middlewares;

use Hypario\Router\Router;
use Psr\Http\Server\MiddlewareInterface;

class RouterMiddleware implements MiddlewareInterface
{

    /**
     * @var Router
     */
    private $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Process an incoming server request.
     *
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     */
    public function process(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Server\RequestHandlerInterface $handler): \Psr\Http\Message\ResponseInterface
    {
        // get the matched route
        $route = $this->router->match($request);
        // if no route matched, go to the next middleware
        if (is_null($route)) {
            return $handler->handle($request);
        }

        // get the params of the matched route
        $params = $route->getParams();
        foreach($params as $param => $value) {
            $request = $request->withAttribute($param, $value);
        }
        // put the route on the request
        $request = $request->withAttribute(get_class($route), $route);
        // go to the next route
        return $handler->handle($request);
    }
}
