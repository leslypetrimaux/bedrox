<?php

namespace Bedrox\Google\Firebase;

use Bedrox\Core\Response;
use Bedrox\Google\Firebase\Firestore\Collection;
use Bedrox\Google\Firebase\Firestore\Collections;
use Bedrox\Google\Firebase\Firestore\Document;
use Bedrox\Google\Firebase\Firestore\Documents;
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
     * @param string $host
     * @param string $apiKey
     */
    public function __construct(string $host, string $apiKey)
    {
        parent::__construct($host, $apiKey);
        $this->setBaseURI($this->host);
        $this->setTimeOut(10);
        $this->setToken($this->apiKey);
        $this->initCurlHandler();
        $this->setSSLConnection(false);
        $this->collections = new Collections();
        $this->collection = new Collection();
        $this->documents = new Documents();
        $this->document = new Document();
        // https://firestore.googleapis.com/v1/projects/bedrox-php/databases/(default)/documents/users
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

    /**
     * @param string $path
     * @param string $data
     * @param string $method
     * @return bool|string|null
     */
    private function writeData(string $path, string $data, string $method = 'PATCH')
    {
        $header = array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data)
        );
        try {
            $ch = $this->getCurlHandler($path, $method);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            $return = curl_exec($ch);
        } catch (Exception $e) {
            http_response_code(500);
            exit((new Response())->renderView($_SERVER['APP']['FORMAT'], null, array(
                'code' => 'ERR_FIREBASE_PERSIST:' . $e->getCode(),
                'message' => $e->getMessage()
            )));
        }
        return $return;
    }

    /**
     * @param string $path
     * @return bool|array|null
     */
    public function get(string $path)
    {
        try {
            $ch = $this->getCurlHandler($path, 'GET');
            $db = json_decode(curl_exec($ch));
        } catch (Exception $e) {
            http_response_code(500);
            exit((new Response())->renderView($_SERVER['APP']['FORMAT'], null, array(
                'code' => 'ERR_FIREBASE_GET:' . $e->getCode(),
                'message' => $e->getMessage()
            )));
        }
        $return = array();
        foreach ($db->documents as $document) {
            $doc = array();
            foreach ($document->fields as $key => $value) {
                $doc[$key] = $value->stringValue;
            }
            $return[] = $doc;
        }
        return $return;
    }

    /**
     * @param string $path
     * @param string $data
     * @return bool|string|null
     */
    public function patch(string $path, string $data)
    {
        return $this->writeData($path, $data);
    }

    public function del(string $path)
    {
        try {
            $ch = $this->getCurlHandler($path, 'DELETE');
            $return = curl_exec($ch);
        } catch (Exception $e) {
            http_response_code(500);
            exit((new Response())->renderView($_SERVER['APP']['FORMAT'], null, array(
                'code' => 'ERR_FIREBASE_DELETE:' . $e->getCode(),
                'message' => $e->getMessage()
            )));
        }
        return $return;
    }
}