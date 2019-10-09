<?php


namespace Bedrox\Cmd\Router;


class Route
{
    public $url;

    public function __construct(string $url)
    {
        $this->url = $url;
    }
}
