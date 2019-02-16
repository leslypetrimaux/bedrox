<?php

namespace Bedrox;

use Bedrox\Core\Session;
use Bedrox\Core\Functions\Parsing;

class Skeleton
{
    public const BASE = '/';

    /** Format de secours */
    public static const DEFAULT_FORMAT = 'json';
    /** Encodage de secours */
    public static const DEFAULT_ENCODE = 'utf-8';

    public $session;
    public $request;
    public $auth;

    protected $parsing;

    /**
     * Skeleton constructor.
     * Used by the Application Kernel. Is currently the Framework Kernel.
     * Handle every actions on the Application.
     */
    public function __construct()
    {
        $globals = (new Session())->globals;
        $this->session = !empty($globals) ? $globals : false;
        $this->parsing = new Parsing();
    }

    /**
     * Return the current user.
     *
     * @return bool
     */
    public function getAuth(): bool
    {
        return $this->auth;
    }

    /**
     * Set the current authentication.
     *
     * @param bool|null $auth
     * @return Skeleton
     */
    public function setAuth(?bool $auth): self
    {
        $this->auth = $auth ?: false;
        return $this;
    }
}

require_once __DIR__ . '/Core/Functions/Globals.php';