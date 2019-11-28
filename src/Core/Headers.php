<?php


namespace Bedrox\Core;

class Headers
{
    public const X_RESPONSE_TYPE = 'X-Response-Type';
    public const HTTP_CONTENT_TYPE = 'Content-Type';
    public const HTTP_USER_AGENT = 'User-Agent';
    public const HTTP_ACCEPT = 'Accept';
    public const HTTP_CACHE_CONTROL = 'Cache-Control';
    public const HTTP_COOKIE = 'Cookie';
    public const HTTP_CONNECTION = 'Connection';
    public const HTTP_HOST = 'Host';
    public const HTTP_UPGRADE_INSECURE_REQUESTS = 'Upgrade-Insecure-Requests';
    public const HTTP_ACCEPT_ENCODING = 'Accept-Encoding';
    public const HTTP_ACCEPT_LANGUAGE = 'Accept-Language';
    public const SEC_FETCH_USER = 'Sec-Fetch-User';
    public const SEC_FETCH_SITE = 'Sec-Fetch-Site';
    public const SEC_FETCH_MODE = 'Sec-Fetch-Mode';

    private $responseType;
    private $requestMethod;
    private $contentType;
    private $userAgent;
    private $accept;
    private $cacheControl;
    private $cookie;
    private $connection;
    private $host;
    private $upgradeInsecureRequests;
    private $acceptEncoding;
    private $acceptLanguage;
    private $fetchUser;
    private $fetchSite;
    private $fetchMode;
    private $isGet;
    private $isPost;
    private $isPut;
    private $isPatch;
    private $isDelete;
    private $isLink;
    private $isUnlink;
    private $isLock;
    private $isUnlock;

