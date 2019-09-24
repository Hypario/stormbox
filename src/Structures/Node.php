<?php

namespace Hypario\Structures;

class Node
{

    /**
     * data contained in the node
     * @var array
     */
    private $data = [];

    /**
     * all the childrens
     * @var Node[]
     */
    public $childrens = [];

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * add a children to the node
     * @param string $key
     * @param Node $children
     */
    public function addChildren(string $key, Node $children)
    {
        if (!isset($this->childrens[$key])) {
            $this->childrens[$key] = $children;
        }
    }

    /**
     * transform the node and all his childrens into array
     * of type ['data' => [], 'childrens' => []]
     * @return array
     */
    public function toArray(): array
    {
        $result['data'] = $this->data;
        foreach ($this->childrens as $children) {
            $result['childrens'][] = $children->toArray();
        }

        return $result;
    }
}
