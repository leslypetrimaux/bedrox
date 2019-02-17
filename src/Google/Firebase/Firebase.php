<?php

namespace Bedrox\Google\Firebase;

class Firebase
{
    public $config;

    /**
     * FirebaseDatabase constructor.
     *
     * @param array $config
     */
    public function __construct(array &$config)
    {
        $this->config = $config;
    }
}