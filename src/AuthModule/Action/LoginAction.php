<?php

namespace App\AuthModule\Action;

use Framework\Actions\ActionInterface;
use Framework\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface;

class LoginAction extends ActionInterface {

    /**
     * @var RendererInterface
     */
    private $renderer;

    public function __construct(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ServerRequestInterface $request)
    {
        return $this->renderer->render('@auth/login');
    }

    /**
     * @inheritDoc
     */
    protected function getParams(ServerRequestInterface $request): array
    {
        return [];
    }
}
