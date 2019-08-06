<?php


namespace Hypario\Renderer;


class PHPRenderer implements RendererInterface
{

    const DEFAULT_NAMESPACE = '__MAIN';

    /**
     * Enregistre tout les chemins vers les vues par Namespace
     * la clé est le namespace et la valeur est le chemin vers la vue
     * @var array
     */
    private $paths = [];

    /**
     * Variable gloablement accessible pour toutes les vues
     * @var array
     */
    private $globals = [];

    public function __construct(?string $defaultPath = null)
    {
        if (!is_null($defaultPath)) {
            $this->addPath($defaultPath);
        }
    }

    /**
     * Permet de rajouter un chemin pour changer les vues
     *
     * @param string $namespace
     * @param null|string $path
     */
    public function addPath(string $namespace, ?string $path = null): void
    {
        if (is_null($path)) {
            $this->paths[self::DEFAULT_NAMESPACE] = $namespace;
        } else {
            $this->paths[$namespace] = $path;
        }
    }

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
    public function render(string $view, array $params = []): string
    {
        if ($this->hasNamespace($view)) {
            $path = $this->replaceNamespace($view) . '.php';
        } else {
            $path = $this->paths[self::DEFAULT_NAMESPACE] . DIRECTORY_SEPARATOR . $view . '.php';
        }
        ob_start();
        $renderer = $this;
        extract($this->globals);
        extract($params);
        require($path);
        return ob_get_clean();
    }

    /**
     * Permet de rajouter une variable globale à toute les vues
     *
     * @param string $key
     * @param mixed $value
     */
    public function addGlobal(string $key, $value): void
    {
        $this->globals[$key] = $value;
    }

    private function hasNamespace(string $view): bool
    {
        return $view[0] === '@';
    }

    private function getNamespace(string $view): string
    {
        return substr($view, 1, strpos($view, '/') - 1);
    }

    private function replaceNamespace(string $view): string
    {
        $namespace = $this->getNamespace($view);
        return str_replace('@' . $namespace, $this->paths[$namespace], $view);
    }
}
