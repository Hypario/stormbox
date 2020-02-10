<?php

namespace Framework\Actions;

use GuzzleHttp\Psr7\Response;
use Hypario\Router\Router;
use Psr\Http\Message\ResponseInterface;

/**
 * Rajoute des méthodes liée à l'utilisation du Router
 *
 * Trait RouterAwareAction
 * @package Framework\Actions
 */
trait RouterAwareAction
{
    /**
     * Renvoie une réponse de redirection
     *
     * @param string $path
     * @param array $params
     * @return ResponseInterface
     */
    public function redirect(string $path, array $params = []): ResponseInterface
    {
        $redirectUri = $this->router->getPath($path, $params);
        return (new Response())
            ->withStatus(301)
            ->withHeader('Location', $redirectUri);
    }
}
