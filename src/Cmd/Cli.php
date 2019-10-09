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
    public function generate(string $type, array $args): bool
    {
        switch ($type) {
            case 'route':
                foreach ($args as $key => $value) {
                    if (is_string($key)) {
                        print_r($key . ' => ' . $value . PHP_EOL);
                    }
                }
                break;
            default:
                print_r('Cette commande n\'existe pas pour les générations. Nous vous invitons à consulter la documentation pour la liste des commandes.');
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
