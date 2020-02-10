<?php

namespace Framework\Renderer;

interface RendererInterface
{

    /**
     * Add a path to a dir of view
     *
     * @param string $path
     * @param $namespace
     */
    public function addPath(string $path, $namespace = null);

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
    public function render(string $view, array $params = []): string;

    /**
     * Allow to add a global variable to all views
     * @param string $key
     * @param $value
     */
    public function addGlobal(string $key, $value): void;

}
