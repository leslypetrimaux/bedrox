<?php

namespace Bedrox\EDR;

class ForeignKeys
{
    public $entity;

    /**
     * ForeignKeys constructor.
     *
     * @param string $entity
     */
    public function __construct(string &$entity)
    {
        $this->entity = $entity;
    }

    /**
     * @return string
     */
    public function getEntity(): string
    {
        return $this->entity;
    }
}