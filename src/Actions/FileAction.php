<?php


namespace Hypario\Actions;


use Hypario\ActionInterface;
use Hypario\Database\Table;
use Hypario\File;
use Hypario\KnownException;
use Hypario\structures\Node;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class FileAction implements ActionInterface
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
     * @return ResponseInterface|string
     * @throws KnownException
     */
    public function __invoke(ServerRequestInterface $request): string
    {
        /** @var File[] $files */
        $files = $this->table->makeQuery()
            ->select('f.*')
            ->fetchAll()
            ->getAll();

        $tree = new Node(['name' => '/']);
        foreach ($files as $file) {

            $parts = explode('/', $file->path);
            $leafPart = array_pop($parts);

            $parentNode = &$tree;
            foreach ($parts as $part) {
                $parentNode->addChildren($part, new Node(['name' => $part]));

                $parentNode = &$parentNode->childrens[$part];
            }

            if (empty($parentNode->childrens[$leafPart])) {
                $parentNode->addChildren($leafPart, new Node(['name' => $leafPart]));
            }
        }
        return json_encode($tree->toArray());
    }
}
