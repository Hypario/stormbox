<?php


namespace App\ApiModule\Actions;

use Exception;
use Framework\Actions\Action;
use Framework\Exception\KnownException;
use Framework\Validator\Validator;
use League\Flysystem\Filesystem;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class FileApiAction extends Action
{

    protected $params = ["path"];
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
     * @throws Exception
     */
    public function __invoke(ServerRequestInterface $request): string
    {
        $params = $this->getParams($request);
        $validator = $this->getValidator($params);

        if (!$validator->isValid()) {
            throw new KnownException(ERROR_REQUEST, "A file should have a path");
        }

        $path = $params['path'];

        // return the tree json_encoded

        if ($this->filesystem->has($path)) {
            var_dump($this->filesystem->get($path));
            die();
        }

        throw new KnownException(NOT_FOUND);
    }

    protected function getValidator(array $params): Validator
    {
        return parent::getValidator($params)
            ->required('path');
    }
}
