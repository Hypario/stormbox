<?php

namespace Hypario;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ActionInterface
{

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface|string
     * @throws KnownException
     */
    public function __invoke(ServerRequestInterface $request);

}
