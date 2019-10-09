<?php

namespace Bedrox\Cmd\Router;

use Bedrox\Cmd\Console;

class Generator
{
    protected const ROUTE = 'route';
    protected const CONTROLLER = 'controller';

    public $route;
    public $controller;

    public function configure(array &$args): void
    {
        $this->route = new Route($args['url']);
        $this->controller = new Controller($args['src']);
    }

    public function get(string $param)
    {
        switch ($param) {
            case self::ROUTE:
                return $this->route;
                break;
            case self::CONTROLLER:
                return $this->controller;
                break;
            default:
                Console::print('Erreur !');
                break;
        }
        return null;
    }
}
