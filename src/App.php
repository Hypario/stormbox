<?php

namespace Hypario;

use DI\ContainerBuilder;
use Hypario\Middlewares\RoutePrefixedMiddleware;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class App implements RequestHandlerInterface
{

    public $definition;

    /**
     * @var string[]
     */
    public $middlewares = [];

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * used to know what middleware to process
     * @var int
     */
    private $index = 0;

    public function __construct(string $definition)
    {
        $this->definition = $definition;
    }

    /**
     * @param $middleware
     * @param string|null $routePrefix
     * @return App
     */
    public function pipe($middleware, string $routePrefix = null): self
    {
        if (is_null($routePrefix)) {
            $this->middlewares[] = $middleware;
        } else {
            $this->middlewares[] = new RoutePrefixedMiddleware($this->getContainer(), $routePrefix, $middleware);
        }
        return $this;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function run(ServerRequestInterface $request): ResponseInterface
    {

        $container = $this->getContainer();

        // return the response
        return $this->handle($request);
    }

    public function getContainer(): ContainerInterface
    {
        if ($this->container === null) {
            $builder = new ContainerBuilder();
            $builder->addDefinitions($this->definition);
            $this->container = $builder->build();
        }
        return $this->container;
    }

    /**
     * Handle the request and return a response.
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $middleware = $this->getMiddleware();

        /**
         * @var MiddlewareInterface $middleware
         */
        return $middleware->process($request, $this);
    }

    /**
     * @return MiddlewareInterface
     */
    private function getMiddleware(): MiddlewareInterface
    {
        $middleware = $this->container->get($this->middlewares[$this->index]);
        ++$this->index;

        return $middleware;
    }

}
