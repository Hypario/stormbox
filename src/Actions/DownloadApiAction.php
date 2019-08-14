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
        if (isset($params['path']) && !empty($params['path'])) {
            $wanted = $params['path'];
        } else {
            throw new KnownException(ERROR_FILE_DONT_EXIST);
        }

        $files = $this->table->makeQuery()
            ->where('f.path LIKE ?')
            ->params(["%$wanted%"])
            ->fetchAll()->getAll();

        if (!empty($files)) {

            // if the file have an extension, get the zipname of it
            if (strpos($wanted, '.')) {
                $zipName = substr($wanted, 0, strpos($wanted, '.')) . '.zip';
            } else {
                $zipName = "{$wanted}.zip";
            }

            // path to zip
            $zipPath = ROOT . "/build/" . $zipName;

            //then zip the folder
            $zip = new \ZipArchive();
            $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

            foreach ($files as $file) {
                // all the directories are created automatically (due to localname)
                $zip->addFile(ROOT . "/files/" . $this->binToUuid($file->uuid), $file->path);
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

    /**
     * transform a binary to uuid
     * @param string $bin
     * @return string
     */
    private function binToUuid(string $bin): string
    {
        return join("-", unpack("H8time_low/H4time_mid/H4time_hi/H4clock_seq_hi/H12clock_seq_low", $bin));
    }

}
