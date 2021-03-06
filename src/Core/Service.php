<?php

namespace Bedrox\Core;

use Bedrox\Skeleton;
use Doctrine\ORM\EntityManager;

class Service
{
    public $_em;
    public $_req;

    private $self;

    /**
     * Service constructor.
     */
    public function __construct()
    {
        $this->self = get_class();
        $this->_em = $this->getDoctrine();
        $this->_req = $this->getRequest();
    }

    /**
     * Return Doctrine EntityManager to be usable in the current service.
     *
     * @return EntityManager|null
     */
    public function getDoctrine(): ?EntityManager
    {
        return Skeleton::$entityManager;
    }

    /**
     * Return Request to be usable in the current service.
     *
     * @return Request|null
     */
    public function getRequest(): ?Request
    {
        /** @var Response $response */
        $response = Skeleton::$response;
        return $response->getRequest();
    }

    /**
     * @return string
     */
    public function getSelf(): string
    {
        return $this->self;
    }
}
