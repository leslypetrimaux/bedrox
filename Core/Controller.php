<?php

namespace Bedrox\Core;

use Bedrox\Skeleton;

class Controller extends Skeleton
{
    public $session;
    public $request;

    /**
     * Controller constructor.
     * Return objects for the Application Controllers.
     *
     * @param Response $response
     */
    public function __construct(Response $response)
    {
        parent::__construct();
        $this->session = $response->session;
        $this->request = $response->request;
        $this->setAuth($_SESSION['APP_AUTH']);
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

    /**
     * Return Session to be usable in the current controller.
     *
     * @return Session
     */
    public function getSession(): Session
    {
        return new Session();
    }
}