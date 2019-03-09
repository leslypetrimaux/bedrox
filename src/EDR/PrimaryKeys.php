<?php

namespace Bedrox\EDR;

class PrimaryKeys
{
    public $keys;

    /**
     * PrimaryKeys constructor.
     *
     * @param string ...$keys
     */
    public function __construct(string ...$keys)
    {
        $this->keys = trim(implode(',', $keys));
    }

    /**
     * @return string[]
     */
    public function getKeys(): array
    {
        return $this->keys;
    }
}