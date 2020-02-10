<?php

namespace Framework\TwigExtensions;

use Hypario\Router\Router;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RouterTwigExtension extends AbstractExtension
{

    /**
     * @var Router
     */
    private $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('path', [$this, 'pathFor']),
            new TwigFunction('is_subpath', [$this, 'isSubPath']),
            new TwigFunction('has', [$this, 'hasRoute'])
        ];
    }

    /**
     * generate a path from given name
     *
     * @param string $name
     * @param array $params
     * @param array $queryParams
     * @return string
     * @throws \Exception
     */
    public function pathFor(string $name, array $params = [], array $queryParams = [])
    {
        return $this->router->getPath($name, $params, $queryParams);
    }

    /**
     * Check if named route exist
     *
     * @param string $name
     * @return bool
     */
    public function hasRoute(string $name) {
        return $this->router->hasRoute($name);
    }

    public function isSubPath(string $path): bool
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $expectedUri = $this->router->getPath($path);
        return strpos($uri, $expectedUri) !== false;
    }

}
