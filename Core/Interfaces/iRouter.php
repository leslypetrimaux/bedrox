<?php

namespace Bedrox\Core\Interfaces;

interface iRouter
{
    /**
     * @param string $current
     * @return mixed
     */
    public function getCurrentRoute(string $current);
}