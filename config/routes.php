<?php

use Hypario\Actions\FileAction;
use Hypario\Actions\UploadAction;
use Hypario\Actions\DownloadApiAction;
use Hypario\Actions\IndexAction;
use Hypario\Router;

// defines the routes
$router = $app->getContainer()->get(Router::class);
$router->get('/', IndexAction::class, 'index');
$router->post('/api/upload', UploadAction::class);
$router->post('/api/download', DownloadApiAction::class);
$router->get('/api/files', FileAction::class);