    public function __construct(array $headers)
    {
        $this->host = !empty($headers[self::HTTP_HOST]) ? $headers[self::HTTP_HOST] : !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null;
        $this->connection = !empty($headers[self::HTTP_CONNECTION]) ? $headers[self::HTTP_CONNECTION] : null;
        $this->upgradeInsecureRequests = !empty($headers[self::HTTP_UPGRADE_INSECURE_REQUESTS]) ? $headers[self::HTTP_UPGRADE_INSECURE_REQUESTS] : !empty($_SERVER['HTTP_UPGRADE_INSECURE_REQUESTS']) ? $_SERVER['HTTP_UPGRADE_INSECURE_REQUESTS'] : null;
        $this->cacheControl = !empty($headers[self::HTTP_CACHE_CONTROL]) ? $headers[self::HTTP_CACHE_CONTROL] : !empty($_SERVER['HTTP_CACHE_CONTROL']) ? $_SERVER['HTTP_CACHE_CONTROL'] : null;
        $this->accept = !empty($headers[self::HTTP_ACCEPT]) ? $headers[self::HTTP_ACCEPT] : !empty($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : null;
        $this->acceptEncoding = !empty($headers[self::HTTP_ACCEPT_ENCODING]) ? $headers[self::HTTP_ACCEPT_ENCODING] : !empty($_SERVER['HTTP_ACCEPT_ENCODING']) ? $_SERVER['HTTP_ACCEPT_ENCODING'] : null;
        $this->acceptLanguage = !empty($headers[self::HTTP_ACCEPT_LANGUAGE]) ? $headers[self::HTTP_ACCEPT_LANGUAGE] : !empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : null;
        $this->requestMethod = !empty($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : null;
        $this->responseType = !empty($format) ? $format : null;
        $this->contentType = !empty($headers[self::HTTP_CONTENT_TYPE]) ? $headers[self::HTTP_CONTENT_TYPE] : null;
        $this->userAgent = !empty($headers[self::HTTP_USER_AGENT]) ? $headers[self::HTTP_USER_AGENT] : !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
        $this->cookie = !empty($headers[self::HTTP_COOKIE]) ? $headers[self::HTTP_COOKIE] : null;
        $this->fetchUser = !empty($headers[self::SEC_FETCH_USER]) ? $headers[self::SEC_FETCH_USER] : null;
        $this->fetchSite = !empty($headers[self::SEC_FETCH_SITE]) ? $headers[self::SEC_FETCH_SITE] : null;
        $this->fetchMode = !empty($headers[self::SEC_FETCH_MODE]) ? $headers[self::SEC_FETCH_MODE] : null;
        $this->isGet = ($_SERVER['REQUEST_METHOD'] === 'GET') ? true : false;
        $this->isPost = ($_SERVER['REQUEST_METHOD'] === 'POST') ? true : false;
        $this->isPut = ($_SERVER['REQUEST_METHOD'] === 'PUT') ? true : false;
        $this->isPatch = ($_SERVER['REQUEST_METHOD'] === 'PATCH') ? true : false;
        $this->isDelete = ($_SERVER['REQUEST_METHOD'] === 'DELETE') ? true : false;
        $this->isLink = ($_SERVER['REQUEST_METHOD'] === 'LINK') ? true : false;
        $this->isUnlink = ($_SERVER['REQUEST_METHOD'] === 'UNLINK') ? true : false;
        $this->isLock = ($_SERVER['REQUEST_METHOD'] === 'LOCK') ? true : false;
        $this->isUnlock = ($_SERVER['REQUEST_METHOD'] === 'UNLOCK') ? true : false;
    }

    /**
     * @return string|null
     */
    public function getResponseType(): ?string
    {
        return $this->responseType;
    }

    /**
     * @param null $responseType
     * @return Headers
     */
    public function setResponseType($responseType)
    {
        $this->responseType = $responseType;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRequestMethod(): ?string
    {
        return $this->requestMethod;
    }

    /**
     * @return string|null
     */
    public function getContentType(): ?string
    {
        return $this->contentType;
    }

    /**
     * @return string|null
     */
    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    /**
     * @return string|null
     */
    public function getAccept(): ?string
    {
        return $this->accept;
    }

    /**
     * @return string|null
     */
    public function getCacheControl(): ?string
    {
        return $this->cacheControl;
    }

    /**
     * @return string|null
     */
    public function getCookie(): ?string
    {
        return $this->cookie;
    }

    /**
     * @return string|null
     */
    public function getConnection(): ?string
    {
        return $this->connection;
    }

    /**
     * @return string|null
     */
    public function getHost(): ?string
    {
        return $this->host;
    }

    /**
     * @return string|null
     */
    public function getUpgradeInsecureRequests(): ?string
    {
        return $this->upgradeInsecureRequests;
    }

    /**
     * @return string|null
     */
    public function getAcceptEncoding(): ?string
    {
        return $this->acceptEncoding;
    }

    /**
     * @return string|null
     */
    public function getAcceptLanguage(): ?string
    {
        return $this->acceptLanguage;
    }

    /**
     * @return string|null
     */
    public function getFetchUser(): ?string
    {
        return $this->fetchUser;
    }

    /**
     * @return string|null
     */
    public function getFetchSite(): ?string
    {
        return $this->fetchSite;
    }

    /**
     * @return string|null
     */
    public function getFetchMode(): ?string
    {
        return $this->fetchMode;
    }

    /**
     * @return mixed
     */
    public function getIsGet()
    {
        return $this->isGet;
    }

    /**
     * @return mixed
     */
    public function getIsPost()
    {
        return $this->isPost;
    }

    /**
     * @return mixed
     */
    public function getIsPut()
    {
        return $this->isPut;
    }

    /**
     * @return mixed
     */
    public function getIsPatch()
    {
        return $this->isPatch;
    }

    /**
     * @return mixed
     */
    public function getIsDelete()
    {
        return $this->isDelete;
    }

    /**
     * @return mixed
     */
    public function getIsLink()
    {
        return $this->isLink;
    }

    /**
     * @return mixed
     */
    public function getIsUnlink()
    {
        return $this->isUnlink;
    }

    /**
     * @return mixed
     */
    public function getIsLock()
    {
        return $this->isLock;
    }

    /**
     * @return mixed
     */
    public function getIsUnlock()
    {
        return $this->isUnlock;
    }
}
