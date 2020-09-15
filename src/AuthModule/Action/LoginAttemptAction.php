<?php


namespace App\AuthModule\Action;


use App\AuthModule\DatabaseAuth;
use App\AuthModule\User;
use App\Framework\Response\RedirectResponse;
use Framework\Actions\{Action, RouterAwareAction};
use Framework\Session\{FlashService, SessionInterface};
use Framework\Validator\Validator;
use Hypario\Router\Router;
use Psr\Http\Message\ServerRequestInterface;

class LoginAttemptAction extends Action
{

    protected $params = ["username", 'password'];

    /**
     * @var DatabaseAuth
     */
    private DatabaseAuth $auth;
    /**
     * @var SessionInterface
     */
    private SessionInterface $session;
    /**
     * @var Router
     */
    private Router $router;

    use RouterAwareAction;

    public function __construct(DatabaseAuth $auth, SessionInterface $session, Router $router)
    {
        $this->auth = $auth;
        $this->session = $session;
        $this->router = $router;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ServerRequestInterface $request)
    {
        $params = $this->getParams($request);
        if ($this->getValidator($params)->isValid()) {
            /** @var User $user */
            $user = $this->auth->login($params['username'], $params['password']);
            if ($user && $user->validated) {
                if (!is_null($user->totpKey)) {
                    $this->session->set('user_id', $user->id);
                    return new RedirectResponse($this->router->getPath('auth.loginTotp'));
                } else {
                    $this->auth->setUser($user);
                    $path = $this->session->get('auth.redirect') ?: $this->router->getPath('index');
                    $this->session->delete('auth.redirect');
                    (new FlashService($this->session))->success("Vous êtes maintenant connecté.");
                    return new RedirectResponse($path);
                }
            }
        }
        (new FlashService($this->session))->error("Identifiant ou mot de passe incorrect.");
        return $this->redirect('auth.login');
    }

    protected function getValidator(array $params): Validator
    {
        return parent::getValidator($params)
            ->required('username')
            ->required('password');
    }
}
