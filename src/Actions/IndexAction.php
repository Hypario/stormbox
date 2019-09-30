<?php

namespace Hypario\Actions;

use Hypario\ActionInterface;
use Hypario\Database\Table;
use Hypario\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface;

class IndexAction extends ActionInterface
{

    /**
     * @var RendererInterface
     */
    private $renderer;

    /**
     * @var Table
     */
    private $table;

    public function __construct(RendererInterface $renderer, Table $table)
    {
        $this->renderer = $renderer;
        $this->table = $table;
    }

    public function __invoke(ServerRequestInterface $request): string
    {
        return $this->renderer->render('index');
    }

    /**
     * Filter the parameters passed
     * @param ServerRequestInterface $request
     * @return array
     */
    protected function getParams(ServerRequestInterface $request): array
    {
        return [];
    }
}
