<?php


namespace Bedrox\Cmd;


use Bedrox\Skeleton;

class Cli
{
    protected $bedrox;

    public function __construct()
    {
        $this->bedrox = new Skeleton();
    }

    // TODO: new Route (Add in Yaml + generate new Controller)
    // TODO: use Doctrine Console
}
