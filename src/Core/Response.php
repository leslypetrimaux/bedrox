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
     * @param Render $data
     * @param array|null $error
     * @return null|string
     */
    public function renderView(string $format, Render $data, ?array $error): ?string
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
     * @param Render $data
     * @param array|null $error
     * @return string|null
     */
    public function renderJSON(Render $data, ?array $error): ?string
    {
        header('Content-Type: application/json; charset=' . $this->parsing->parseAppEncode());
        return json_encode(
            $this->renderResult($data, $error),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }

    /**
     * Render XML Format.
     *
     * @param Render $data
     * @param array|null $error
     * @return string|null
     */
    public function renderXML(Render $data, ?array $error): ?string
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
        return $domXml->saveXML();
    }

    /**
     * Render CSV Format.
     *
     * @param Render $data
     * @param array|null $error
     * @return string|null
     */
    public function renderCSV(Render $data, ?array $error): ?string
    {
        $result = $this->renderResult($data, $error);
        $result = $this->parsing->parseRecursiveToArray($result);
        $csv = $this->parsing->parseArrayToCsv($result);
        return $csv;
    }

    /**
     * Render result Array.
     *
     * @param Render $render
     * @param array|null $error
     * @return array|null
     */
    public function renderResult(Render $render, ?array $error): ?array
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
        if ($render instanceof Render) {
            $result['data'] = $render->getData();
        }
        if ($error) {
            $result['error'] = $error;
        }
        $this->session->unset('DUMPS');
        $this->clear();
        return $result;
    }

    /**
     * Clear _SESSION & all globals/variables
     */
    public function clear(): void
    {
        unset($_GET, $_POST, $_FILES);
        session_unset();
        session_destroy();
        setcookie(session_name(), '', -3600);
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
            // TODO: handle a different way to load controller (dependencies injection not allowed for now)
            $this->setResponse($response);
            $class = new $response->route->controller();
            $functionStr = $response->route->function;
            $diParams = array();
            $uriParams = array();
            try {
                $method = (new ReflectionClass($class))->getMethod($functionStr);
                $refParams = $method->getParameters();
                try {
                    foreach ($refParams as $refParam) {
                        $type = false;
                        if ($refParam->getClass() != null) {
                            $refClass = $refParam->getClass()->getName();
                            $tmpClass = new $refClass();
                        } else {
                            $refClass = $refParam->getName();
                            $tmpClass = gettype($refClass);
                        }
                        $usefull = !empty($tmpClass->_em);
                        if ($response->route->paramsCount > 0) {
                            if (!empty($response->route->params)) {
                                foreach ($response->route->params as $paramKey => $paramValue) {
                                    if (empty($paramValue)) {
                                        BedroxException::render(
                                            'ERR_URI_NOTFOUND_PARAMS',
                                            'The parameter does not exists. Please check your URI and/or "' . $response->route->name . '".',
                                            404
                                        );
                                    }
                                }
                                foreach ($response->route->params as $tmpParam) {
                                    $entity = $tmpClass instanceof $tmpParam;
                                    $type = gettype($tmpClass) === gettype($tmpParam);
                                    if (($entity && $type) || ($type && !$entity)) {
                                        if (empty($tmpClass->_em)) {
                                            array_push($uriParams, $tmpParam);
                                        }
                                    }
                                }
                            } else {
                                BedroxException::render(
                                    'ERR_URI_NOTFOUND_PARAMS',
                                    'The controller need a parameter that was not send. Please check your URI and/or "' . $response->route->name . '".',
                                    404
                                );
                            }
                        }
                        if (!$type && $usefull) {
                            array_push($diParams, $tmpClass);
                        }
                    }
                } catch (TypeError $e) {
                    BedroxException::render(
                        'ERR_URI_TYPE',
                        $e->getMessage()
                    );
                }
            } catch (ReflectionException $e) {
                BedroxException::render(
                    'ERR_CONTROLLER_PARAMS',
                    $e->getMessage()
                );
            }
            $params = array_merge($diParams, $uriParams);
            $function = call_user_func_array(array($class, $functionStr), $params);
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
