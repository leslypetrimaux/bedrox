<?php

namespace Bedrox\EDR;

class Table
{
    public $table;

    /**
     * Table constructor.
     *
     * @param string $table
     */
    public function __construct(string &$table)
    {
        $this->table = $table;
    }

    /**
     * @return string|null
     */
    public function getTable(): ?string
    {
        return $this->table;
    }
}