<?php

namespace App\AuthModule\Action;

use App\AuthModule\DatabaseAuth;
use App\AuthModule\TotpBackupGenerator;
use App\AuthModule\TotpBackupTable;
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
     * Where to redirect when success
     * @var string
     */
    private string $redirectTo = "/";

    /**
     * Where to redirect when Totp is checked
     * @var string
     */
    private string $redirectCheckTotp = "/totp/backup";

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
    /**
     * @var TotpBackupGenerator
     */
    private TotpBackupGenerator $backupGenerator;
    /**
     * @var TotpBackupTable
     */
    private TotpBackupTable $backupTable;

    public function __construct(
        RendererInterface $renderer,
        SessionInterface $session,
        FlashService $flash,
        DatabaseAuth $auth,
        UserTable $userTable,
        TotpBackupGenerator $backupGenerator,
        TotpBackupTable $backupTable
    )
    {
        $this->renderer = $renderer;
        $this->session = $session;
        $this->flash = $flash;
        $this->auth = $auth;
        $this->userTable = $userTable;
        $this->backupGenerator = $backupGenerator;
        $this->backupTable = $backupTable;
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

            // delete all backup code
            $backup = $this->backupTable->findAllBy('user_id', $user->id)->getAll();

            if (!empty($backup)) {
                foreach ($backup as $code) {
                    $this->backupTable->delete($code->id);
                }
            }

            $this->flash->success("L'authentification à double facteur a bien été désactivée.");
            return new RedirectResponse($this->redirectTo);
        }
        $this->flash->error("Vous devez être connecté pour désactiver l'authentification à double facteur.");
        return new RedirectResponse($this->redirectTo);
    }

    /**
     * @param ServerRequestInterface $request
     * @return RedirectResponse|string
     */
    private function checkTotp(ServerRequestInterface $request)
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
            return new RedirectResponse($this->redirectCheckTotp);
        } else {
            $this->flash->error("Le code ne correspond pas.");
            return new RedirectResponse($request->getUri()->getPath());
        }
    }
}
