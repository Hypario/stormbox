<?php

namespace Hypario;

use Hypario\Database\Table;
use Psr\Http\Message\ServerRequestInterface;

abstract class Actions
{

    /**
     * @var Table
     */
    protected $table;

    public function __construct(Table $table)
    {
        $this->table = $table;
    }

    abstract public function __invoke(ServerRequestInterface $request);

}
