<?php


namespace App\ApiModule\Actions;


use App\AuthModule\DatabaseAuth;
use Framework\Actions\Action;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\UploadedFile;
use Framework\Exception\KnownException;
use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class UploadApiAction extends Action
{

    /**
     * @var Filesystem
     */
    private ?Filesystem $filesystem = null;
    /**
     * @var DatabaseAuth
     */
    private DatabaseAuth $auth;
    /**
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    public function __construct(DatabaseAuth $auth, ContainerInterface $container, ?Filesystem $filesystem = null)
    {
        $this->auth = $auth;
        $this->container = $container;
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
        $errors = [];

        $chunk = isset($params["chunk"]) ? intval($params["chunk"]) : 1;
        $nbChunk = isset($params["nbChunk"]) ? intval($params["nbChunk"]) : 1;

        if (!isset($params["path"]) || empty($params["path"])) {
            throw new KnownException(ERROR_REQUEST, "A file should have a path.");
        }

        // generate the upload path

        /** @var $files UploadedFile[] */
        $files = $request->getUploadedFiles();

        if (!array_key_exists('blob', $files) || $files['blob']->getError()) {
            $errors[] = "File couldn't be uploaded.";
        }
        if ($chunk > $nbChunk) {
            $errors[] = "Chunk too big";
        }
        if ($chunk <= 0 || $nbChunk <= 0) {
            $errors[] = "Chunk should be positive";
        }
        if ($errors) {
            throw new KnownException(ERROR_REQUEST, json_encode($errors), true);
        }

        if ($chunk <= $nbChunk) {
            $path = $this->auth->getUser()->id . DIRECTORY_SEPARATOR . $params['path'];
            $file = $files['blob'];

            if (is_null($this->filesystem)) {
                $flag = $chunk === 1 ? 0 : FILE_APPEND;
                file_put_contents(
                    $this->container->get('uploadDirectory') . DIRECTORY_SEPARATOR . $path,
                    $file->getStream()->read($file->getSize()),
                    $flag
                );
            } else {
                try {
                    if ($this->filesystem->has($path)) {
                        if ($chunk > 1) {
                            $this->write($path, $file, true);
                        } else {
                            $this->filesystem->delete($path);
                            $this->write($path, $file);
                        }
                    } else {
                        $this->write($path, $file);
                    }
                } catch (\Exception $e) {
                    throw new KnownException(SERVER_ERROR, null, true); // we should never be in here (can happen if it crashes)
                }
            }
            return new Response(204, [], "");
        }
        throw new KnownException(UNKNOWN_ERROR, null, true);
    }

    /**
     * @param string $path
     * @param UploadedFile $file
     * @param bool $update
     * @throws FileExistsException
     * @throws FileNotFoundException
     */
    private function write(string $path, UploadedFile $file, bool $update = false)
    {
        if ($update) {
            // append to file
            $tmp = tmpfile();
            fwrite($tmp, fread($backup = $this->filesystem->readStream($path), $this->filesystem->getSize($path)));
            fwrite($tmp, $file->getStream()->getContents());

            $this->filesystem->updateStream($path, $tmp);

            fclose($tmp);
            fclose($backup);
        } else {
            $this->filesystem->writeStream($path, fopen($file->getStream()->getMetadata('uri'), 'r+'));
        }
    }

}
