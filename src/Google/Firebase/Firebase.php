<?php

namespace Bedrox\Google\Firebase;

class Firebase
{
    protected $apiKey;
    protected $authDomain;
    protected $databaseURL;
    protected $projectId;
    protected $storageBucket;
    protected $messagingSenderId;

    /**
     * Firebase constructor.
     *
     * @param array $config
     */
    public function __construct(array &$config)
    {
        $this->apiKey = $config['apiKey'];
        $this->authDomain = $config['authDomain'];
        $this->databaseURL = $config['databaseURL'];
        $this->projectId = $config['projectId'];
        $this->storageBucket = $config['storageBucket'];
        $this->messagingSenderId = $config['messagingSenderId'];
    }
}