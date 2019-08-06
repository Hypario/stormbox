<?php

namespace Hypario;

use DI\ContainerBuilder;
use Hypario\Actions\ApiAction;
use Hypario\Actions\DownloadApiAction;
use Hypario\Actions\IndexAction;
use Hypario\Middlewares\CombinedMiddleware;
use Hypario\Middlewares\RoutePrefixedMiddleware;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
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

    public function __construct(string $definition)
    {
        $this->definition = $definition;
    }

    public function pipe(string $routePrefix, $middleware = null): self
    {
        if (is_null($middleware)) {
            $this->middlewares[] = $routePrefix;
        } else {
            $this->middlewares[] = new RoutePrefixedMiddleware($this->getContainer(), $routePrefix, $middleware);
        }
        return $this;
    }

    public function run(ServerRequestInterface $request): ResponseInterface
    {
        $container = $this->getContainer();
        $router = $container->get(Router::class);
        $router->get('/', IndexAction::class, 'index');
        $router->post('/api', ApiAction::class);
        $router->post('/api/download', DownloadApiAction::class);
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
        $middleware = new CombinedMiddleware($this->getContainer(), $this->middlewares);
        return $middleware->process($request, $this);
    }
}
