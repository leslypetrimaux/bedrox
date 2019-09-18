<?php

namespace Bedrox\Core;

use Bedrox\Skeleton;

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
     * @return EntityManager
     */
    public function getEntityManager(): EntityManager
    {
        return new EntityManager();
    }

    public function getDoctrine(): ?\Doctrine\ORM\EntityManager
    {
        return Skeleton::$entityManager;
    }
}
