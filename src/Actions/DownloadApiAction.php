<?php

namespace Hypario\Actions;

use Hypario\Database\Table;
use Psr\Http\Message\ServerRequestInterface;

class DownloadApiAction
{

    /**
     * @var Table
     */
    private $table;

    public function __construct(Table $table)
    {
        $this->table = $table;
    }

    public function __invoke(ServerRequestInterface $request)
    {
        $params = $request->getParsedBody();
        var_dump($params);

        $files = $this->table->makeQuery()
            ->where('f.path LIKE ?')
            ->params([$params['path'] . '%'])
            ->fetchAll();
        foreach ($files as $file) {
            @mkdir('build' . DIRECTORY_SEPARATOR . dirname($file->path), 0777, true);
            file_put_contents("build" . DIRECTORY_SEPARATOR . $file->path, file_get_contents("files/" . $file->uuid));
        }
        die();
    }

}
