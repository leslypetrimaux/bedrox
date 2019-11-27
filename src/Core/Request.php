<?php

namespace Bedrox\Core;

use Bedrox\Core\Exceptions\BedroxException;
use Bedrox\Core\Interfaces\iRequest;
use Bedrox\Security\Base;
use Bedrox\Skeleton;
use Exception;

/**
 * @property  route
 * @property Route|null route
 */
class Request implements iRequest
{
    protected const X_RESPONSE_TYPE = 'X-Response-Type';
    protected const HTTP_REQUEST_METHOD = 'Request-Method';
    protected const HTTP_CONTENT_TYPE = 'Content-Type';
    protected const HTTP_USER_AGENT = 'User-Agent';
    protected const HTTP_ACCEPT = 'Accept';
    protected const HTTP_CACHE_CONTROL = 'Cache-Control';
    protected const HTTP_COOKIE = 'Cookie';
    protected const HTTP_CONNECTION = 'Connection';

    public $headers;
    public $files;
    public $get;
    public $post;

    /**
     * Create the Application request from PHP globals.
     *
     * @return Request
     */
    public static function createFromGlobals(): self
    {
        $request = new self();
        try {
            $request->get = !empty($_GET) ? self::xssFilter($_GET) : null;
            $request->post = !empty($_POST) ? self::xssFilter($_POST) : null;
            $request->files = !empty($_FILES) ? self::xssFilter($_FILES) : null;
            /** @noinspection PhpComposerExtensionStubsInspection */
            $headers = getallheaders();
            if (!empty($headers[self::X_RESPONSE_TYPE])) {
                $format = $request->parseResponseType($headers[self::X_RESPONSE_TYPE]);
                if (!empty($format) && !$request->getResponseType($format)) {
                    BedroxException::render(
                        'ERR_URI_FORMAT',
                        'Error while trying to access your output format. Please check your application configuration.'
                    );
                }
            } else {
                $format = null;
            }
            $request->headers = array(
                self::HTTP_REQUEST_METHOD => !empty($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : null,
                self::X_RESPONSE_TYPE => !empty($format) ? $format : null,
                self::HTTP_CONTENT_TYPE => !empty($headers[self::HTTP_CONTENT_TYPE]) ? $headers[self::HTTP_CONTENT_TYPE] : null,
                self::HTTP_USER_AGENT => !empty($headers[self::HTTP_USER_AGENT]) ? $headers[self::HTTP_USER_AGENT] : null,
                self::HTTP_ACCEPT => !empty($headers[self::HTTP_ACCEPT]) ? $headers[self::HTTP_ACCEPT] : null,
                self::HTTP_CACHE_CONTROL => !empty($headers[self::HTTP_CACHE_CONTROL]) ? $headers[self::HTTP_CACHE_CONTROL] : null,
                self::HTTP_COOKIE => !empty($headers[self::HTTP_COOKIE]) ? $headers[self::HTTP_COOKIE] : null,
                self::HTTP_CONNECTION => !empty($headers[self::HTTP_CONNECTION]) ? $headers[self::HTTP_CONNECTION] : null
            );
            $request->route = (new Router())->getCurrentRoute(!empty($_SERVER['REDIRECT_URL']) ? $_SERVER['REDIRECT_URL'] : Skeleton::BASE, $format);
        } catch (Exception $e) {
            BedroxException::render(
                'ERR_GET_ROUTE',
                'Unable to access the requested route. Please check your application configuration.'
            );
        }
        return $request;
    }

    /**
     * Search for XSS vunerabilities
     *
     * @param array $items
     * @return array
     */
    public static function xssFilter(array $items): array
    {
        $results = array();
        foreach ($items as $key => $value) {
            $results[htmlspecialchars($key, Base::REPLACE_FLAGS)] = htmlspecialchars($value, Base::REPLACE_FLAGS);
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
     * @param string $format
     * @return bool
     */
    public function getResponseType(string $format): bool
    {
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
        if ($request !== null) {
            $response->request = new self();
            $response->request->get = !empty($request->get) ? $request->get : null;
            $response->request->post = !empty($request->post) ? $request->post : null;
            $response->request->files = !empty($request->files) ? $request->files : null;
            $response->request->headers = !empty($request->headers) ? $request->headers : null;
            if (empty($response->request->headers[self::X_RESPONSE_TYPE])) {
                $response->request->headers[self::X_RESPONSE_TYPE] = $request->route->getRender();
            }
            $response->route = $request->route;
        } else {
            BedroxException::render(
                'ERR_REQUEST',
                'An error occurs while trying to read the request.'
            );
        }
        return $response;
    }
}
