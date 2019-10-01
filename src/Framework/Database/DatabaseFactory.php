<?php


namespace Framework\Database;


use Framework\KnownException;
use Psr\Container\ContainerInterface;

class DatabaseFactory
{

    /**
     * @param ContainerInterface $c
     * @return \PDO
     * @throws KnownException
     */
    public function __invoke(ContainerInterface $c): \PDO
    {
        try {
            $pdo =  new \PDO("pgsql:host={$c->get('database.host')};dbname={$c->get('database.name')};options='--client_encoding=UTF8'",
                $c->get('database.username'),
                $c->get('database.password'),
                [
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
                ]
            );
            $pdo->exec("SET search_path TO {$c->get('database.schema')}");
            return $pdo;
        } catch (\PDOException $e) {
            throw new KnownException(PDO_ERROR);
        }
    }

}
