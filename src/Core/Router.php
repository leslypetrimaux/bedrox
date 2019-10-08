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
                    throw new RuntimeException('Impossible de récupérer les routes depuis le fichier. Veuillez vérifier votre fichier "./config/env.yaml".');
                }
            } else {
                throw new RuntimeException('Erreur lors de l\'ouverture du fichier des routes. Veuillez vérifier votre fichier "./config/env.yaml".');
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
        $cRoute = explode('.', $current);
        if (empty($format) && !empty(end($cRoute)) && (new Request())->getResponseType(end($cRoute))) {
            $format = end($cRoute);
            $current = str_replace(
                array(
                    '.' . Response::FORMAT_XML,
                    '.' . Response::FORMAT_JSON,
                    '.' . Response::FORMAT_CSV
                ),
                '',
                $current
            );
            $this->session->set('URI_FORMAT', $format);
        }
        if (!empty($format) && !(new Request())->getResponseType($format)) {
            BedroxException::render(
                'ERR_URI_FORMAT',
                'Erreur lors de la récupération de l\'encodage de la page. Vérifiez votre route ou la configuration de votre application.'
            );
        }
        $route = new Route();
        $firewall = $this->firewall->getFirewall();
        foreach ($this->routes as $name => $routes) {
            $path = $routes['path'];
            $keys = array();
            if (!empty($routes['entity']) && is_array($routes['entity'])) {
                $aCurrent = explode('/', $current);
                $aPath = explode('/', $path);
                foreach ($routes['entity'] as $param) {
                    $keys[] = $param;
                }
                if (!empty($keys) && count($aCurrent)===count($aPath)) {
                    foreach ($aCurrent as $key => $value) {
                        if ($aCurrent[$key]!==$aPath[$key]) {
                            foreach ($keys as $keyKey => $keyValue) {
                                if (preg_match(self::ARG_STRING, $aPath[$key]) && is_string($aCurrent[$key])) {
                                    $route->setParams(strval($aCurrent[$key]));
                                }
                                if (preg_match(self::ARG_NUM, $aPath[$key]) && is_int(intval($aCurrent[$key]))) {
                                    if (intval($aCurrent[$key])) {
                                        $route->setParams(intval($aCurrent[$key]));
                                    } else {
                                        BedroxException::render(
                                            'ERR_URI_PARAM_INT',
                                            'Le paramètre ne correspond pas à celui de la route ou du controller. Veuillez vérifier la configuration de votre route.'
                                        );
                                    }
                                }
                                if (preg_match(self::ARG_DATE, $aPath[$key])) {
                                    if (strtotime($aCurrent[$key])) {
                                        $route->setParams(new DateTime($aCurrent[$key]));
                                    } else {
                                        BedroxException::render(
                                            'ERR_URI_PARAM_DATE',
                                            'Le paramètre ne correspond pas à celui de la route ou du controller. Veuillez vérifier la configuration de votre route.'
                                        );
                                    }
                                }
                                if (preg_match(self::ARG_BOOL, $aPath[$key])) {
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
                                                'Le paramètre ne correspond pas à celui de la route ou du controller. Veuillez vérifier la configuration de votre route.'
                                            );
                                    }
                                }
                                if (preg_match('/{(.*)*}$/', $aPath[$key])) {
                                    $repo = preg_replace('/{' . $keyValue . '(.*)?$/', $keyValue, $aPath[$key]);
                                    $criteria = str_replace('{' . $keyValue . '.', '', $aPath[$key]);
                                    $criteria = str_replace('}', '', $criteria);
                                    $class = '\\App\\Entity\\' . ucfirst($repo);
                                    $em = (new Controller(new Response()))->getDoctrine();
                                    if ( !empty($_SERVER['APP']['SGBD']['type']) && $_SERVER['APP']['SGBD']['type'] === Env::DB_DOCTRINE ) {
                                        if ($em->getRepository($class) !== null) {
                                            $route->setParams($em->getRepository($class)->findOneBy(array(
                                                $criteria => $aCurrent[$key]
                                            )));
                                        } else {
                                            BedroxException::render(
                                                'ERR_ORM_PARAMS',
                                                'Erreur lors de la récupération de l\'entité. Veuillez vérifier la configuration de votre route.'
                                            );
                                        }
                                    } else {
                                        if ((new EntityManager())->getRepo($repo) !== null) {
                                            $route->setParams((new EntityManager())->getRepo($repo)->find($aCurrent[$key]));
                                        } else {
                                            BedroxException::render(
                                                'ERR_EDR_PARAMS',
                                                'Erreur lors de la récupération de l\'entité. Veuillez vérifier la configuration de votre route.'
                                            );
                                        }
                                    }
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
                        'Vous n\'avez pas accès à cette page. Veuillez vérifier votre token ou l\'adresse de votre page.',
                        403
                    );
                }
            }
        }
        return $route;
    }
}
