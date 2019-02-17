<?php

namespace Bedrox\Google\Firebase;

use Exception;

class RealtimeDatabase extends Firebase
{
    private $baseURI;
    private $timeout;
    private $token;
    private $curlHandler;
    private $sslConnection;

    /**
     * RealtimeDatabase constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->setBaseURI($this->config['databaseURL']);
        $this->setTimeOut(10);
        $this->setToken($this->config['apiKey']);
        $this->initCurlHandler();
        $this->setSSLConnection(false);
    }

    /**
     * @param string $uri
     * @return RealtimeDatabase
     */
    public function setBaseURI(string $uri): self
    {
        $uri .= (substr($uri, -1) === '/' ? '' : '/');
        $this->baseURI = $uri;
        return $this;
    }

    /**
     * @param int $seconds
     * @return RealtimeDatabase
     */
    public function setTimeOut(int $seconds): self
    {
        $this->timeout = $seconds;
        return $this;
    }

    /**
     * @param string $token
     * @return RealtimeDatabase
     */
    public function setToken(string $token): self
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @return RealtimeDatabase
     */
    public function initCurlHandler(): self
    {
        $this->curlHandler = curl_init();
        return $this;
    }

    /**
     * @return RealtimeDatabase
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
     * @return RealtimeDatabase
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
        $path = ltrim($path, '/');
        return $url . $path . '.json';
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
            $return = null;
        }
        return $return;
    }

    /**
     * @param string $path
     * @return bool|string|null
     */
    public function get(string $path)
    {
        try {
            $ch = $this->getCurlHandler($path, 'GET');
            $return = curl_exec($ch);
        } catch (Exception $e) {
            $return = null;
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
            $return = null;
        }
        return $return;
    }
}