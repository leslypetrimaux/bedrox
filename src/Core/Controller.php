<?php

namespace Bedrox\Core;

use Bedrox\Skeleton;
use Bedrox\EDR\EntityManager as EDR;
use Doctrine\ORM\EntityManager;

class Controller extends Skeleton
{
    public $request;

    /**
     * Controller constructor.
     * Return objects for the Application Controllers.
     */
    public function __construct()
    {
        parent::__construct();
        $this->request = Skeleton::getRequest();
    }

    /**
     * Return EntityManager to be usable in the current controller.
     *
     * @return EDR
     */
    public function getEntityManager(): EDR
    {
        return new EDR();
    }

    /**
     * Return Doctrine EntityManager to be usable in the current controller.
     *
     * @return EntityManager|null
     */
    public function getDoctrine(): ?EntityManager
    {
        return Skeleton::$entityManager;
    }
}
