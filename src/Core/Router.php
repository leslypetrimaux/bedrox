<?php

namespace Bedrox\Core;

use Bedrox\Core\Interfaces\iRouter;
use Exception;
use Bedrox\Skeleton;

class Router extends Skeleton implements iRouter
{
    protected $security;
    protected $routes;

    public $route;

    /**
     * Router constructor.
     * Load Router configuration file.
     */
    public function __construct()
    {
        try {
            parent::__construct();
            $this->security = new Security();
            if (file_exists($_SERVER['APP'][Env::FILE_ROUTER])) {
                $this->routes = $this->parsing->parseXmlToArray($_SERVER['APP'][Env::FILE_ROUTER])['route']['route'];
            } else {
                throw new Exception();
            }
        } catch (Exception $e) {
            http_response_code(500);
            exit((new Response())->renderView($_SERVER['APP']['FORMAT'], null, array(
                'code' => 'ERR_FILE_ROUTER',
                'message' => 'Echec lors de l\'ouverture du fichier des routes. Veuillez vérifier votre fichier "./environnement.xml".'
            )));
        }
    }

    /**
     * Return the requested route (if exists).
     *
     * @param string $current
     * @return Route|null
     */
    public function getCurrentRoute(string $current): ?Route
    {
        $route = new Route();
        $firewall = $this->security->getFirewall();
        foreach ($this->routes as $routes) {
            $path = $routes['@attributes']['path'];
            $keys = array();
            if (!empty($routes['params'])) {
                $aCurrent = explode('/', $current);
                $aPath = explode('/', $path);
                foreach ($routes['params'] as $keyParam => $valueParam) {
                    $keys[] = $keyParam;
                }
                if (!empty($keys) && count($aCurrent)===count($aPath)) {
                    foreach ($aCurrent as $key => $value) {
                        if ($aCurrent[$key]!==$aPath[$key]) {
                            $int = (int)$aCurrent[$key] !==0 ? (int)$aCurrent[$key] : $aCurrent[$key];
                            if (!is_int($int)) {
                                http_response_code(404);
                                exit((new Response())->renderView($_SERVER['APP']['FORMAT'], null, array(
                                    'code' => 'ERR_URI_WRONG_PARAMS',
                                    'message' => 'Le paramètre de cette route ne correspond pas à celui attendu. Veuillez vérifier votre requête.'
                                )));
                            }
                            $repo = null;
                            foreach ($keys as $keyKey => $keyValue) {
                                $repo = str_replace('{' . $keyValue . '}', $keyValue, $aPath[$key]);
                            }
                            $route->params = (new EntityManager())->getRepo($repo)->find($aCurrent[$key]);
                            $current = str_replace($aCurrent[$key], $aPath[$key], $current);
                        }
                    }
                    $route->paramsCount = count($keys);
                }
            }
            if ( $current === $path && !empty($routes['@attributes']['controller']) ) {
                $controller = explode('::', $routes['@attributes']['controller']);
                $route->name = $routes['@attributes']['name'];
                $route->url = $path;
                $route->controller = $controller[0];
                $route->function = $controller[1];
                $route->render = !empty($routes['render']) ? $routes['@attributes']['render'] : $this->session['APP_FORMAT'];
                if ($this->security->isAuthorized($route->name, $firewall)) {
                    http_response_code(403);
                    exit((new Response())->renderView($_SERVER['APP']['FORMAT'], null, array(
                        'code' => 'ERR_URI_DENIED_ACCESS',
                        'message' => 'Vous n\'avez pas accès à cette page. Veuillez vérifier votre token ou l\'adresse de votre page.'
                    )));
                }
            }
        }
        return $route;
    }
}