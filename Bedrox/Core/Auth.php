<?php

namespace Bedrox\Core;

use Bedrox\Skeleton;

class Auth extends Skeleton
{
    public $request;
    protected $em;

    /**
     * Auth constructor.
     * Handle user connexion.
     */
    public function __construct()
    {
        parent::__construct();
        $this->request = !empty($_REQUEST) ? $_REQUEST : false;
        $this->em = new EntityManager();
    }

    /**
     * Verify the token integrity
     *
     * @return bool
     */
    public function tokenVerification(): bool
    {
        return !empty($this->request['token']) ? $this->session['APP_AUTH'] = $_SESSION['APP_AUTH'] = $this->request['token'] === $this->session['APP_TOKEN'] : false;
    }
}