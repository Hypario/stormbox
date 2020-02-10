<?php

namespace Framework\Renderer;

use Twig\Environment;

class TwigRenderer implements RendererInterface
{

    /**
     * @var Environment
     */
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * Add a path to a dir of view
     *
     * @param string $path
     * @param $namespace
     */
    public function addPath(string $path, $namespace = null)
    {
        $this->twig->getLoader()->addPath($path, $namespace);
    }

    /**
     * Allow to render a vue
     * The path can be precised with namespaces added via addPath()
     * We create a namespace by adding a @ in front of the string and end with a /
     * Examples :
     *
     * $this->render('@blog/index.php');
     * $this->render('view');
     *
     * @param string $view
     * @param array $params
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function render(string $view, array $params = []): string
    {
        return $this->twig->render($view . '.twig', $params);
    }

    /**
     * Allow to add a global variable to all views
     * @param string $key
     * @param $value
     */
    public function addGlobal(string $key, $value): void
    {
        $this->twig->addGlobal($key, $value);
    }
}
