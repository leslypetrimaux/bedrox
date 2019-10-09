<?php


namespace Bedrox\Cmd\Router;


class Controller
{
    public $src;

    public function __construct(string $src)
    {
        $this->src = $src;
    }
}
