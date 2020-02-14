<?php


namespace App\AuthModule;


use App\Framework\Response\RedirectResponse;
use Framework\Auth\ForbiddenException;
use Framework\Exception\KnownException;
use Framework\Session\FlashService;
use Framework\Session\SessionInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ForbiddenMiddleware implements MiddlewareInterface
{

    /**
     * @var string
     */
    private string $loginPath;
    /**
     * @var SessionInterface
     */
    private SessionInterface $session;

    public function __construct(string $loginPath, SessionInterface $session)
    {
        $this->loginPath = $loginPath;
        $this->session = $session;
    }

    /**
     * @inheritDoc
     * @throws KnownException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (ForbiddenException $e) {
            if (strpos($request->getUri()->getPath(), '/api') == 0) {
                throw new KnownException(FORBIDDEN, "Vous devez posséder un compte pour effectuer cette action.", true);
            }
            return $this->redirectLogin($request);
        }
    }

    private function redirectLogin(ServerRequestInterface $request): ResponseInterface
    {
        $this->session->set('auth.redirect', $request->getUri()->getPath());
        (new FlashService($this->session))->error("Vous devez posséder un compte pour accéder à cette page.");
        return new RedirectResponse($this->loginPath);
    }
}
