<?php

namespace Bedrox\EDR;

class Column
{
    public $name;
    public $type;
    public $length;

    public function __construct(string &$name, string &$type, ?string $length = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->length = $length;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string|null
     */
    public function getLength(): ?string
    {
        return $this->length;
    }
}