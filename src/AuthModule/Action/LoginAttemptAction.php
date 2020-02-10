<?php


namespace App\AuthModule\Action;


use App\AuthModule\DatabaseAuth;
use App\Framework\Response\RedirectResponse;
use Framework\Actions\ActionInterface;
use Framework\Actions\RouterAwareAction;
use Framework\Session\FlashService;
use Framework\Session\SessionInterface;
use Framework\Validator\Validator;
use Hypario\Router\Router;
use Psr\Http\Message\ServerRequestInterface;

class LoginAttemptAction extends ActionInterface
{

    protected $params = ["username", 'password'];

    /**
     * @var DatabaseAuth
     */
    private $auth;
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
        if (!$this->getValidator($request)->isValid()) {
            $user = $this->auth->login($params['username'], $params['password']);
            if ($user) {
                $path = $this->session->get('auth.redirect') ?: $this->router->getPath('/');
                $this->session->delete('auth.redirect');
                return new RedirectResponse($path);
            }
        }
        (new FlashService($this->session))->error("Identifiant ou mot de passe incorrect");
        return $this->redirect('auth.login');
    }

    /**
     * @inheritDoc
     */
    protected function getParams(ServerRequestInterface $request): array
    {
        return array_filter($request->getParsedBody(), fn ($key) => in_array($key, $this->params), ARRAY_FILTER_USE_KEY);
    }

    protected function getValidator(ServerRequestInterface $request): Validator
    {
        return parent::getValidator($request)
            ->required('username')
            ->required('password');
    }
}
