<?php

namespace App\AuthModule\Action;

use App\AuthModule\DatabaseAuth;
use App\Framework\Response\RedirectResponse;
use Framework\Actions\Action;
use Framework\Auth\Auth;
use Framework\Exception\KnownException;
use Framework\Session\FlashService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class LogoutAction extends Action
{

    /**
     * @var DatabaseAuth
     */
    private DatabaseAuth $auth;
    /**
     * @var FlashService
     */
    private FlashService $flashService;

    public function __construct(DatabaseAuth $auth, FlashService $flashService)
    {
        $this->auth = $auth;
        $this->flashService = $flashService;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ServerRequestInterface $request)
    {
        $this->auth->logout();
        $this->flashService->success("Vous êtes maintenant déconnecté.");
        return new RedirectResponse("/");
    }
}
