<?php

namespace App\AccountModule\Action;

use App\AuthModule\TotpBackupGenerator;
use Framework\Actions\Action;
use Framework\Auth\Auth;
use Framework\Renderer\RendererInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;

class TotpBackupAction extends Action
{

    /**
     * @var TotpBackupGenerator
     */
    private TotpBackupGenerator $backupGenerator;
    /**
     * @var Auth
     */
    private Auth $auth;

    /**
     * @var RendererInterface
     */
    private RendererInterface $renderer;

    public function __construct(TotpBackupGenerator $backupGenerator, Auth $auth, RendererInterface $renderer)
    {
        $this->backupGenerator = $backupGenerator;
        $this->auth = $auth;
        $this->renderer = $renderer;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ServerRequestInterface $request)
    {
        $user = $this->auth->getUser();

        $codes = $this->backupGenerator->generateBackupCodes($user->id);

        return $this->renderer->render('@account/totp_codes', compact('codes'));
    }
}
