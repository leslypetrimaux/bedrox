<?php

namespace Bedrox\Core;

use Bedrox\Skeleton;

class Service
{
    public $_em;

    /**
     * Service constructor.
     */
    public function __construct()
    {
        $this->_em = Skeleton::$entityManager;
    }
}
