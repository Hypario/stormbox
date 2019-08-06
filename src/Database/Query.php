<?php

namespace Hypario\Database;

use Traversable;

class Query implements \IteratorAggregate
{

    /**
     * @var array
     */
    private $select;

    /**
     * @var array
     */
    private $from = [];

    /**
     * @var array
     */
    private $where = [];

    private $entity;

    private $order = [];

    private $limit;

    private $joins;
    /**
     * @var \PDO
     */
    private $pdo;
    /**
     * @var
     */
    private $params = [];

    public function __construct(?\PDO $pdo = null)
    {
        $this->pdo = $pdo;
    }

    /**
     * @param string $table
     * @param null|string $alias
     * @return Query
     */
    public function from(string $table, ?string $alias = null): self
    {
        if ($alias) {
            $this->from[$table] = $alias;
        } else {
            $this->from[] = $table;
        }
        return $this;
    }

    public function select(string ...$fields): self
    {
        $this->select = $fields;
        return $this;
    }

    /**
     * Spécifie la limite
     * @param int $length
     * @param int $offset
     * @return Query
     */
    public function limit(int $length, int $offset = 0): self
    {
        $this->limit = "$offset, $length";
        return $this;
    }

    /**
     * Spécifie l'ordre de récupération
     * @param string $order
     * @return Query
     */
    public function order(string $order): self
    {
        $this->order[] = $order;
        return $this;
    }

    /**
     * Ajoute une liaison
     * @param string $table
     * @param string $condition
     * @param string $type
     * @return Query
     */
    public function join(string $table, string $condition, string $type = "left"): self
    {
        $this->joins[$type][] = [$table, $condition];
        return $this;
    }

    public function where(string ...$condition): self
    {
        $this->where = array_merge($this->where, $condition);
        return $this;
    }

    public function count(): int
    {
        $query = clone $this;
        $table = current($this->from);
        return $query->select("COUNT($table.id)")->execute()->fetchColumn();
    }

    public function __toString()
    {
        $parts = ['SELECT'];
        if ($this->select) {
            $parts[] = join(', ', $this->select);
        } else {
            $parts[] = '*';
        }
        $parts[] = 'FROM';
        $parts[] = $this->buildFrom();
        if (!empty($this->joins)) {
            foreach ($this->joins as $type => $joins) {
                foreach ($joins as [$table, $condition]) {
                    $parts[] = strtoupper($type) . " JOIN $table ON $condition";
                }
            }
        }
        if (!empty($this->where)) {
            $parts[] = "WHERE";
            $parts[] = "(" . join(') AND (', $this->where) . ')';
        }
        if (!empty($this->order)) {
            $parts[] = 'ORDER BY';
            $parts[] = join(', ', $this->order);
        }
        if ($this->limit) {
            $parts[] = 'LIMIT ' . $this->limit;
        }
        return join(' ', $parts);
    }

    public function params(array $params): self
    {
        $this->params = array_merge($this->params, $params);
        return $this;
    }

    public function into(string $entity): self
    {
        $this->entity = $entity;
        return $this;
    }

    /**
     * Récupère un résultat
     * @return bool|mixed
     */
    public function fetch()
    {
        $record = $this->execute()->fetch(\PDO::FETCH_ASSOC);
        if ($record === false) {
            return false;
        }
        if ($this->entity) {
            return Hydrator::hydrate($record, $this->entity);
        }
        return $record;
    }

    /**
     * Retourne un résultat ou renvoie une exception
     * @return bool|mixed
     * @throws NoRecordException
     */
    public function fetchOrFail()
    {
        $record = $this->fetch();
        if ($record === false) {
            throw new NoRecordException();
        }
        return $record;
    }

    public function fetchAll(): QueryResult
    {
        return new QueryResult(
            $this->execute()->fetchAll(\PDO::FETCH_ASSOC),
            $this->entity
        );
    }

    private function buildFrom(): string
    {
        $from = [];
        foreach ($this->from as $key => $value) {
            if (is_string($key)) {
                $from[] = "$key as $value";
            } else {
                $from[] = $value;
            }
        }
        return join(', ', $from);
    }

    private function execute()
    {
        $query = $this->__toString();
        if (!empty($this->params)) {
            $statement = $this->pdo->prepare($query);
            $statement->execute($this->params);
            return $statement;
        }
        return $this->pdo->query($query);
    }

    /**
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        return $this->fetchAll();
    }
}
