<?php

namespace Hypario\Actions;

use Hypario\Database\NoRecordException;
use Hypario\Database\Table;
use Hypario\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface;

class IndexAction
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

}
