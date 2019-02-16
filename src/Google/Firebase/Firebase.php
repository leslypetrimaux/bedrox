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

    /**
     * @param array $config
     * @return bool
     */
    public function connect(array $config): bool
    {
        // TODO: implements connexion to firebase core
        return false;
    }
}