<?php


namespace App\AccountModule\Action;


use App\AuthModule\UserTable;
use App\Framework\Response\RedirectResponse;
use Framework\Actions\Action;
use Framework\Auth\Auth;
use Framework\Exception\KnownException;
use Framework\Renderer\RendererInterface;
use Framework\Session\FlashService;
use Framework\Validator\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AccountEditAction extends Action
{

    protected $params = ['email', 'password', 'password_confirm'];

    /**
     * @var Auth
     */
    private Auth $auth;
    /**
     * @var UserTable
     */
    private UserTable $userTable;
    /**
     * @var FlashService
     */
    private FlashService $flash;
    /**
     * @var RendererInterface
     */
    private RendererInterface $renderer;

    public function __construct(Auth $auth, UserTable $userTable, FlashService $flash, RendererInterface $renderer)
    {
        $this->auth = $auth;
        $this->userTable = $userTable;
        $this->flash = $flash;
        $this->renderer = $renderer;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ServerRequestInterface $request)
    {
        $user = $this->auth->getUser();
        $params = $this->getParams($request);
        $validator = $this->getValidator($params);

        if ($validator->isValid()) {
            $userParams = [
                'password' => password_hash($params['password'], PASSWORD_ARGON2ID)
            ];

            $this->userTable->update($user->id, $userParams);

            $this->flash->success("Votre compte a bien été mis à jour");
            return new RedirectResponse($request->getUri()->getPath());
        }
        $errors = $validator->getErrors();
        return $this->renderer->render('@account/account', compact('user', 'errors'));
    }

    protected function getValidator(array $params): Validator
    {
        return parent::getValidator($params)
            ->email('email')
            ->confirm('password');
    }
}
