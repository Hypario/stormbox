<?php

namespace Hypario\Middlewares;

use GuzzleHttp\Psr7\Response;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Used for routes to have multiples Middleware
 * Class CombinedMiddleware
 * @package Framework\Middleware
 */
class CombinedMiddlewareHandler implements RequestHandlerInterface
{

    /**
     * @var string[]
     */
    private $middlewares = [];

    /**
     * @var int
     */
    private $index = 0;
    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var RequestHandlerInterface
     */
    private $handler;

    public function __construct(ContainerInterface $container, array $middlewares, RequestHandlerInterface $handler)
    {
        $this->middlewares = $middlewares;
        $this->container = $container;
        $this->handler = $handler;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $middleware = $this->getMiddleware();
        if (is_null($middleware)) {
            return $this->handler->handle($request);
        } elseif (is_callable($middleware)) {
            $response = call_user_func_array($middleware, [$request, [$this, 'handle']]);
            if (is_string($response)) {
                return new Response(200, [], $response);
            }
            return $response;
        } elseif ($middleware instanceof MiddlewareInterface) {
            return $middleware->process($request, $this);
        }
    }

    /**
     * @return object
     */
    private function getMiddleware()
    {
        if (array_key_exists($this->index, $this->middlewares)) {
            if (is_string($this->middlewares[$this->index])) {
                $middleware = $this->container->get($this->middlewares[$this->index]);
            } else {
                $middleware = $this->middlewares[$this->index];
            }
            $this->index++;
            return $middleware;
        }
        return null;
    }
}
