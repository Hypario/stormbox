<?php

namespace App\AuthModule\Action;

use App\AuthModule\DatabaseAuth;
use App\AuthModule\UserTable;
use App\Framework\Response\RedirectResponse;
use Framework\Actions\Action;
use Framework\Renderer\RendererInterface;
use Framework\Session\FlashService;
use Framework\Session\SessionInterface;
use Otp\GoogleAuthenticator;
use Otp\Otp;
use ParagonIE\ConstantTime\Encoding;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class TotpAction extends Action
{

    protected $params = ['code'];

    /**
     * @var RendererInterface
     */
    private RendererInterface $renderer;
    /**
     * @var SessionInterface
     */
    private SessionInterface $session;
    /**
     * @var FlashService
     */
    private FlashService $flash;
    /**
     * @var DatabaseAuth
     */
    private DatabaseAuth $auth;
    /**
     * @var UserTable
     */
    private UserTable $userTable;

    public function __construct(
        RendererInterface $renderer,
        SessionInterface $session,
        FlashService $flash,
        DatabaseAuth $auth,
        UserTable $userTable
    )
    {
        $this->renderer = $renderer;
        $this->session = $session;
        $this->flash = $flash;
        $this->auth = $auth;
        $this->userTable = $userTable;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ServerRequestInterface $request)
    {

        if ($request->getMethod() === "POST") {
            return $this->checkTotp($request);
        } else if ($request->getMethod() === "DELETE") {
            return $this->disableTotp();
        } else {
            return $this->enableTotp();
        }
    }

    private function enableTotp()
    {
        $secret = GoogleAuthenticator::generateRandom();

        $this->session->set('secret', $secret);
        $QrCodeUrl = GoogleAuthenticator::getQrCodeUrl('totp', "Stormbox", $secret);

        return $this->renderer->render('@auth/totp', compact('QrCodeUrl'));
    }

    private function disableTotp()
    {
        if ($user = $this->auth->getUser()) {
            $this->userTable->update($user->id, ['totp_key' => null]);

            $this->flash->success("L'authentification à double facteur a bien été désactivée.");
            return new RedirectResponse("/");
        }
        $this->flash->error("Vous devez être connecté pour désactiver l'authentification à double facteur.");
        return new RedirectResponse("/");
    }

    private function checkTotp(ServerRequestInterface $request): ResponseInterface
    {
        $otp = new Otp();
        $params = $this->getParams($request);
        $secret = $this->session->get('secret');
        if ($otp->checkTotp(Encoding::base32DecodeUpper($secret), $params['code'])) {
            // update user TOTP key
            $user = $this->auth->getUser();
            $user->totpKey = $secret;
            $this->userTable->update($user->id, ['totp_key' => $secret]);

            $this->session->delete('secret');

            $this->flash->success("L'authentification à double facteur a bien été activée.");
            return new RedirectResponse("/");
        } else {
            $this->flash->error("Le code ne correspond pas.");
            return new RedirectResponse($request->getUri()->getPath());
        }
    }
}
