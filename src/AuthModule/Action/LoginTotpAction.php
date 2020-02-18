<?php

namespace App\AuthModule\Action;

use App\AuthModule\DatabaseAuth;
use App\AuthModule\UserTable;
use App\Framework\Response\RedirectResponse;
use Framework\Actions\Action;
use Framework\Exception\KnownException;
use Framework\Renderer\RendererInterface;
use Framework\Session\FlashService;
use Framework\Session\SessionInterface;
use Hypario\Router\Router;
use Otp\Otp;
use ParagonIE\ConstantTime\Encoding;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class LoginTotpAction extends Action
{

    protected $params = ["code"];

    /**
     * @var SessionInterface
     */
    private SessionInterface $session;
    /**
     * @var Router
     */
    private Router $router;
    /**
     * @var RendererInterface
     */
    private RendererInterface $renderer;
    /**
     * @var UserTable
     */
    private UserTable $userTable;
    /**
     * @var DatabaseAuth
     */
    private DatabaseAuth $auth;
    /**
     * @var FlashService
     */
    private FlashService $flash;

    public function __construct(
        SessionInterface $session,
        Router $router,
        RendererInterface $renderer,
        UserTable $userTable,
        DatabaseAuth $auth,
        FlashService $flash
    )
    {
        $this->session = $session;
        $this->router = $router;
        $this->renderer = $renderer;
        $this->userTable = $userTable;
        $this->auth = $auth;
        $this->flash = $flash;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ServerRequestInterface $request)
    {
        if (!$this->session->get('user_id')) {
            return new RedirectResponse($this->router->getPath('auth.login'));
        }

        if ($request->getMethod() === "POST") {
            $otp = new Otp();
            $params = $this->getParams($request);

            $user = $this->userTable->find($this->session->get('user_id'));

            if ($otp->checkTotp(Encoding::base32DecodeUpper($user->totpKey), $params['code'])) {
                $this->auth->setUser($user);
                $this->flash->success("Vous êtes maintenant connecté.");
                return new RedirectResponse("/");
            } else {
                $this->flash->error("Code incorrect");
                return new RedirectResponse($this->router->getPath("auth.loginTotp"));
            }
        }
        return $this->renderer->render('@auth/login_totp');
    }
}
