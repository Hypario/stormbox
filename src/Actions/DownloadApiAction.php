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
        $wanted = $request->getParsedBody()['path'];

        $files = $this->table->makeQuery()
            ->where('f.path LIKE ?')
            ->params(['%' . $wanted . '%'])
            ->fetchAll()->getAll();

        if (!empty($files)) {
            chdir("build");
            // foreach file, create the folders, the file, and write the content
            foreach ($files as $file) {

                if (dirname($file->path) !== $wanted) {
                    $path = substr($file->path, strpos($file->path, $wanted), strlen($file->path));
                }

// jamais de shell exec quand tu peux le faire en PHP
// jamais de passage de paramètre non-échappé dans un exec
// c.f. https://www.php.net/manual/en/ref.exec.php
                exec('mkdir ' . dirname($path) . ' || touch ' . $path . ' && cat ../files/' . $file->uuid . ' >> ' . $path);
            }

            // if the file have an extension, get the zipname of it
            if (strpos($wanted, '.')) {
                $zipname = substr($wanted, 0, strpos($wanted, '.')) . '.zip';
            } else {
                $zipname = "{$wanted}.zip";
            }
// pareil, tu peux pas dépendre de la présence de `zip` dans le path,
// surtout si tu as PHP libzip
            //then zip the folder and remove the build
            exec("zip -r $zipname $wanted && rm -rf $wanted");

            $zip = new \ZipArchive();
            $zip->open($zipname);

            $response = new Response(200, [
                'Content-Type' => "application/zip",
                'Content-Disposition' => "attachment; filename=" . $zipname,
                'Pragma' => "public",
                'Expires' => '0',
                "Content-Length" => filesize($zipname)
            ], file_get_contents($zip->filename));

            $zip->close();
            unlink($zipname);
            return $response;
        }
        // tu faisais une structure { Error, info }, et maintenant tu
        // renvois seulement info ?
        return json_encode("The wanted file or directory doesn't exist");
    }

}
