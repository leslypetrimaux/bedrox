<?php

namespace Bedrox\Core;

use Bedrox\Skeleton;
use Bedrox\EDR\EntityManager as EDR;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

class Controller extends Skeleton
{
    public const PHP_CONSTRUCTOR = '__construct';
    public const CONSTRUCTOR = '__self';

    private $request;

    /**
     * Controller constructor.
     * Return objects for the Application Controllers.
     */
    public function __construct()
    {
        parent::__construct();
        $this->request = parent::getResponse()->getRequest();
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

    /**
     * @param $class
     * @return EntityRepository|null
     */
    public function getRepo($class): ?EntityRepository
    {
        return $this->getDoctrine()->getRepository($class);
    }

    /**
     * @return Request|null
     */
    public function getRequest(): ?Request
    {
        return $this->request;
    }

    /**
     * @return Headers|null
     */
    public function getHeaders(): ?Headers
    {
        return $this->request->getHeaders();
    }
}
