<?php


namespace App\AuthModule\Action;


use App\AuthModule\DatabaseAuth;
use App\AuthModule\User;
use App\AuthModule\UserTable;
use App\Framework\Response\RedirectResponse;
use Framework\Actions\Action;
use Framework\Auth\Auth;
use Framework\Exception\KnownException;
use Framework\Exception\NoRecordException;
use Framework\Session\FlashService;
use Hypario\Router\Router;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ActivateAction extends Action
{

    /**
     * @var UserTable
     */
    private UserTable $userTable;
    /**
     * @var FlashService
     */
    private FlashService $flashService;
    /**
     * @var DatabaseAuth
     */
    private DatabaseAuth $auth;
    /**
     * @var Router
     */
    private Router $router;

    public function __construct(UserTable $userTable, FlashService $flashService, DatabaseAuth $auth, Router $router)
    {
        $this->userTable = $userTable;
        $this->flashService = $flashService;
        $this->auth = $auth;
        $this->router = $router;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ServerRequestInterface $request)
    {
        try {
            /** @var User $user */
            $user = $this->userTable->findBy("activation_code", $request->getAttribute("code"));
            $this->userTable->update($user->id, [
                "validated" => true,
                "activation_code" => ""
            ]);
            $this->flashService->success("Votre compte a été activé, vous avez été automatiquement connecté.");
            $this->auth->setUser($user);
            return new RedirectResponse($this->router->getPath('index'));
        } catch (NoRecordException $e) {
            throw new KnownException(NOT_FOUND);
        }
    }
}
