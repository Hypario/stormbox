<?php


namespace Hypario\Actions;


use Hypario\ActionInterface;
use Hypario\Database\Table;
use Hypario\File;
use Hypario\KnownException;
use Hypario\Structures\Node;
use Hypario\Validator\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class FileAction extends ActionInterface
{

    protected $params = ["path"];

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
     * @return ResponseInterface|string
     * @throws \Exception
     */
    public function __invoke(ServerRequestInterface $request): string
    {
        // it uses getParams to validate data
        $validator = $this->getValidator($request);

        if (!$validator->isValid()) {
            throw new KnownException(ERROR_PATH);
        }

        $path = $this->getParams($request)['path'];

        /** @var File[] $files */
        $files = $this->table->makeQuery()
            ->where('f.path LIKE ?')
            ->params(["%$path%"])
            ->fetchAll()->getAll();

        // return the tree json_encoded
        return json_encode($this->getTree($files)->toArray());
    }

    /**
     * Filter the parameters passed
     * @param ServerRequestInterface $request
     * @return array
     */
    protected function getParams(ServerRequestInterface $request): array
    {
        $params = $request->getParsedBody();

        return array_filter($params, function ($key) {
            return in_array($key, $this->params);
        }, ARRAY_FILTER_USE_KEY);
    }

    protected function getValidator(ServerRequestInterface $request): Validator
    {
        return parent::getValidator($request)
            ->required('path');
    }

    /**
     * @param File[] $files
     * @return Node
     */
    private function getTree(array $files): Node
    {

        $tree = new Node(['name' => '/', 'type' => 'dir']);

        foreach ($files as $file) {

            // get all the subfolder (if there is at least one)
            $parts = explode('/', $file->path);

            // get all the leaf (all the files at the end)
            $leafPart = array_pop($parts);

            // we need here the root of the tree (here is '/')
            $parentNode = &$tree;

            // add all the subfolders to the tree
            foreach ($parts as $part) {

                // add a subfolder as a node, in data we give the name and is_dir = true
                $parentNode->addChildren(
                    $part,
                    new Node(
                        ['name' => $part, 'type' => 'dir']
                    )
                );

                // reference the next children (the next subfolder)
                $parentNode = &$parentNode->childrens[$part];
            }

            // add the files
            if (empty($parentNode->childrens[$leafPart])) {
                $parentNode->addChildren(
                    $leafPart,
                    new Node(
                        ['name' => $leafPart, 'type' => 'file']
                    )
                );
            }
        }

        return $tree;
    }
}
