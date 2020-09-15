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
     * @var ContainerInterface
     */
    private ContainerInterface $container;
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

    public function __construct(
        ContainerInterface $container,
        RendererInterface $renderer,
        UserTable $userTable,
        DatabaseAuth $auth,
        FlashService $flashService,
        Router $router
    )
    {
        $this->container = $container;
        $this->renderer = $renderer;
        $this->userTable = $userTable;
        $this->auth = $auth;
        $this->flashService = $flashService;
        $this->router = $router;
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

            $code = substr(md5(uniqid(rand(), true)), 16, 16);

            $userParams = [
                'username' => $params['username'],
                'email' => $params['email'],
                'password' => password_hash($params['password'], $this->container->get('password.algo')),
                'activation_code' => $code
            ];

            $this->userTable->insert($userParams);
            /** @var User $user */
            $user = Hydrator::hydrate($userParams, User::class);
            $user->id = $this->userTable->getPdo()->lastInsertId();

            $transport = (new \Swift_SmtpTransport('localhost', 1025));
            $mailer = new \Swift_Mailer($transport);

            $message = (new \Swift_Message("Activate your Stormbox account"))
                ->setFrom(["noreply@stormbox.com" => "Stormbox"])
                ->setTo([$user->email => $user->username])
                ->setBody("Activate your account now ! click the link here : " . $this->container->get('domain') . $this->router->getPath("auth.activate", ["code" => $code]));

            $mailer->send($message);

            $this->flashService->success("Votre compte a bien été créée, un email vous a été envoyé afin d'activer votre compte.");
            return new RedirectResponse('/');
        }
        $errors = $validator->getErrors();
        return $this->renderer->render('@auth/register', [
            'errors' => $errors,
            'user' => [
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
