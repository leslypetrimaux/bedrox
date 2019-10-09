<?php


namespace Bedrox\Cmd\Router;


use Bedrox\Cmd\Console;

class Route
{
    public const NAME = 'name';
    public const PATH = 'path';
    public const CONTROLLER = 'controller';
    public const PARAMS = 'params';

    public $name;
    public $path;
    public $controller;
    public $params;

    public function __construct(string $name, string $path, array $params = null)
    {
        $this->name = $name;
        $this->path = $path;
        $this->params = $params;
    }

    public function setController(Controller &$controller): void
    {
        if (!empty($controller->src)) {
            $this->controller = '\\App\\Controller\\' . $controller->src . '::' . $controller->function;
        } else {
            Console::print('Impossible de récupérer le nom du controller. Veuillez réessayer.');
        }
    }
}
