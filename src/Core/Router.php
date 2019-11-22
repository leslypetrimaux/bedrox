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
                $content = YamlParser::YAMLLoad($_SERVER['APP'][Env::ROUTER]);
                if (is_array($content)) {
                    $this->routes = $content;
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
        if (empty($format) && !empty(end($cRoute)) && (new Request())->getResponseType(end($cRoute))) {
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
        if (!empty($format) && !(new Request())->getResponseType($format)) {
            BedroxException::render(
                'ERR_URI_FORMAT',
                'Error while trying to retrieve your page format. Please check your configuration.'
            );
        }
        $route = new Route();
        $firewall = $this->firewall->getFirewall();
        foreach ($this->routes as $name => $routes) {
            $path = $routes['path'];
            $keys = array();
            if ((!empty($routes['params']) && is_array($routes['params'])) || (!empty($routes['entity']) && is_array($routes['entity']))) {
                $aCurrent = explode('/', $current);
                $aPath = explode('/', $path);
                if (!empty($routes['entity'])) {
                    foreach ($routes['entity'] as $param) {
                        $keys[] = $param;
                    }
                }
                if (!empty($routes['params'])) {
                    foreach ($routes['params'] as $param) {
                        $keys[] = $param;
                    }
                }
                if (!empty($keys) && count($aCurrent)===count($aPath)) {
                    foreach ($aCurrent as $key => $value) {
                        if ($aCurrent[$key]!==$aPath[$key]) {
                            foreach ($keys as $keyKey => $keyValue) {
                                switch ($aPath[$key]) {
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
                                    case (preg_match('/{(.*)*}$/', $aPath[$key]) ? true : false):
                                        $repo = preg_replace('/{' . $keyValue . '(.*)?$/', $keyValue, $aPath[$key]);
                                        $criteria = str_replace('{' . $keyValue . '.', '', $aPath[$key]);
                                        $criteria = str_replace('}', '', $criteria);
                                        if ($repo === $keyValue) {
                                            $class = '\\App\\Entity\\' . ucfirst($repo);
                                        } else {
                                            BedroxException::render(
                                                'ERR_ROUTE_PARAMS',
                                                'Error while trying to retrieve the entity parameter. Please check "' . $_SERVER['APP'][Env::ROUTER] . '".'
                                            );
                                        }
                                        $em = (new Controller(new Response()))->getDoctrine();
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
                                            if ((new EntityManager())->getRepo($repo) !== null) {
                                                $route->setParams((new EntityManager())->getRepo($repo)->find($aCurrent[$key]));
                                            } else {
                                                BedroxException::render(
                                                    'ERR_EDR_PARAMS',
                                                    'Error while trying to access the entity. Please check "' . $_SERVER['APP'][Env::ROUTER] . '".'
                                                );
                                            }
                                        }
                                        break;
                                    default:
                                        if ($aCurrent[$key] !== $aPath[$key]) {
                                            BedroxException::render(
                                                'ERR_URI_PARAMS',
                                                'The URI parameters contains error. Please check "' . $_SERVER['APP'][Env::ROUTER] . '".'
                                            );
                                        }
                                        break;
                                }
                            }
                            $current = str_replace($aCurrent[$key], $aPath[$key], $current);
                        }
                    }
                }
            }
            if ( $current === $path && !empty($routes['controller']) ) {
                $controller = explode('::', $routes['controller']);
                $route->setName($name);
                $route->setUrl($path);
                $route->setController($controller[0]);
                $route->setFunction($controller[1]);
                $route->setRender(!empty($format) ? $format : $_SERVER['APP']['FORMAT']);
                if ($this->firewall->isNotAuthorized($route->getName(), $firewall)) {
                    BedroxException::render(
                        'ERR_URI_DENIED_ACCESS',
                        'You don\'t have access to this section. Please check your token or URI.',
                        403
                    );
                }
            }
        }
        return $route;
    }
}
