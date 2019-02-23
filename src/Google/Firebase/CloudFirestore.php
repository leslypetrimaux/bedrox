<?php

namespace Bedrox\Google\Firebase;

use Bedrox\Core\Response;
use Exception;

class CloudFirestore extends Firebase
{
    /**
     * CloudFirestore constructor.
     *
     * @param string $host
     * @param string $apiKey
     * @param string $clientId
     * @param string $oAuthToken
     * @param string $type
     */
    public function __construct(string &$host, string $apiKey, string $clientId, string $oAuthToken, string $type = 'public')
    {
        parent::__construct($host, $apiKey, $clientId, $oAuthToken, $type);
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
     * @return bool|string
     */
    private function writeData(string $path, string $data, string $method = 'PATCH')
    {
        try {
            $tmp = json_decode($data);
            $doc = array();
            $doc['name'] = $this->getJsonPath($path);
            foreach ($tmp as $key => $value) {
                $doc['fields'][$key]['stringValue'] = $value;
            }
            $data = json_encode($doc);
            $header = array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data)
            );
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

    /**
     * @param string $path
     * @param string $data
     * @return bool|string
     */
    public function patch(string $path, string $data)
    {
        return $this->writeData($path, $data);
    }

    /**
     * @param string $path
     * @return bool|string
     */
    public function unset(string $path)
    {
        try {
            $ch = $this->getCurlHandler($path, 'DELETE');
            $return = curl_exec($ch);
        } catch (Exception $e) {
            http_response_code(500);
            exit((new Response())->renderView($_SERVER['APP']['FORMAT'], null, array(
                'code' => 'ERR_FIRESTORE_DELETE:' . $e->getCode(),
                'message' => $e->getMessage()
            )));
        }
        return $return;
    }
}