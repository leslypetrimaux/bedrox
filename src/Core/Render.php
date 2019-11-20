<?php


namespace Bedrox\Core;

class Render
{
    public $data;

    /**
     * Render constructor.
     * @param array|null $data
     */
    public function __construct(?array $data = array())
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}
