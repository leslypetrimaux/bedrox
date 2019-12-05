<?php

namespace Bedrox\Core;

use Bedrox\Core\Exceptions\BedroxException;
use Bedrox\Core\Interfaces\iRequest;
use Bedrox\Security\Base;
use Bedrox\Skeleton;
use Exception;

class Request implements iRequest
{
    /** @var Headers $headers */
    private $headers;
    /** @var Route $route */
    private $route;
    private $files;
    private $get;
    private $post;

    /**
     * Create the Application request from PHP globals.
     *
     * @return Request
     */
    public static function createFromGlobals(): self
    {
        $request = new self;
        try {
            $request->get = !empty($_GET) ? self::xssFilter($_GET) : null;
            $request->post = !empty($_POST) ? self::xssFilter($_POST) : null;
            $request->files = !empty($_FILES) ? self::xssFilter($_FILES) : null;
            /** @noinspection PhpComposerExtensionStubsInspection */
            $headers = getallheaders();
            if (!empty($headers[Headers::X_RESPONSE_TYPE])) {
                $format = $request->parseResponseType($headers[Headers::X_RESPONSE_TYPE]);
                if (!empty($format) && !$request->getResponseType($format)) {
                    BedroxException::render(
                        'ERR_URI_FORMAT',
                        'Error while trying to access your output format. Please check your application configuration.'
                    );
                }
            } else {
                $format = null;
            }
            $request->headers = new Headers($headers);
            $urlRequested = empty($_SERVER[Headers::SRV_REDIRECT_URL]) ? empty($_SERVER[Headers::SRV_PATH_INFO]) ? Skeleton::BASE : $_SERVER[Headers::SRV_PATH_INFO] : $_SERVER[Headers::SRV_REDIRECT_URL];
            $request->setRoute((new Router())->getCurrentRoute($urlRequested, $format));
        } catch (Exception $e) {
            BedroxException::render(
                'ERR_GET_ROUTE',
                'Unable to access the requested route. Please check your application configuration.'
            );
        }
        return $request;
    }

    /**
     * Search for XSS vunerabilities (recursive)
     *
     * @param array $items
     * @return array
     */
    public static function xssFilter(array $items): array
    {
        $results = array();
        foreach ($items as $key => $value) {
            $results[htmlspecialchars($key, Base::REPLACE_FLAGS)] = is_array($value) ? self::xssFilter($value) : htmlspecialchars($value, Base::REPLACE_FLAGS);
        }
        return $results;
    }

    /**
     * Search Response type
     *
     * @param string $format
     * @return string|null
     */
    public function parseResponseType(string $format): ?string
    {
        if (in_array($format, Response::TYPE_JSON, true)) {
            return Response::FORMAT_JSON;
        }
        if (in_array($format, Response::TYPE_XML, true)) {
            return Response::FORMAT_XML;
        }
        return null;
    }

    /**
     * Check if the response type asked is valid
     *
     * @param string|null $format
     * @return bool
     */
    public function getResponseType(?string $format = null): bool
    {
        $format = is_null($format) ? $_SERVER[Env::APP][Env::FORMAT] : $format;
        return in_array($format, array(
            Response::FORMAT_JSON,
            Response::FORMAT_XML,
            Response::FORMAT_CSV
        ), true);
    }

    /**
     * Handle the user's Request and return the wanted Response.
     *
     * @param Request $request
     * @return Response
     */
    public function handle(Request $request): Response
    {
        $response = new Response();
        if (!is_null($request)) {
            $request->get = !empty($request->get) ? $request->get : null;
            $request->post = !empty($request->post) ? $request->post : null;
            $request->files = !empty($request->files) ? $request->files : null;
            /** @var Headers headers */
            $request->headers = !empty($request->getHeaders()) ? $request->getHeaders() : null;
            $response->setRoute($request->getRoute());
            if (empty($request->headers->getResponseType())) {
                $request->headers->setResponseType($request->route->getRender());
            }
            $response->setRequest($request);
        } else {
            BedroxException::render(
                'ERR_REQUEST',
                'An error occurs while trying to read the request.'
            );
        }
        return $response;
    }

    /**
     * @return Headers
     */
    public function getHeaders(): Headers
    {
        return $this->headers;
    }

    /**
     * @return mixed
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @return mixed
     */
    public function getGet()
    {
        return $this->get;
    }

    /**
     * @return mixed
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * @return Route|null
     */
    private function getRoute(): ?Route
    {
        return $this->route;
    }

    /**
     * @param Route $route
     * @return Request
     */
    private function setRoute(Route $route): Request
    {
        $this->route = $route;
        return $this;
    }
}
