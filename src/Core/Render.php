<?php


namespace Bedrox\Core;

class Render
{
    private $data;
    private $format;
    private $force;

    /**
     * Render constructor.
     * @param array|null $data
     * @param string|null $format
     * @param bool $force
     */
    public function __construct(?array $data = array(), ?string $format = null, bool $force = false)
    {
        $this->data = $data;
        $this->format = $format;
        $this->force = $force;
    }

    /**
     * Return array of data to render the view
     *
     * @return array|null
     */
    public function getData(): ?array
    {
        return $this->data;
    }

    /**
     * @return string|null
     */
    public function getFormat(): ?string
    {
        return $this->format;
    }

    /**
     * @return bool
     */
    public function getForce(): bool
    {
        return $this->force ?? false;
    }
}
