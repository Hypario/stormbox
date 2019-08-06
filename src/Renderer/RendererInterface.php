<?php

namespace Hypario\Renderer;

interface RendererInterface
{

    /**
     * Permet de rajouter un chemin pour changer les vues
     *
     * @param string $namespace
     * @param null|string $path
     */
    public function addPath(string $namespace, ?string $path = null): void;

    /**
     * Permet de rendre une vue
     * Le chemin peut être préciser avec des namespaces rajoutés par le addPath()
     * on crée un namespace en mettant un @ devant et termine par un /
     * Exemples :
     * $this->render('@blog/index.php');
     * $this->render('view');
     *
     * @param string $view
     * @param array $params
     * @return string
     */
    public function render(string $view, array $params = []): string;

    /**
     * Permet de rajouter une variable globale à toute les vues
     *
     * @param string $key
     * @param mixed $value
     */
    public function addGlobal(string $key, $value): void;
}
