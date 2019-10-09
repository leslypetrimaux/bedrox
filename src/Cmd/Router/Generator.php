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
        $routeName = !empty($args[Route::NAME]) ? $args[Route::NAME] : null;
        $routePath = !empty($args[Route::PATH]) ? $args[Route::PATH] : null;
        $routeParams = $this->setParams(!empty($args[Route::PARAMS]) ? $args[Route::PARAMS] : null);
        $controllerSrc = !empty($args[Controller::SRC]) ? $args[Controller::SRC] : null;
        $this->route = new Route($routeName, $routePath, $routeParams);
        $this->controller = new Controller($controllerSrc);
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

    public function setParams(?string $params): ?array
    {
        return null === $params ? null : explode(',', $params);
    }
}
