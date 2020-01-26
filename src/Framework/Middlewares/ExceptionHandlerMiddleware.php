<?php

namespace Framework\Middlewares;

use Framework\Exception\KnownException;
use Framework\Exception\KnownExceptionResolver;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ExceptionHandlerMiddleware implements MiddlewareInterface
{

    /**
     * @var KnownExceptionResolver
     */
    private $resolver;

    public function __construct(KnownExceptionResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * Process an incoming server request.
     *
     * Processes an incoming server request in order to produce a response.
     * If unable to produce the response itself, it may delegate to the provided
     * request handler to do so.
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (KnownException $e) {
            return new Response($e->getCode() === 0 ? 200 : 500, [], "<h1>" . $this->resolver->getMessage($e->getCode()) . "</h1>");
        }
    }
}
