<?php

namespace Bedrox\EDR;

class Entity
{
    /**
     * @var string
     */
    public $id;

    /**
     * Return Entity primary key.
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return Entity
     */
    public function setId($id): self
    {
        $this->id = $id;
        return $this;
    }
}
