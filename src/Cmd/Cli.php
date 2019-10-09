<?php

namespace Bedrox\Cmd;

use Bedrox\Cmd\Router\Generator;
use Bedrox\Skeleton;

class Cli extends Console
{
    protected const ROUTE = 'route';

    protected $bedrox;

    public function __construct()
    {
        $this->bedrox = new Skeleton(true);
    }

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
                    // TODO: new Route (Add in Yaml + generate new Controller)
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

    // TODO: use Doctrine Console

    /**
     * @return Skeleton
     */
    public function getBedrox(): Skeleton
    {
        return $this->bedrox;
    }
    /**
     * @param Skeleton $bedrox
     * @return Cli
     */
    public function setBedrox(Skeleton $bedrox): Cli
    {
        $this->bedrox = $bedrox;
        return $this;
    }
}
