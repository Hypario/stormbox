<?php


namespace Hypario\Actions;


use GuzzleHttp\Psr7\UploadedFile;
use Hypario\Actions;
use Hypario\Database\NoRecordException;
use Hypario\Database\Table;
use Psr\Http\Message\ServerRequestInterface;

// ça s'abstrait, surtout le constructeur
class ApiAction extends Actions
{

    public function __invoke(ServerRequestInterface $request): string
    {
        $params = $request->getParsedBody();

        $chunk = isset($params["chunk"]) ? intval($params["chunk"]) : 1;
        $nbChunk = isset($params["nbChunk"]) ? intval($params["nbChunk"]) : 1;

        if (!isset($params["path"]) && !empty($params["path"])) {
            return '{"Error": 1, "Info": "Your file must have a path (including the filename) to be uploaded."}';
        }

        $path = $params["path"];

        // valeur à ne pas hardcoder
        $length = isset($params["chunkSize"]) ? intval($params["chunkSize"]) : 64 * 1024;

        $file = $this->table->makeQuery()
            ->select('f.*')
            ->where("f.path = ?")->params([$path])
            ->fetch();

        if ($file) {
            $uploadDirectory = 'files/' . $file->uuid;
        } else {
            $uuid = uniqid();
            $uploadDirectory = 'files/' . $uuid;
        }

        /** @var $files UploadedFile[] */
        $files = $request->getUploadedFiles();

        if (empty($files) || $files['blob']->getError()) {
            return '{"Error": 2, "Info": "Failed to upload file, please contact the support."}';
        }

        if ($chunk > $nbChunk) {
            return '{"Error": 3, "Info": "The chunk cannot be upper than the number of chunks."}';
        }

        if ($chunk <= 0 || $nbChunk <= 0) {
            return '{"Error": 4, "Info": "The chunk or the number of chunk cannot be negative or equal to 0."}';
        }

        if ($chunk <= $nbChunk) {
            file_put_contents($uploadDirectory, $files['blob']->getStream()->read($length), FILE_APPEND);
            $files['blob']->getStream()->close();
        }

        if (!isset($file) || empty($file)) {
            $this->table->insert([
                'path' => $path,
                'uuid' => $uuid
            ]);
        }
        return '{"Error": 0, "Info": "Upload successful."}';
    }

}
