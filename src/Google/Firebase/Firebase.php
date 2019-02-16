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

    public $auth;
    public $database;
    public $storage;
    public $messaging;

    /**
     * @param array $config
     */
    public function initializeApp(array &$config): void
    {
        $this->apiKey = $config['apiKey'];
        $this->authDomain = $config['authDomain'];
        $this->databaseURL = $config['databaseURL'];
        $this->projectId = $config['projectId'];
        $this->storageBucket = $config['storageBucket'];
        $this->messagingSenderId = $config['messagingSenderId'];
    }
}