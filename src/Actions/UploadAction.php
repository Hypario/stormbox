<?php


namespace Hypario\Actions;


use GuzzleHttp\Psr7\UploadedFile;
use Hypario\ActionInterface;
use Hypario\Database\Table;
use Hypario\KnownException;
use Psr\Http\Message\ServerRequestInterface;

class UploadAction implements ActionInterface
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

        // get or generate uuid
        if ($file) {
            $uuid = $file->uuid;
        } else {
            $uuid = $this->gen_uuid();
        }

        // generate the upload path from uuid
        $uploadDirectory = ROOT . '/files/' . $uuid;

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
                    'uuid' => $this->uuidToBin($uuid)
                ]);
            }

            throw new KnownException(ERROR_OK);
        }

        throw new KnownException(ERROR_DEFAULT);
    }

    /**
     * generate a v4 uuid
     * @return string
     */
    private function gen_uuid(): string
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),

            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * transform a uuid to binary to optimize DB
     * @param string $uuid
     * @return string
     */
    private function uuidToBin(string $uuid): string
    {
        return pack("H*", str_replace('-', '', $uuid));
    }

}
