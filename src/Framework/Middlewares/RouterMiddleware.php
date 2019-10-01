<?php

namespace Framework\Middlewares;

use Hypario\Router;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

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
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws \Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // check if the url matched a route
        $route = $this->router->match($request);
        if (is_null($route)) {
            return $handler->handle($request);
        }

        // put the parameters in the request
        $params = $route->getParams();
        foreach($params as $param => $value) {
            $request = $request->withAttribute($param, $value);
        }
        $request = $request->withAttribute(get_class($route), $route);

        // go to the next middleware
        return $handler->handle($request);
    }
}
