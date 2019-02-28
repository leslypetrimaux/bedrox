<?php

namespace Bedrox;

use Bedrox\Core\Functions\Parsing;
use Bedrox\Core\Session;

class Skeleton
{
    public const BASE = '/';

    protected $session;
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
        $this->session = new Session();
        $this->parsing = new Parsing();
    }

    public function __debugInfo()
    {
        return array(
            '_class' => static::class,
            '_this' => $this
        );
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

    /**
     * Get dumps to display
     *
     * @return array|null
     */
    public function getDumps(): ?array
    {
        return $this->session->get('DUMPS') ?? null;
    }
}

require_once __DIR__ . '/Core/Functions/Globals.php';