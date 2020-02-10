<?php

namespace App\WebModule\Actions;

use Framework\Actions\Action;
use Framework\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface;

class IndexAction extends Action
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
        return $this->renderer->render('@web/index');
    }
}
