<?php


namespace Hypario\Actions;


use function GuzzleHttp\Psr7\str;
use Hypario\ActionInterface;
use Hypario\Database\Table;
use Hypario\File;
use Hypario\KnownException;
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
        if (!empty($request->getParsedBody())) {
            return $this->getContent($request->getParsedBody());
        }
        return $this->getAll();
    }

    /**
     * @return string
     */
    private function getAll(): string
    {
        return json_encode(
            $this->table->makeQuery()
                ->select('f.*')
                ->fetchAll()
                ->getAll()
        );
    }

    /**
     * @param array $params
     * @return string
     */
    private function getContent(array $params): string
    {
        var_dump("To be developped");
        die();
    }
}
