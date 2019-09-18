<?php

namespace Bedrox\Core;

use Bedrox\Core\Interfaces\iRouter;
use Bedrox\Skeleton;
use Bedrox\Yaml\YamlParser;
use RuntimeException;

class Router extends Skeleton implements iRouter
{
    protected $security;
    protected $routes;
    protected $response;

    public $route;

    /**
     * Router constructor.
     * Load Router configuration file.
     */
    public function __construct()
    {
        $this->response = new Response();
        try {
            parent::__construct();
            $this->security = new Security();
            if (file_exists($_SERVER['APP'][Env::FILE_ROUTER])) {
                $content = YamlParser::YAMLLoad($_SERVER['APP'][Env::FILE_ROUTER]);
                $this->routes = $content;
            } else {
                throw new RuntimeException('Echec lors de l\'ouverture du fichier des routes. Veuillez vérifier votre fichier "./config/env.yaml".');
            }
        } catch (RuntimeException $e) {
            http_response_code(500);
            exit($this->response->renderView($_SERVER['APP']['FORMAT'], null, array(
                'code' => 'ERR_FILE_ROUTER',
                'message' => $e->getMessage()
            )));
        }
    }

    /**
     * Return the requested route (if exists).
     *
     * @param string $current
     * @param string|null $format
     * @return Route|null
     */
    public function getCurrentRoute(string $current, ?string $format = null): ?Route
    {
        $cRoute = explode('.', $current);
        if (empty($format) && !empty(end($cRoute)) && (new Request())->getResponseType(end($cRoute))) {
            $format = end($cRoute);
            $current = str_replace(
                array('.' . Response::FORMAT_XML, '.' . Response::FORMAT_JSON),
                '',
                $current
            );
        }
        if (!empty($format) && !(new Request())->getResponseType($format)) {
            http_response_code(500);
            exit($this->response->renderView($_SERVER['APP']['FORMAT'], null, array(
                'code' => 'ERR_URI_FORMAT',
                'message' => 'Erreur lors de la récupération de l\'encodage de la page. Vérifiez votre route ou la configuration de votre application.'
            )));
        }
        $route = new Route();
        $firewall = $this->security->getFirewall();
        foreach ($this->routes as $name => $routes) {
            $path = $routes['path'];
            $keys = array();
            if (!empty($routes['params'])) {
                $aCurrent = explode('/', $current);
                $aPath = explode('/', $path);
                foreach ($routes['params'] as $param) {
                    $keys[] = $param;
                }
                if (!empty($keys) && count($aCurrent)===count($aPath)) {
                    foreach ($aCurrent as $key => $value) {
                        if ($aCurrent[$key]!==$aPath[$key]) {
                            $repo = null;
                            foreach ($keys as $keyKey => $keyValue) {
                                $repo = str_replace('{' . $keyValue . '}', $keyValue, $aPath[$key]);
                            }
                            if ((new EntityManager())->getRepo($repo) !== null) {
                                $route->setParams((new EntityManager())->getRepo($repo)->find($aCurrent[$key]));
                            } else {
                                http_response_code(500);
                                exit($this->response->renderView($_SERVER['APP']['FORMAT'], null, array(
                                    'code' => 'ERR_URI_PARAMS',
                                    'message' => 'Erreur lors de la récupération de l\'entité. Veuillez vérifier la configuration de votre route.'
                                )));
                            }
                            $current = str_replace($aCurrent[$key], $aPath[$key], $current);
                        }
                    }
                    $route->setParamsCount(count($keys));
                }
            }
            if ( $current === $path && !empty($routes['controller']) ) {
                $controller = explode('::', $routes['controller']);
                $route->setName($name);
                $route->setUrl($path);
                $route->setController($controller[0]);
                $route->setFunction($controller[1]);
                $route->setRender(!empty($format) ? $format : $_SERVER['APP']['FORMAT']);
                if ($this->security->isNotAuthorized($route->getName(), $firewall)) {
                    http_response_code(403);
                    exit($this->response->renderView($_SERVER['APP']['FORMAT'], null, array(
                        'code' => 'ERR_URI_DENIED_ACCESS',
                        'message' => 'Vous n\'avez pas accès à cette page. Veuillez vérifier votre token ou l\'adresse de votre page.'
                    )));
                }
            }
        }
        return $route;
    }
}
