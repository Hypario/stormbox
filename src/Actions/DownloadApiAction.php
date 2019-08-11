<?php

namespace Hypario\Actions;

use GuzzleHttp\Psr7\Response;
use Hypario\ActionInterface;
use Hypario\Database\Table;
use Hypario\KnownException;
use Psr\Http\Message\ServerRequestInterface;

class DownloadApiAction implements ActionInterface
{

    /**
     * @var Table
     */
    private $table;

    public function __construct(Table $table)
    {
        $this->table = $table;
    }

    /**
     * @param ServerRequestInterface $request
     * @return Response|\Psr\Http\Message\ResponseInterface|string
     * @throws KnownException
     */
    public function __invoke(ServerRequestInterface $request)
    {
        $wanted = $request->getParsedBody()['path'];

        $files = $this->table->makeQuery()
            ->where('f.path LIKE ?')
            ->params(["%$wanted%"])
            ->fetchAll()->getAll();

        if (!empty($files)) {
            // foreach file, create the folders, the file, and write the content
            foreach ($files as $file) {
                if (dirname($file->path) !== $wanted) {
                    // path of the subfolder or file
                    $path = substr($file->path, strpos($file->path, $wanted), strlen($file->path));
                } else {
                    // path of a root folder
                    $path = ROOT . "/build/$wanted";
                }
            }

            // if the file have an extension, get the zipname of it
            if (strpos($wanted, '.')) {
                $zipname = substr($wanted, 0, strpos($wanted, '.')) . '.zip';
            } else {
                $zipname = "{$wanted}.zip";
            }

            //then zip the folder

            $zip = new \ZipArchive();
            $zip->open(ROOT . "/build/" . $zipname, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

            foreach ($files as $file) {
                $zip->addFile(ROOT . "/files/" . $file->uuid, $file->path);
            }
            $zip->close(); // the zip is created when closed

            $zip->open(ROOT . "/build/" . $zipname); // open it again to put the content inside

            // create the download response by putting the zip content inside response
            $response = new Response(200, [
                'Content-Type' => "application/zip",
                'Content-Disposition' => "attachment; filename=" . $zipname,
                'Pragma' => "public",
                'Expires' => '0',
                "Content-Length" => filesize(ROOT . "/build/" . $zipname)
            ], file_get_contents($zip->filename));

            $zip->close(); // close it again to remove it

            // remove the build
            unlink(ROOT . "/build/" . $zipname);

            // return the download response
            return $response;
        }
        throw new KnownException(ERROR_FILE_DONT_EXIST);
    }

}
