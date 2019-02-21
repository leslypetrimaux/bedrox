<?php

namespace Bedrox\Google\Firebase;

class Firebase
{
    protected $host;
    protected $apiKey;
    protected $oAuthToken;
    protected $type;

    /**
     * FirebaseDatabase constructor.
     *
     * @param string $host
     * @param string $apiKey
     * @param string $oAuthToken
     * @param string $type
     */
    public function __construct(string &$host, string $apiKey, string $oAuthToken, string $type = 'public')
    {
        $this->host = $host;
        $this->apiKey = $apiKey;
        $this->oAuthToken = $oAuthToken;
        $this->type = $type;
    }
}