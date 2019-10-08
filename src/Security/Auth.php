<?php

namespace Bedrox\Security;

use Bedrox\Skeleton;

class Auth extends Skeleton
{
    public $request;

    /**
     * Auth constructor.
     * Handle user connexion.
     */
    public function __construct()
    {
        parent::__construct();
        $this->request = !empty($_REQUEST) ? $_REQUEST : false;
    }

    /**
     * Verify the token integrity
     *
     * @return bool
     */
    public function tokenVerification(): bool
    {
        $verification = !empty($this->request['token']) ? $this->request['token'] === $this->session->get('APP_TOKEN') : false;
        $this->session->set('APP_AUTH', $verification);
        return $verification;
    }
}
