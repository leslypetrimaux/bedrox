<?php

namespace Bedrox\Core;

use Bedrox\Core\Interfaces\iSession;

class Session implements iSession
{
    /**
     * Session constructor.
     * Create globals from PHP _SESSION.
     */
    public function __construct()
    {
        !isset($_SESSION) ? session_start() : null;
    }

    /**
     * @return mixed|null
     */
    public function getAll()
    {
        return !empty($_SESSION) ? $_SESSION : null;
    }

    /**
     * Return the wanted PHP _SESSION value.
     *
     * @param string $key
     * @return mixed|null
     */
    public function get(string $key)
    {
        return !empty($_SESSION[$key]) ? $_SESSION[$key] : null;
    }

    /**
     * Define a new PHP _SESSION variable.
     *
     * @param string $key
     * @param mixed $value
     */
    public function set(string $key, $value): void
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