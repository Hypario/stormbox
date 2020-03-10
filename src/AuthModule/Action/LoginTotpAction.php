<?php

namespace App\AuthModule\Action;

use App\AuthModule\DatabaseAuth;
use App\AuthModule\TotpBackupTable;
use App\AuthModule\UserTable;
use App\Framework\Response\RedirectResponse;
use Framework\Actions\Action;
use Framework\Auth\User;
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
    /**
     * @var TotpBackupTable
     */
    private TotpBackupTable $backupTable;

    public function __construct(
        SessionInterface $session,
        Router $router,
        RendererInterface $renderer,
        UserTable $userTable,
        DatabaseAuth $auth,
        FlashService $flash,
        TotpBackupTable $backupTable
    )
    {
        $this->session = $session;
        $this->router = $router;
        $this->renderer = $renderer;
        $this->userTable = $userTable;
        $this->auth = $auth;
        $this->flash = $flash;
        $this->backupTable = $backupTable;
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
                return $this->login($user);
            } elseif ($this->checkBackupCode($params['code'], $user->id)) {
                return $this->login($user);
            } else {
                $this->flash->error("Code incorrect");
                return new RedirectResponse($this->router->getPath("auth.loginTotp"));
            }
        }
        return $this->renderer->render('@auth/login_totp');
    }

    private function login(User $user): ResponseInterface
    {
        $this->auth->setUser($user);
        $this->flash->success("Vous êtes maintenant connecté.");
        return new RedirectResponse("/");
    }

    private function checkBackupCode(string $code, int $userId): bool
    {
        $backupCodes = $this->backupTable->findAllBy('user_id', $userId);

        foreach ($backupCodes->getAll() as $backupCode) {
            if ($backupCode->hash === hash("sha256", $code)) {
                $this->backupTable->delete($backupCode->id);
                return true;
            }
        }
        return false;
    }
}
