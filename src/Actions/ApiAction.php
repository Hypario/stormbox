<?php


namespace Hypario\Actions;


use GuzzleHttp\Psr7\UploadedFile;
use Hypario\Database\NoRecordException;
use Hypario\Database\Table;
use Psr\Http\Message\ServerRequestInterface;

class ApiAction
{

    /**
     * @var Table
     */
    private $table;

    public function __construct(Table $table)
    {
        $this->table = $table;
    }

    public function __invoke(ServerRequestInterface $request): string
    {
        $params = $request->getParsedBody();
        $chunk = isset($params["chunk"]) ? intval($params["chunk"]) : 1;
        $nbChunk = isset($params["nbChunk"]) ? intval($params["nbChunk"]) : 1;
        if (!isset($params["path"]) && !empty($params["path"])) {
            return json_encode('{"Error": 0, "info": "Your file must have a path (including the filename) to be uploaded."}');
        }
        $path = $params["path"];
        $length = isset($params["chunkSize"]) ? intval($params["chunkSize"]) : 64 * 1024;
        try {
            $file = $this->table->makeQuery()
                ->select('f.*')
                ->where("f.path = ?")->params([$path])
                ->fetchOrFail();
            $uploadDirectory = 'files/' . $file->uuid;
        } catch (NoRecordException $e) {
            $uuid = uniqid();
            $uploadDirectory = 'files/' . $uuid;
        }

        /** @var $files UploadedFile[] */
        $files = $request->getUploadedFiles();

        if (empty($files) || $files['blob']->getError()) {
            return json_encode('{"Error": 0, "info": "Failed to upload file, please contact the support."}');
        }

        if ($chunk > $nbChunk) {
            return json_encode('{"Error": 0, "info": "The chunk can\'t be upper than the number of chunks."}');
        }

        if ($chunk <= 0 || $nbChunk <= 0) {
            return json_encode('{"Error": 0, "info": "The chunk or the number of chunk can\'t be negative or equal to 0."}');
        }

        if ($chunk <= $nbChunk) {

            $out = @fopen($uploadDirectory, $chunk == 1 ? "wb" : "ab");
            if ($out) {
                @fwrite($out, $files['blob']->getStream()->read($length));
            }

            @fclose($out);
            $files['blob']->getStream()->close();

        }

        if (!isset($file) || empty($file)) {
            $this->table->insert([
                'path' => $path,
                'uuid' => $uuid
            ]);
        }
        return json_encode('{"ok": 1, "info": "Upload successful."}');
    }

}
