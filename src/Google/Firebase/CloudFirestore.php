<?php

namespace Bedrox\Google\Firebase;

use Bedrox\Core\Response;
use Exception;

class CloudFirestore extends Firebase
{
    private $baseURI;
    private $documentURI;
    private $timeout;
    private $token;
    private $curlHandler;
    private $sslConnection;

    public $collections;
    public $collection;
    public $documents;
    public $document;

    /**
     * CloudFirestore constructor.
     *
     * @param string $host
     * @param string $apiKey
     * @param string $oAuthToken
     * @param string $type
     */
    public function __construct(string &$host, string $apiKey, string $oAuthToken, string $type = 'public')
    {
        parent::__construct($host, $apiKey, $oAuthToken, $type);
        $this->setBaseURI($this->host);
        $this->setTimeOut(10);
        if ($type !== 'public') {
            $this->setToken($this->oAuthToken);
        }
        $this->initCurlHandler();
        $this->setSSLConnection(false);
    }

    /**
     * @param string $uri
     * @return CloudFirestore
     */
    public function setBaseURI(string $uri): self
    {
        $uri = 'https://firestore.googleapis.com/v1/projects/' . $uri . '/databases/(default)/documents/';
        $this->baseURI = $uri;
        return $this;
    }

    /**
     * @param int $seconds
     * @return CloudFirestore
     */
    public function setTimeOut(int $seconds): self
    {
        $this->timeout = $seconds;
        return $this;
    }

    /**
     * @param string $token
     * @return CloudFirestore
     */
    public function setToken(string $token): self
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @return CloudFirestore
     */
    public function initCurlHandler(): self
    {
        $this->curlHandler = curl_init();
        return $this;
    }

    /**
     * @return CloudFirestore
     */
    public function closeCurlHandler(): self
    {
        curl_close($this->curlHandler);
        $this->curlHandler = null;
        return $this;
    }

    /**
     * @return bool
     */
    public function getSSLConnection(): bool
    {
        return $this->sslConnection;
    }

    /**
     * @param bool $enableSSLConnection
     * @return CloudFirestore
     */
    public function setSSLConnection(bool $enableSSLConnection): self
    {
        $this->sslConnection = $enableSSLConnection;
        return $this;
    }

    /**
     * @param string $path
     * @return string
     */
    private function getJsonPath(string $path): string
    {
        $url = $this->baseURI;
        $path .= (substr($path, -1) === '/' ? '' : '/');
        $this->documentURI = str_replace('https://firestore.googleapis.com/v1/', '', $url . $path);
        return $url . $path;
    }

    /**
     * @param string $path
     * @param string $mode
     * @return mixed
     */
    private function getCurlHandler(string $path, string $mode)
    {
        $url = $this->getJsonPath($path);
        $ch = $this->curlHandler;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->getSSLConnection());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $mode);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        return $ch;
    }

    private function writeData(string $path, string $data, string $method = 'PATCH')
    {
        // TODO: Implement writeData() method.
    }

    /**
     * @param string $path
     * @return array|null
     */
    public function get(string $path): ?array
    {
        try {
            $ch = $this->getCurlHandler($path, 'GET');
            $db = json_decode(curl_exec($ch));
            $docs = $doc = null;
        } catch (Exception $e) {
            http_response_code(500);
            exit((new Response())->renderView($_SERVER['APP']['FORMAT'], null, array(
                'code' => 'ERR_FIREBASE_GET:' . $e->getCode(),
                'message' => $e->getMessage()
            )));
        }
        if (!empty($db->documents)) {
            $docs = array();
            foreach ($db->documents as $document) {
                $doc = array();
                foreach ($document->fields as $key => $value) {
                    $doc[$key] = $value->stringValue;
                }
                $docs[] = $doc;
            }
        } else {
            $doc = array();
            foreach ($db->fields as $key => $value) {
                $doc[$key] = $value->stringValue;
            }
        }
        return $docs ?? $doc;
    }

    public function patch(string $path, string $data)
    {
        return $this->writeData($path, $data);
    }

    public function unset(string $path)
    {
        // TODO: Implement del() method.
    }
}