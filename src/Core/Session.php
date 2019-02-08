<?php

namespace Bedrox\Core;

use Bedrox\Core\Interfaces\iSession;

class Session implements iSession
{
    public $globals;

    /**
     * Session constructor.
     * Create globals from PHP _SESSION.
     */
    public function __construct()
    {
        $this->globals = !empty($_SESSION) ? $_SESSION : false;
    }

    /**
     * Return the wanted PHP _SESSION value.
     *
     * @param string $key
     * @return null|string
     */
    public function get(string $key): ?string
    {
        return !empty($_SESSION[$key]) ? $_SESSION[$key] : null;
    }

    /**
     * Define a new PHP _SESSION variable.
     *
     * @param string $key
     * @param string $value
     */
    public function set(string $key, string $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Destroy a PHP _SESSION variable.
     *
     * @param string $key
     */
    public function unset(string $key): void
    {
        unset($_SESSION[$key]);
    }
}