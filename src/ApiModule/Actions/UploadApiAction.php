<?php


namespace App\ApiModule\Actions;


use Framework\Actions\Action;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\UploadedFile;
use Framework\Exception\KnownException;
use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UploadApiAction extends Action
{

    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface|string
     * @throws KnownException
     */
    public function __invoke(ServerRequestInterface $request)
    {

        $params = $request->getParsedBody();

        $chunk = isset($params["chunk"]) ? intval($params["chunk"]) : 1;
        $nbChunk = isset($params["nbChunk"]) ? intval($params["nbChunk"]) : 1;

        if (!isset($params["path"]) || empty($params["path"])) {
            throw new KnownException(ERROR_REQUEST, "A file should have a path.");
        }

        // generate the upload path

        /** @var $files UploadedFile[] */
        $files = $request->getUploadedFiles();

        if (empty($files) || $files['blob']->getError()) {
            throw new KnownException(ERROR_REQUEST, "File couldn't be uploaded.");
        }

        if ($chunk > $nbChunk) {
            throw new KnownException(ERROR_REQUEST, "Chunk too big.");
        }

        if ($chunk <= 0 || $nbChunk <= 0) {
            throw new KnownException(ERROR_REQUEST, "Chunk should be positive");
        }

        if ($chunk <= $nbChunk) {
            $path = "1" . DIRECTORY_SEPARATOR . $params['path'];
            // append content into the right file
            foreach ($files as $file) {
                try {
                    if ($this->filesystem->has($path)) {
                        $putStream = tmpfile();
                        fwrite($putStream, $this->filesystem->read($path));
                        fwrite($putStream, $file->getStream()->getContents());
                        $this->filesystem->putStream($path, $putStream);
                    } else {
                        $this->filesystem->writeStream($path, fopen($file->getStream()->getMetadata('uri'), 'r+'));
                    }
                } catch (\Exception $e) {
                    throw new KnownException(SERVER_ERROR); // we should never be in here (can happen if it crashes)
                }
            }
            return new Response(204, [], "");
        }
        throw new KnownException(UNKNOWN_ERROR);
    }
}
