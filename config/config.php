<?php

use Hypario\Database\DatabaseFactory;
use Hypario\Renderer\PHPRenderer;
use Hypario\Renderer\RendererInterface;
use function DI\factory;

return [

    'database.host' => 'ipabdd',
    'database.username' => 'fabien.duterte',
    'database.password' => '',
    'database.name' => 'stormbox',
    'database.schema' => 'stormbox',

    RendererInterface::class => \DI\create(PHPRenderer::class)->constructor(ROOT . '/views'),

    PDO::class => factory(DatabaseFactory::class),
];
