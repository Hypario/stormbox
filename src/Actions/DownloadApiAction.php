<?php

namespace Hypario\Actions;

use GuzzleHttp\Psr7\Response;
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

        $files = $this->table->makeQuery()
            ->where('f.path LIKE ?')
            ->params([$params['path'] . '%'])
            ->fetchAll()->getAll();

        if (!empty($files)) {
            chdir("build");
            // foreach file, create the folders, the file, and write the content
            foreach ($files as $file) {
                exec("mkdir " . dirname($file->path) .
                    " && touch " . $file->path .
                    " && cat ../files/" . $file->uuid . " >> " . $file->path
                );
            }
            //then zip the folder and remove the build
            exec("zip -r {$params['path']} {$params['path']} && rm -rf {$params['path']} && chmod -R 777 {$params['path']}.zip");

            $archive = new \ZipArchive();
            $archive->open("{$params['path']}.zip");
            $response = new Response(200, [
                'Content-Type' => "application/zip",
                'Content-Disposition' => "attachment; filename=" . $params['path'] . ".zip",
                'Pragma' => "public",
                'Expires' => '0',
                "Content-Length" => filesize("{$params['path']}.zip")
            ], file_get_contents($archive->filename));

            $archive->close();
            unlink($params['path'] . '.zip');
            return $response;
        }
        return json_encode("The wanted file or directory doesn't exist");
    }

    public function readfile(string $file) {
        flush();
        readfile($file);
        return "";
    }

}
