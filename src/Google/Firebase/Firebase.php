<?php

namespace Bedrox\Google\Firebase;

class Firebase
{
    public $host;
    public $apiKey;

    /**
     * FirebaseDatabase constructor.
     *
     * @param string $host
     * @param string $apiKey
     */
    public function __construct(string &$host, string $apiKey)
    {
        $this->host = $host;
        $this->apiKey = $apiKey;
    }
}