<?php

use Hypario\Database\DatabaseFactory;
use Hypario\Renderer\PHPRenderer;
use Hypario\Renderer\RendererInterface;
use function DI\factory;

return [

    'database.host' => 'localhost',
    'database.username' => 'root',
    'database.password' => 'root',
    'database.name' => 'root',
    'database.schema' => 'stormbox',

    RendererInterface::class => \DI\create(PHPRenderer::class)->constructor(ROOT . '/views'),

    PDO::class => factory(DatabaseFactory::class),
];
