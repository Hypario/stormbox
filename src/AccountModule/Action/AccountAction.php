<?php

namespace App\AccountModule\Action;

use Framework\Actions\Action;
use Framework\Auth\Auth;
use Framework\Exception\KnownException;
use Framework\Renderer\RendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AccountAction extends Action
{

    /**
     * @var RendererInterface
     */
    private RendererInterface $renderer;
    /**
     * @var Auth
     */
    private Auth $auth;

    public function __construct(RendererInterface $renderer, Auth $auth)
    {
        $this->renderer = $renderer;
        $this->auth = $auth;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ServerRequestInterface $request)
    {
        $user = $this->auth->getUser();
        return $this->renderer->render('@account/account', compact('user'));
    }
}
