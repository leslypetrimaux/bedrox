<?php

namespace Bedrox\Core\Interfaces;

interface iSession
{
    /**
     * @return mixed|null
     */
    public function getAll();
    /**
     * @param string $key
     * @return mixed|null
     */
    public function get(string $key);

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function set(string $key, $value);

    /**
     * @param string $key
     */
    public function unset(string $key);
}