<?php

namespace Framework\TwigExtensions;

use Framework\App;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ModuleExtension extends AbstractExtension
{

    /**
     * @var App
     */
    private App $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('hasModule', [$this, 'has'])
        ];
    }

    public function has(string $name): bool
    {
        foreach ($this->app->getModules() as $module) {
            if (strpos($module, $name)) {
                return true;
            }
        }
        return false;
    }

}
