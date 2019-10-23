<?php

namespace Bedrox\Core;

use Bedrox\Core\Exceptions\BedroxException;
use Bedrox\Core\Interfaces\iResponse;
use Bedrox\Skeleton;
use DOMDocument;
use ReflectionClass;
use ReflectionException;
use SimpleXMLElement;
use TypeError;

class Response extends Skeleton implements iResponse
{
    public const TYPE_JSON = array(
        'application/json',
        'text/json'
    );
    public const TYPE_XML = array(
        'application/xml',
        'text/xml'
    );

    public const FORMAT_JSON = 'json';
    public const FORMAT_XML = 'xml';
    public const FORMAT_CSV = 'csv';
    public const FORMAT_HTML = 'html';
    public const FORMAT_XLS = 'xls';
    public const FORMAT_XLSX = 'xlsx';

    public $request;
    public $route;

    /**
     * @param string $format
     * @param array $data
     * @param array $error
     * @return null|string
     */
    public function renderView(string $format, ?array $data, ?array $error): ?string
    {
        switch ($format) {
            case self::FORMAT_JSON:
                return $this->renderJSON($data, $error);
            case self::FORMAT_XML:
                return $this->renderXML($data, $error);
            case self::FORMAT_CSV:
                // return $this->renderCSV($data, $error);
            default:
                return $this->renderJSON(null, array(
                    'code' => 'ERR_OUTPUT',
                    'message' => 'The application output format is not specified. Please check "config/env.yaml".'
                ));
        }
    }

    /**
     * Render JSON Format.
     *
     * @param array $data
     * @param array $error
     * @return string|null
     */
    public function renderJSON(?array $data, ?array $error): ?string
    {
        header('Content-Type: application/json; charset=' . $this->parsing->parseAppEncode());
        $this->clear();
        return json_encode(
            $this->renderResult($data, $error),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * Render XML Format.
     *
     * @param array $data
     * @param array $error
     * @return string|null
     */
    public function renderXML(?array $data, ?array $error): ?string
    {
        $result = $this->renderResult($data, $error);
        $xml = new SimpleXMLElement('<Response></Response>');
        $result = $this->parsing->parseRecursiveToArray($result);
        $xml = $this->parsing->parseArrayToXml($result, $xml);
        $domXml = new DOMDocument('1.0', $this->parsing->parseAppEncode());
        $domXml->preserveWhiteSpace = false;
        $domXml->formatOutput = true;
        $domXml->loadXML($xml->asXML());
        header('Content-Type: application/xml; charset=' . $this->parsing->parseAppEncode());
        $this->clear();
        return $domXml->saveXML();
    }

    /**
     * Render CSV Format.
     *
     * @param array|null $data
     * @param array|null $error
     * @return string|null
     */
    public function renderCSV(?array $data, ?array $error): ?string
    {
        $result = $this->renderResult($data, $error);
        $result = $this->parsing->parseRecursiveToArray($result);
        $csv = $this->parsing->parseArrayToCsv($result);
        $this->clear();
        return $csv;
    }

    /**
     * Render result Array.
     *
     * @param array $data
     * @param array $error
     * @return array|null
     */
    public function renderResult(?array $data, ?array $error): ?array
    {
        $result = array(
            'status' => !is_array($error) ? 'success' : 'error',
            'statusCode' => http_response_code(),
            'execTime' => round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2)
        );
        if ($this->getDumps()) {
            $result['dumps'] = $this->getDumps();
            $result['error'] = array(
                'code' => 'WARN_DUMPS',
                'message' => 'Dumps are still in your code !'
            );
        }
        if ($data) {
            $result['data'] = $data;
        }
        if ($error) {
            $result['error'] = $error;
        }
        $this->session->unset('DUMPS');
        return $result;
    }

    /**
     * Clear _SESSION & all globals/variables
     */
    public function clear(): void
    {
        unset($_GET, $_POST, $_FILES);
    }

    /**
     * Render the view depending on the route/controller/function.
     * Parameters defined in the Router file configuration.
     *
     * @param Response $response
     */
    public function terminate(Response $response): void
    {
        $render = empty($response->route->render) ? $this->session->get('APP_FORMAT') : $response->route->render;
        if ( !empty($response->route) && !empty($response->route->url) && !empty($response->route->controller) && !empty($response->route->function) && !empty($render) ) {
            $class = new $response->route->controller($response);
            $functionStr = $response->route->function;
            if ($response->route->paramsCount > 0) {
                if (!empty($response->route->params)) {
                    try {
                        foreach ($response->route->params as $paramKey => $paramValue) {
                            if (empty($paramValue)) {
                                BedroxException::render(
                                    'ERR_URI_NOTFOUND_PARAMS',
                                    'The parameter does not exists. Please check your URI and/or "' . $response->route->name . '".',
                                    404
                                );
                            }
                        }
                        $method = (new ReflectionClass($class))->getMethod($functionStr);
                        if (count($method->getParameters()) === count($response->route->params)) {
                            try {
                                $function = call_user_func_array(array($class, $functionStr), $response->route->params);
                            } catch (TypeError $e) {
                                BedroxException::render(
                                    'ERR_URI_TYPE',
                                    'The URI parameter does not match the controller type. Please check your router or controller.'
                                );
                            }
                        } else {
                            throw new ReflectionException('The number of parameter does not match. Please check "' . $_SERVER['APP'][Env::ROUTER] . '" or your URI.');
                        }
                    } catch (ReflectionException $e) {
                        BedroxException::render(
                            'ERR_URI_PARAMS',
                            $e->getMessage()
                        );
                    }
                } else {
                    BedroxException::render(
                        'ERR_URI_NOTFOUND_PARAMS',
                        'The controller need a parameter that was not send. Please check your URI and/or "' . $response->route->name . '".',
                        404
                    );
                }
            } else {
                $function = $class->$functionStr();
            }
            http_response_code(200);
            /** @var mixed $function */
            exit($this->renderView($render, $function, null));
        }
        BedroxException::render(
            'ERR_URI_NOTFOUND',
            'Your URI "' . $_SERVER['REQUEST_URI'] . '" does not exists or is not correctly configure. Please check "' . $_SERVER['APP'][Env::ROUTER] . '".',
            404
        );
    }
}
