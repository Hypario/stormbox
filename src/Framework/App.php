<?php

namespace Framework;

use DI\ContainerBuilder;
use Framework\Middlewares\CombinedMiddleware;
use Framework\Middlewares\RoutePrefixedMiddleware;
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

    private $modules = [];

    public function __construct(string $definition)
    {
        $this->definition = $definition;
    }

    /**
     * @param string|Module $module
     * @return App
     */
    public function addModule($module): self
    {
        $this->modules[] = $module;
        return $this;
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
        foreach ($this->getModules() as $module) {
            $this->getContainer()->get($module);
        }

        $middleware = new CombinedMiddleware($this->getContainer(), $this->getMiddlewares());
        return $middleware->process($request, $this);
    }

    /**
     * @return string[] | Module[]
     */
    public function getModules(): array
    {
        return $this->modules;
    }

    /**
     * @return MiddlewareInterface[]
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

}
