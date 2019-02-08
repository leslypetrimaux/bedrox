<?php

namespace Bedrox\Core\Interfaces;

interface iSession
{
    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key);

    /**
     * @param string $key
     * @param string $value
     * @return mixed
     */
    public function set(string $key, string $value);

    /**
     * @param string $key
     * @return mixed
     */
    public function unset(string $key);
}