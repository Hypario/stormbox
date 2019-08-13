<?php

use Hypario\Renderer\PHPRenderer;
use Hypario\Renderer\RendererInterface;
use Psr\Container\ContainerInterface;

return [

    'database.host' => 'localhost',
    'database.username' => 'root',
    'database.password' => 'root',
    'database.name' => 'stormbox',

    RendererInterface::class => \DI\create(PHPRenderer::class)->constructor(ROOT . '/views'),

    PDO::class => function (ContainerInterface $c) {
        return new PDO("mysql:host={$c->get('database.host')};dbname={$c->get('database.name')};charset=UTF8",
            $c->get('database.username'),
            $c->get('database.password'),
            [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]
        );
    },
];
