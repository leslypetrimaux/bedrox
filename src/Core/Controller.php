<?php

namespace Bedrox\Core;

use Bedrox\Skeleton;
use Bedrox\EDR\EntityManager as EDR;
use Doctrine\ORM\EntityManager;

class Controller extends Skeleton
{
    public $request;
    public $_em;

    /**
     * Controller constructor.
     * Return objects for the Application Controllers.
     *
     * @param Response $response
     */
    public function __construct(Response $response)
    {
        parent::__construct();
        $this->request = $response->request;
        $this->setAuth($this->session->get('APP_AUTH'));
        $this->_em = $this->getDoctrine();
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
