<?php


namespace App\AuthModule\Action;


use App\AuthModule\DatabaseAuth;
use App\AuthModule\User;
use App\AuthModule\UserTable;
use App\Framework\Response\RedirectResponse;
use Framework\Actions\Action;
use Framework\Database\Hydrator;
use Framework\Exception\KnownException;
use Framework\Renderer\RendererInterface;
use Framework\Session\FlashService;
use Framework\Validator\Validator;
use Hypario\Router\Router;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SignupAction extends Action
{

    protected $params = ['username', 'email', 'password', 'password_confirm'];

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
    private FlashService $flashService;
    /**
     * @var Router
     */
    private Router $router;
    /**
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    public function __construct(
        RendererInterface $renderer,
        UserTable $userTable,
        DatabaseAuth $auth,
        FlashService $flashService,
        Router $router,
        ContainerInterface $container
    )
    {
        $this->renderer = $renderer;
        $this->userTable = $userTable;
        $this->auth = $auth;
        $this->flashService = $flashService;
        $this->router = $router;
        $this->container = $container;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ServerRequestInterface $request)
    {
        if ($request->getMethod() === "GET") {
            return $this->renderer->render("@auth/register");
        }

        $params = $this->getParams($request);
        $validator = $this->getValidator($params);

        if ($validator->isValid()) {
            $userParams = [
                'username' => $params['username'],
                'email' => $params['email'],
                'password' => password_hash($params['password'], $this->container->get('password.algo'))
            ];
            $this->userTable->insert($userParams);

            $user = Hydrator::hydrate($userParams, User::class);
            $user->id = $this->userTable->getPdo()->lastInsertId();
            $this->auth->setUser($user);

            $this->flashService->success("Votre compte a bien été créée, vous êtes maintenant connecté.");
            return new RedirectResponse('/');
        }
        $errors = $validator->getErrors();
        return $this->renderer->render('@auth/register', [
            'errors' => $errors,
            'users' => [
                'username' => $params['username'],
                'email' => $params['email']
            ]
        ]);
    }

    protected function getValidator(array $params): Validator
    {
        return parent::getValidator($params)
            ->required('username', 'password', 'password_confirm')
            ->length('username', 5)
            ->email('email')
            ->confirm('password')
            ->length('password', 6)
            ->unique('username', $this->userTable)
            ->unique('email', $this->userTable);
    }
}
