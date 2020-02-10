<?php

namespace Framework\Renderer;

class PHPRenderer implements RendererInterface
{

    /**
     * @var string
     */
    private $defaultPath;

    /**
     * @var string[string]
     */
    private $namespacedPaths = [];

    /**
     * @var array
     */
    private $globals = [];

    public function __construct(?string $defaultPath = null)
    {
        if(!is_null($defaultPath)) {
            $this->addPath($defaultPath);
        }
    }

    /**
     * Add a path to a dir of view
     *
     * @param string $path
     * @param $namespace
     * @return mixed
     */
    public function addPath(string $path, $namespace = null)
    {
        if (is_null($namespace)) {
            $this->defaultPath = $path;
        } else {
            $this->namespacedPaths[$namespace] = $path;
        }
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
     */
    public function render(string $view, array $params = []): string
    {
        if ($this->hasNamespace($view)) {
            $path = $this->replaceNamespace($view) . '.php';
        } else {
            $path = $this->defaultPath . DIRECTORY_SEPARATOR . $view . '.php';
        }
        ob_start();
        $renderer = $this;
        extract($this->globals);
        extract($params);
        require($path);
        return ob_get_clean();
    }

    /**
     * Allow to add a global variable to all views
     * @param string $key
     * @param $value
     */
    public function addGlobal(string $key, $value): void
    {
        $this->globals[$key] = $value;
    }

    private function hasNamespace(string $view): bool
    {
        return $view[0] == '@';
    }

    private function getNamespace(string $view): string
    {
        return substr($view, 1, strpos($view, '/') -1 );
    }

    private function replaceNamespace(string $view): string
    {
        $namespace = $this->getNamespace($view);
        return str_replace('@' . $namespace, $this->namespacedPaths[$namespace], $view);
    }
}
