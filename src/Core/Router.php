<?php

namespace Bedrox\Core;

use Bedrox\Core\Exceptions\BedroxException;
use Bedrox\Core\Interfaces\iRouter;
use Bedrox\EDR\EntityManager;
use Bedrox\Skeleton;
use Bedrox\Yaml\YamlParser;
use Bedrox\Security\Firewall;
use DateTime;
use Exception;
use RuntimeException;

class Router extends Skeleton implements iRouter
{
    protected $firewall;
    protected $routes;

    public const ROUTE_PATH = 'path';
    public const REQUIRE_ROUTER = 'require';

    public const ARG_STRING = '[string]';
    public const ARG_NUM = '[num]';
    public const ARG_DATE = '[date]';
    public const ARG_BOOL = '[bool]';

    /**
     * Router constructor.
     * Load Router configuration file.
     */
    public function __construct()
    {
        try {
            parent::__construct();
            $this->firewall = new Firewall();
            if (file_exists($_SERVER['APP'][Env::ROUTER])) {
                $router = YamlParser::YAMLLoad($_SERVER['APP'][Env::ROUTER]);
                if (is_array($router)) {
                    $this->routes = $this->parseRouter($router);
                } else {
                    throw new RuntimeException('Unable to access your router. Please check "' . $_SERVER['APP'][Env::ROUTER] . '".');
                }
            } else {
                throw new RuntimeException('An error occurs while opening your router. Please check "' . $_SERVER['APP'][Env::ROUTER] . '".');
            }
        } catch (RuntimeException $e) {
            BedroxException::render(
                'ERR_FILE_ROUTER',
                $e->getMessage()
            );
        }
    }

    /**
     * @param array $router
     * @return array|null
     */
    protected function parseRouter(array $router): ?array
    {
        foreach ($router as $route) {
            $subPath = $route[self::ROUTE_PATH];
            foreach ($route as $key => $value) {
                if ($key === self::REQUIRE_ROUTER) {
                    $router = $this->parseRecursiveRouter($router, $subPath, $value);
                }
            }
        }
        return $router;
    }

    protected function parseRecursiveRouter(array $router, string $subPath, string $value): ?array
    {
        $valuePath = realpath(dirname($_SERVER['APP'][Env::ROUTER]) . DIRECTORY_SEPARATOR . $value . '.yaml');
        $subRouter = YamlParser::YAMLLoad($valuePath);
        foreach ($subRouter as $subKey => $subValue) {
            foreach ($subValue as $subRouteKey => $subRouteValue) {
                if ($subRouteKey === self::ROUTE_PATH) {
                    $subValue[$subRouteKey] = $subPath . $subRouteValue;
                }
                $router[$subKey] = $subValue;
                if ($subRouteKey === self::REQUIRE_ROUTER) {
                    $router = $this->parseRecursiveRouter($router, $subValue[self::ROUTE_PATH], $subRouteValue);
                }
            }
        }
        return $router;
    }

    /**
     * Return the requested route (if exists).
     *
     * @param string $current
     * @param string|null $format
     * @return Route|null
     * @throws Exception
     */
    public function getCurrentRoute(string $current, ?string $format = null): ?Route
    {
        $class = null;
        $cRoute = explode('.', $current);
        $tmpRequest = new Request;
        if (empty($format) && !empty(end($cRoute)) && $tmpRequest->getResponseType(end($cRoute))) {
            $format = end($cRoute);
            $current = str_replace(
                array(
                    '.' . Response::FORMAT_XML,
                    '.' . Response::FORMAT_JSON
                ),
                '',
                $current
            );
            $this->session->set('URI_FORMAT', $format);
        }
        if (!empty($format) && !$tmpRequest->getResponseType($format)) {
            BedroxException::render(
                'ERR_URI_FORMAT',
                'Error while trying to retrieve your page format. Please check your configuration.'
            );
        }
        $route = $this->getRouteFromUri($current);
        $route->setRender(!empty($format) ? $format : $_SERVER['APP']['FORMAT']);
        return $route;
    }

    /**
     * @param string $name
     * @param array $data
     * @param Route $route
     * @return Route|null
     */
    private function setCurrentRoute(string $name, array $data, Route $route): ?Route
    {
        $controller = explode('::', $data['controller']);
        $route->setName($name);
        $route->setUrl($data['path']);
        $route->setController($controller[0]);
        $route->setFunction($controller[1]);
        if ($this->firewall->isNotAuthorized($route->getName(), $this->firewall->getFirewall())) {
            BedroxException::render(
                'ERR_URI_DENIED_ACCESS',
                'You don\'t have access to this section. Please check your token or URI.',
                403
            );
        }
        return $route;
    }

