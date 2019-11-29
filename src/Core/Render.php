<?php


namespace Bedrox\Core;

class Render
{
    private $data;

    /**
     * Render constructor.
     * @param array|null $data
     */
    public function __construct(?array $data = array())
    {
        $this->data = $data;
    }

    /**
     * Return array of data to render the view
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}
