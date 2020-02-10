<?php

namespace Framework\Actions;

use Framework\Exception\KnownException;
use Framework\Validator\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class Action
{

    /**
     * Accepted params
     * @var array
     */
    protected $params = [];

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface|string
     * @throws KnownException
     */
    public abstract function __invoke(ServerRequestInterface $request);

    /**
     * Filter the params
     * @param ServerRequestInterface $request
     * @return array
     */
    public function getParams(ServerRequestInterface $request): array
    {
        return array_filter($request->getParsedBody(), fn($key) => in_array($key, $this->params), ARRAY_FILTER_USE_KEY);
    }

    /**
     * return the validator with the given rules
     * @param array $params
     * @return Validator
     */
    protected function getValidator(array $params): Validator
    {
        return new Validator($params);
    }

}
