<?php

namespace Bedrox\Cmd;

use Bedrox\Cmd\Router\Controller;
use Bedrox\Cmd\Router\Generator;
use Bedrox\Cmd\Router\Route;

class Cli extends Console
{
    protected const ROUTE = 'route';

    public function generate(string $type, array $args): bool
    {
        switch ($type) {
            case self::ROUTE:
                $params = array();
                $countArgs = count($args) - 2;
                foreach ($args as $key => $value) {
                    if (is_string($key)) {
                        $params[$key] = $value;
                    }
                }
                if ($countArgs === count($params)) {
                    $generator = new Generator();
                    $generator->configure($params);
                    /** @var Route $route */
                    $route = $generator->get('route');
                    /** @var Controller $controller */
                    $controller = $generator->get('controller');
                    $route->setController($controller);
                    print_r($route);
                    print_r($controller);
                    parent::print('La génération de route n\'est pas encore disponible.');
                } else {
                    parent::print('Le nombre de paramètres ne correspond pas.');
                }
                break;
            default:
                parent::print('Cette commande n\'existe pas pour les générations. Nous vous invitons à consulter la documentation pour la liste des commandes.');
                break;
        }
        return false;
    }

    // TODO: import Doctrine Console
}
