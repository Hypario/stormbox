<?php

namespace App\WebModule\Actions;

use App\Framework\Response\RedirectResponse;
use Framework\Actions\Action;
use Framework\Auth\Auth;
use Framework\Renderer\RendererInterface;
use Framework\Session\SessionInterface;
use Psr\Http\Message\ServerRequestInterface;

class IndexAction extends Action
{

    /**
     * @var RendererInterface
     */
    private $renderer;
    /**
     * @var SessionInterface
     */
    private SessionInterface $session;
    /**
     * @var Auth
     */
    private Auth $auth;

    public function __construct(RendererInterface $renderer, Auth $auth)
    {
        $this->renderer = $renderer;
        $this->auth = $auth;
    }

    public function __invoke(ServerRequestInterface $request): string
    {
        if ($this->auth->getUser()) {
            return $this->renderer->render('@web/upload');
        }
        return $this->renderer->render('@web/index');
    }
}
