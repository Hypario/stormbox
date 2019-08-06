<?php

namespace Hypario\Middlewares;

use Hypario\Router;
use Psr\Http\Message\ServerRequestInterface;

class RouterMiddleware
{

    /**
     * @var Router
     */
    private $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function __invoke(ServerRequestInterface $request, callable $next)
    {
        // On vérifie si la Route existe
        $route = $this->router->match($request);
        if (is_null($route)) {
            return $next($request);
        }

        // Met les paramètres de la route dans la Requete
        $params = $route->getParams();
        $request = array_reduce(array_keys($params), function (ServerRequestInterface $request, $key) use ($params) {
            return $request->withAttribute($key, $params[$key]);
        }, $request);
        $request = $request->withAttribute(get_class($route), $route);
        return $next($request);
    }
}
