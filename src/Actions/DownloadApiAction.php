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
        $params = $request->getParsedBody();
        if (!empty($params['path'])) {
            $wanted = $params['path'];
        } else {
            throw new KnownException(ERROR_FILE_DONT_EXIST);
        }

        $files = $this->table->makeQuery()
            ->where('f.path LIKE ?')
            ->params(["%$wanted%"])
            ->fetchAll()->getAll();


        if (!empty($files)) {

            $precise = false;

            // if is a path get last item in path
            if (strpos(trim($wanted, '/'), '/')) {
                $parts = explode('/', $wanted);
                $wanted = array_pop($parts);

                // need a precise file
                $precise = true;
            }

            // if the file have an extension, get the zipname of it
            if (strpos($wanted, '.')) {
                $zipName = substr($wanted, 0, strpos($wanted, '.')) . '.zip';
            } // else is a folder
            else {
                // can't be a precise file if folder
                $precise = false;

                // get the zipname of a folder
                $zipName = "{$wanted}.zip";

                // repath to get all the subfolders
                foreach ($files as $file) {
                    $file->path = substr($file->path, strpos($file->path, $wanted));
                }
            }

            // path to zip
            $zipPath = ROOT . "/build/" . $zipName;

            //then zip the folder
            $zip = new \ZipArchive();
            $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

            foreach ($files as $file) {
                // all the directories are created automatically (due to localname)
                $zip->addFile(ROOT . "/files/" . $file->uuid, $precise ? $wanted : $file->path);
            }

            $zip->close(); // the zip is created when closed

            $zip->open($zipPath); // open it again to send the content

            // create the download response by putting the zip content inside response
            $response = new Response(200, [
                'Content-Type' => "application/zip",
                'Content-Disposition' => "attachment; filename=" . $zipName,
                'Pragma' => "public",
                'Expires' => '0',
                "Content-Length" => filesize($zipPath)
            ], file_get_contents($zip->filename));

            $zip->close(); // close it again to remove it

            // remove the build
            unlink($zipPath);

            // return the download response
            return $response;
        }
        throw new KnownException(ERROR_FILE_DONT_EXIST);
    }

}