    /**
     * @param string $current
     * @return Route|null
     * @throws Exception
     */
    private function getRouteFromUri(string $current): ?Route
    {
        $obj = new Route;
        foreach ($this->routes as $name => $route) {
            $uriFullPath = $current === $route['path'];
            $uriParams = !empty($route['params']) ? true : false;
            $uriController = !empty($route['controller']) ? true : false;
            if ( $uriFullPath && !$uriParams && $uriController ) {
                $obj = $this->setCurrentRoute($name, $route, $obj);
            } else {
                $currentArray = explode('/', $current);
                $currentCount = count($currentArray);
                $pathArray = explode('/', $route['path']);
                $pathCount = count($pathArray);
                $keys = array();
                if (!empty($route['entity'])) {
                    foreach ($route['entity'] as $param) {
                        $keys[] = $param;
                    }
                }
                if (!empty($route['params'])) {
                    foreach ($route['params'] as $param) {
                        $keys[] = $param;
                    }
                }
                if (!empty($keys) && $currentCount===$pathCount) {
                    $obj = $this->getRouteFromParams($current, $currentArray, $pathArray, $keys, $obj);
                    $obj = $this->setCurrentRoute($name, $route, $obj);
                }
            }
        }
        return $obj;
    }

    /**
     * @param string $current
     * @param array $aCurrent
     * @param array $aPath
     * @param array $keys
     * @param Route $route
     * @return Route|null
     * @throws Exception
     */
    private function getRouteFromParams(string $current, array $aCurrent, array $aPath, array $keys, Route $route): ?Route
    {
        foreach ($aCurrent as $key => $value) {
            if ($aCurrent[$key]!==$aPath[$key]) {
                foreach ($keys as $keyKey => $keyValue) {
                    switch ($aPath[$key]) {
                        // PARAMS: string
                        case (preg_match(self::ARG_STRING, $aPath[$key]) ? true : false):
                            if (is_string($aCurrent[$key])) {
                                $route->setParams(strval($aCurrent[$key]));
                            } else {
                                BedroxException::render(
                                    'ERR_URI_PARAM_STRING',
                                    'The send parameter is not a string. Please check "' . $_SERVER['APP'][Env::ROUTER] . '".'
                                );
                            }
                            break;
                        // PARAMS: int
                        case (preg_match(self::ARG_NUM, $aPath[$key]) ? true : false):
                            if (is_int(intval($aCurrent[$key]))) {
                                $route->setParams(intval($aCurrent[$key]));
                            } else {
                                BedroxException::render(
                                    'ERR_URI_PARAM_INT',
                                    'The send parameter is not a number. Please check "' . $_SERVER['APP'][Env::ROUTER] . '".'
                                );
                            }
                            break;
                        // PARAMS: date
                        case (preg_match(self::ARG_DATE, $aPath[$key]) ? true : false):
                            if (strtotime($aCurrent[$key])) {
                                $route->setParams(new DateTime($aCurrent[$key]));
                            } else {
                                BedroxException::render(
                                    'ERR_URI_PARAM_DATE',
                                    'The send parameter is not a date. Please check "' . $_SERVER['APP'][Env::ROUTER] . '".'
                                );
                            }
                            break;
                        // PARAMS: bool
                        case (preg_match(self::ARG_BOOL, $aPath[$key]) ? true : false):
                            switch ($aCurrent[$key]) {
                                case 'true':
                                case '1':
                                    $route->setParams(true);
                                    break;
                                case 'false':
                                case '0':
                                    $route->setParams(false);
                                    break;
                                default:
                                    BedroxException::render(
                                        'ERR_URI_PARAM_BOOL',
                                        'The send parameter is not a boolean. Please check "' . $_SERVER['APP'][Env::ROUTER] . '".'
                                    );
                            }
                            break;
                        // PARAMS: entity
                        case (preg_match('/{(.*)*}$/', $aPath[$key]) ? true : false):
                            $repo = preg_replace('/{' . $keyValue . '(.*)?$/', $keyValue, $aPath[$key]);
                            $criteria = str_replace('{' . $keyValue . '.', '', $aPath[$key]);
                            $criteria = str_replace('}', '', $criteria);
                            if ($repo === $keyValue) {
                                $class = '\\App\\Entity\\' . ucfirst($repo);
                                $em = (new Controller)->getDoctrine();
                                if ( !empty($_SERVER['APP']['SGBD']['type']) && $_SERVER['APP']['SGBD']['type'] === Env::DB_DOCTRINE ) {
                                    if ($em->getRepository($class) !== null) {
                                        $route->setParams($em->getRepository($class)->findOneBy(array(
                                            $criteria => $aCurrent[$key]
                                        )));
                                    } else {
                                        BedroxException::render(
                                            'ERR_ORM_PARAMS',
                                            'Error while trying to access the entity. Please check "' . $_SERVER['APP'][Env::ROUTER] . '".'
                                        );
                                    }
                                } else {
                                    $entityManager = new EntityManager;
                                    if ($entityManager->getRepo($repo) !== null) {
                                        $route->setParams($entityManager->getRepo($repo)->find($aCurrent[$key]));
                                    } else {
                                        BedroxException::render(
                                            'ERR_EDR_PARAMS',
                                            'Error while trying to access the entity. Please check "' . $_SERVER['APP'][Env::ROUTER] . '".'
                                        );
                                    }
                                }
                            }
                            break;
                        default:
                            // TODO: handle unexpected parameters
                            break;
                    }
                }
                $current = str_replace($aCurrent[$key], $aPath[$key], $current);
            }
        }
        return $route;
    }
}
