<?php

namespace App\ApiModule\Actions;

use Framework\ActionInterface;
use Framework\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface;

class IndexAction extends ActionInterface
{

    /**
     * @var RendererInterface
     */
    private $renderer;

    public function __construct(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    public function __invoke(ServerRequestInterface $request): string
    {
        return $this->renderer->render('index');
    }

    /**
     * Filter the parameters passed
     * @param ServerRequestInterface $request
     * @return array
     */
    protected function getParams(ServerRequestInterface $request): array
    {
        return [];
    }
}
