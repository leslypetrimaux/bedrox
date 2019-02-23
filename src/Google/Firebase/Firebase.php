<?php

namespace Bedrox\Google\Firebase;

class Firebase
{
    protected $host;
    protected $apiKey;
    protected $clientId;
    protected $oAuthToken;
    protected $type;

    protected $baseURI;
    protected $timeout;
    protected $token;
    protected $curlHandler;
    protected $sslConnection;

    /**
     * FirebaseDatabase constructor.
     *
     * @param string $host
     * @param string $apiKey
     * @param string $clientId
     * @param string $oAuthToken
     * @param string $type
     */
    public function __construct(string &$host, string $apiKey, string $clientId, string $oAuthToken, string $type = 'public')
    {
        $this->host = $host;
        $this->apiKey = $apiKey;
        $this->clientId = $clientId;
        $this->oAuthToken = $oAuthToken;
        $this->type = $type;
    }
}