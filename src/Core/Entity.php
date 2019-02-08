<?php

namespace Bedrox\Core;

class Entity
{
    public $id;

    /**
     * Return Entity primary key.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }
}