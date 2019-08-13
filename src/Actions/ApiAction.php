<?php


namespace Hypario\Actions;


use GuzzleHttp\Psr7\UploadedFile;
use Hypario\ActionInterface;
use Hypario\Database\Table;
use Hypario\KnownException;
use Psr\Http\Message\ServerRequestInterface;

class ApiAction implements ActionInterface
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
     * @return string
     * @throws KnownException
     */
    public function __invoke(ServerRequestInterface $request): string
    {
        $params = $request->getParsedBody();

        $chunk = isset($params["chunk"]) ? intval($params["chunk"]) : 1;
        $nbChunk = isset($params["nbChunk"]) ? intval($params["nbChunk"]) : 1;


        if (!isset($params["path"]) || empty($params["path"])) {
            throw new KnownException(ERROR_PATH);
        }


        $path = $params["path"];

        // value that shouldn't be hard coded
        $length = isset($params["chunkSize"]) ? intval($params["chunkSize"]) : 64 * 1024;

        $file = $this->table->makeQuery()
            ->select('f.*')
            ->where("f.path = ?")->params([$path])
            ->fetch();

        if ($file) {
            $uploadDirectory = ROOT . '/files/' . $file->uuid;
            $uuid = $file->uuid;
        } else {
            $uuid = uniqid();
            $uploadDirectory = ROOT . '/files/' . $uuid;
        }

        /** @var $files UploadedFile[] */
        $files = $request->getUploadedFiles();

        if (empty($files) || $files['blob']->getError()) {
            throw new KnownException(ERROR_UPLOAD_FAILED);
        }

        if ($chunk > $nbChunk) {
            throw new KnownException(ERROR_TOO_BIG_CHUNK);
        }

        if ($chunk <= 0 || $nbChunk <= 0) {
            throw new KnownException(ERROR_MUST_BE_POSITIVE);
        }

        if ($chunk <= $nbChunk) {
            $flag = $chunk === 1 ? 0 : FILE_APPEND;
            file_put_contents($uploadDirectory, $files['blob']->getStream()->read($length), $flag);
            $files['blob']->getStream()->close();

            if (!isset($file) || empty($file)) {
                $this->table->insert([
                    'path' => $path,
                    'uuid' => $uuid
                ]);
            }

            throw new KnownException(ERROR_OK);
        }

        throw new KnownException(ERROR_DEFAULT);
    }

}
