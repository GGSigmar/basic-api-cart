<?php

namespace App\Pagination;

class PaginatedCollection
{
    /**
     * @var array
     */
    private $items;

    /**
     * @var int
     */
    private $count;

    /**
     * @var int
     */
    private $total;

    /**
     * @var array
     */
    private $_links = [];

    /**
     * @param array $items
     * @param int $total
     */
    public function __construct(array $items, int $total)
    {
        $this->items = $items;
        $this->total = $total;
        $this->count = count($items);
    }

    /**
     * @param string $rel
     * @param string $url
     */
    public function addLink(string $rel, string $url)
    {
        $this->_links[$rel] = $url;
    }
}