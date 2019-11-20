<?php

namespace Bedrox\Core;

use Bedrox\Skeleton;
use Doctrine\ORM\EntityManager;

class Service
{
    public $_em;

    /**
     * Service constructor.
     */
    public function __construct()
    {
        $this->_em = $this->getDoctrine();
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
