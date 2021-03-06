<?php

namespace Bedrox\Core;

use Bedrox\Core\Exceptions\BedroxException;
use Bedrox\Core\Interfaces\iResponse;
use Bedrox\Skeleton;
use DateTime;
use Doctrine\ORM\EntityManager;
use DOMDocument;
use Exception;
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

    private $request;
    private $route;

    /**
     * Render the view depending on the format
     *
     * @param string $format
     * @param Render $data
     * @param array|null $error
     * @return null|string
     */
    public function renderView(string $format, Render $data, ?array $error): ?string
    {
        $renderFormat = $data->getFormat();
        $renderForce = $data->getForce();
        $format = (($format !== $renderFormat) && ($renderForce !== false)) ? $renderFormat : $format;
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
        return $this->parsing->parseArrayToCsv($result);
    }

    /**
     * @return array|null
     */
    private function getExecInfos(): ?array
    {
        try {
            $execStart = $_SERVER[Headers::SRV_REQUEST_TIME_FLOAT];
            $execEnd = microtime(true);
            $execSec = $execEnd - $execStart;
            $execMin = $execSec / 60;
            $execMs = $execSec * 1000;
            $durationArray = array(
                'min' => $execMin,
                'sec' => $execSec,
                'ms' => $execMs
            );
            $dateStart = DateTime::createFromFormat('U.u', $execStart);
            $dateEnd = DateTime::createFromFormat('U.u', $execEnd);
            return array(
                'duration' => $durationArray,
                'timestamp' => array(
                    'start' => $execStart,
                    'end' => $execEnd,
                ),
                'datetime' => array(
                    'start' => $dateStart,
                    'end' => $dateEnd
                ),
            );
        } catch (Exception $e) {
            BedroxException::render(
                'ERR_EXEC_INFOS',
                $e->getMessage()
            );
        }
        return null;
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
            'statusCode' => http_response_code()
        );
        if ($render instanceof Render) {
            $result['data'] = $render->getData();
        }
        $execInfos = $this->getExecInfos();
        if (!is_null($execInfos)) {
            $result['exec'] = $execInfos;
        }
        if ($_SERVER[Env::APP][Env::DEBUG]) {
            if ($this->getDumps()) {
                $result['dumps'] = $this->getDumps();
                $result['error'] = array(
                    'code' => 'WARN_DUMPS',
                    'message' => 'Dumps are still in your code !'
                );
            }
            /** @var EntityManager $em */
            $em = Skeleton::$entityManager;
            if (!is_null($em)) {
                $logger = $em->getConfiguration()->getSQLLogger();
                $result['doctrine'] = $logger;
            }
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

    private function getRoute(): Route
    {
        return $this->route;
    }

    /**
     * Render the view depending on the route/controller/function.
     * Parameters defined in the Router file configuration.
     *
     * @param Response $response
     */
    public function terminate(Response $response): void
    {
        $funConstructor = Controller::CONSTRUCTOR;
        $route = $response->getRoute();
        $render = empty($route->getRender()) ? $this->session->get('APP_FORMAT') : $route->getRender();
        if ( !empty($route) && !empty($route->getUrl()) && !empty($route->getController()) && !empty($route->getFunction()) && !empty($render) ) {
            parent::setResponse($response);
            $controller = $route->getController();
            $class = new $controller;
            $this->handleDependencies($class, $funConstructor);
            $functionStr = $route->getFunction();
            $diParams = array();
            $uriParams = array();
            try {
                if (method_exists($class, $functionStr)) {
                    $method = (new ReflectionClass($class))->getMethod($functionStr);
                    $refParams = $method->getParameters();
                    try {
                        foreach ($refParams as $refParam) {
                            $type = false;
                            if (!is_null($refParam->getClass())) {
                                $refClass = $refParam->getClass()->getName();
                                $tmpClass = new $refClass;
                            } else {
                                $refClass = $refParam->getName();
                                $tmpClass = gettype($refClass);
                            }
                            $usefull = !empty($tmpClass->_em);
                            if (!empty($route->getParamsCount())) {
                                if (!empty($route->getParams())) {
                                    foreach ($route->getParams() as $paramKey => $paramValue) {
                                        if (empty($paramValue)) {
                                            BedroxException::render(
                                                'ERR_URI_NOTFOUND_PARAMS',
                                                'The parameter does not exists. Please check your URI and/or "' . $route->getName() . '".',
                                                404
                                            );
                                        }
                                    }
                                    foreach ($route->getParams() as $tmpParam) {
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
                                        'The controller need a parameter that was not send. Please check your URI and/or "' . $route->getName() . '".',
                                        404
                                    );
                                }
                            }
                            if (!$type && $usefull) {
                                /** @var Service $tmpClass */
                                $service = $tmpClass->getSelf();
                                $isService = (!empty($service) && $service === 'Bedrox\\Core\\Service') ? true : false;
                                if ($isService) {
                                    $this->handleDependencies($tmpClass, $funConstructor);
                                }
                                array_push($diParams, $tmpClass);
                            }
                        }
                    } catch (TypeError $e) {
                        BedroxException::render(
                            'ERR_URI_TYPE',
                            $e->getMessage()
                        );
                    }
                } else {
                    BedroxException::render(
                        'ERR_CONTROLLER_METHOD',
                        'The requested method "' . $functionStr . '" does not exists in your Controller "' . $controller . '".'
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
            exit($this->renderView($render, $function, null));
        }
        BedroxException::render(
            'ERR_URI_NOTFOUND',
            'Your URI "' . $_SERVER[Headers::SRV_REQUEST_URI] . '" does not exists or is not correctly configure. Please check "' . $_SERVER[Env::APP][Env::ROUTER] . '".',
            404
        );
    }

    /**
     * @param Route $route
     * @return Response
     */
    public function setRoute(Route $route): self
    {
        $this->route = $route;
        return $this;
    }

    /**
     * @return Request|null
     */
    public function getRequest(): ?Request
    {
        return $this->request;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function setRequest(Request $request): self
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @param $class
     * @param string $funConstructor
     */
    private function handleDependencies($class, string $funConstructor): void
    {
        // TODO: handle dependencies injections
        if (method_exists($class, $funConstructor)) {
            try {
                $method = (new ReflectionClass($class))->getMethod($funConstructor);
                $refParams = $method->getParameters();
                try {
                    $params = array();
                    $refClass = null;
                    foreach ($refParams as $refParam) {
                        if (!is_null($refParam->getClass())) {
                            $refClass = $refParam->getClass()->getName();
                        } else {
                            $refClass = $refParam->getName();
                        }
                        $refClass = new $refClass;
                        array_push($params, $refClass);
                        $parent = get_parent_class($refClass);
                        if ($parent !== false) {
                            $refParent = new $parent;
                            $this->handleDependencies($refParent, $funConstructor);
                        }
                        $this->handleDependencies($refClass, $funConstructor);
                    }
                    call_user_func_array(array($class, $funConstructor), $params);
                } catch (TypeError $e) {
                    BedroxException::render(
                        'ERR_DEPENDENCY_INJECTION_PARAMETERS',
                        $e->getMessage()
                    );
                }
            } catch (ReflectionException $e) {
                BedroxException::render(
                    'ERR_DEPENDENCY_INJECTION_CONSTRUCTOR',
                    $e->getMessage()
                );
            }
        }
    }
}
