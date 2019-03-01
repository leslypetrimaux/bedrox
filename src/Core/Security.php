<?php

namespace Bedrox\Core;

use Bedrox\Skeleton;
use Bedrox\Yaml\YamlParser;
use RuntimeException;

class Security extends Skeleton
{
    public const FIREWALL = 'firewall';
    public const TOKEN = 'token';
    public const ENCODE = 'encode';
    public const SECRET = 'secret';

    public const TYPE = 'type';
    public const AUTH = 'auth';
    public const NOAUTH = 'no-auth';

    public const SOURCE = 'source';
    public const STRATEGY = 'strategy';
    public const ENTITY = 'entity';
    
    public const ROUTE = 'route';
    public const ANONYMOUS = 'anonymous';

    protected $core;
    protected $response;

    /**
     * Security constructor.
     * Load Security configuration file.
     */
    public function __construct()
    {
        $this->response = new Response();
        try {
            parent::__construct();
            if (file_exists($_SERVER['APP'][Env::FILE_SECURITY])) {
                $content = YamlParser::YAMLLoad($_SERVER['APP'][Env::FILE_SECURITY]);
                $this->core = $content['security'];
            } else {
                $encode = $this->parsing->parseAppFormat();
                http_response_code(500);
                exit($this->response->renderView($encode, null, array(
                    'code' => 'ERR_FILE_SECURITY',
                    'message' => 'Echec lors de l\'ouverture du fichier de sécurité. Veuillez vérifier votre fichier "' . $_SERVER['APP'][Env::FILE_SECURITY] . '".'
                )));
            }
            if ( !is_array($this->core) && is_array($this->core[self::FIREWALL]) && !is_array($this->core[self::FIREWALL][self::ANONYMOUS][self::ROUTE]) && empty($this->core[self::FIREWALL][self::TOKEN]['@attributes']->secret) && empty($this->core[self::FIREWALL][self::TOKEN]['@attributes']->encode) && empty($this->core[self::FIREWALL]['@attributes'][self::TYPE]) ) {
                throw new RuntimeException(
                    'Les variables de configuration de sécurité n\'ont pas pu être définies correctement. Veuillez réessayer.'
                );
            }
        } catch (RuntimeException $e) {
            http_response_code(500);
            exit($this->response->renderView($_SERVER['APP']['FORMAT'], null, array(
                'code' => 'ERR_SECU_FIREWALL',
                'message' => $e
            )));
        }
    }

    /**
     * Define Application Token.
     *
     * @param string $encode
     * @param string $token
     */
    public function defineToken(string $encode, string $token): void
    {
        if ((!empty($encode) || !empty($token)) && in_array($encode, hash_algos(), true)) {
            $app = str_replace(' ', '', ucwords($this->session->get('APP_NAME')));
            $token = $app . '-' . $token;
            $this->session->set('APP_TOKEN', hash($encode, $token));
        } else {
            http_response_code(500);
            exit($this->response->renderView($_SERVER['APP']['FORMAT'], null, array(
                'code' => 'ERR_TOKEN',
                'message' => 'Impossible de générer le token de l\'Application. Veuillez vérifier votre fichier "./security.yaml".'
            )));
        }
    }

    /**
     * Return a Firewall array from Security file configuration.
     *
     * @return array|null
     */
    public function getFirewall(): ?array
    {
        $firewall = array(
            self::SECRET => $this->core[self::FIREWALL][self::TOKEN][self::SECRET],
            self::ENCODE => $this->core[self::FIREWALL][self::TOKEN][self::ENCODE],
            self::TYPE => $this->core[self::FIREWALL][self::TYPE],
            self::ANONYMOUS => array()
        );
        if ($firewall[self::TYPE] === self::AUTH) {
            if (empty($this->core[self::FIREWALL][self::ANONYMOUS])) {
                http_response_code(500);
                exit($this->response->renderView($_SERVER['APP']['FORMAT'], null, array(
                    'code' => 'ERR_FIREWALL_ANONYMOUS',
                    'message' => 'Vous devez définir au moins une route pour informer de l\'accès privé de l\'Application. Veuillez vérifier votre fichier "./security.yaml".'
                )));
            }
            if (!empty($this->core[self::FIREWALL][self::ANONYMOUS])) {
                if (is_array($this->core[self::FIREWALL][self::ANONYMOUS])) {
                    foreach ($this->core[self::FIREWALL][self::ANONYMOUS] as $key => $value) {
                        $firewall[self::ANONYMOUS][] = $value;
                    }
                } else {
                    $firewall[self::ANONYMOUS][] = $this->core[self::FIREWALL][self::ANONYMOUS];
                }
            } else {
                http_response_code(500);
                exit($this->response->renderView($_SERVER['APP']['FORMAT'], null, array(
                    'code' => 'ERR_FIREWALL_PARSING',
                    'message' => 'Impossible de configurer le firewall de l\'Application avec des routes anonymes. Veuillez vérifier votre fichier "./security.yaml".'
                )));
            }
        }
        $this->defineToken($firewall[self::ENCODE], $firewall[self::SECRET]);
        if (empty($_SESSION['APP_TOKEN'])) {
            http_response_code(500);
            exit($this->response->renderView($_SERVER['APP']['FORMAT'], null, array(
                'code' => 'ERR_SESSION',
                'message' => 'Une erreur s\'est produite lors de la lecture/écriture de la session courante. Merci de supprimer le cache de l\'Application.'
            )));
        }
        return $firewall;
    }

    /**
     * Check if the requested URI is available for the current user.
     * Differents authorization systems:
     *
     * + Authentification (auth):
     *   Connect user from _GET|_POST using configuration file:
     *   - Token: connect with encrypted private key
     *
     * + Public (no-auth):
     *   All page are available for non authenticated users.
     *
     * @param string $route
     * @param array $firewall
     * @return bool
     */
    public function isNotAuthorized(string $route, array $firewall): bool
    {
        $redirect = true;
        switch ($firewall[self::TYPE]) {
            case self::AUTH:
                $auth = new Auth();
                $token = $auth->tokenVerification();
                if ($token) {
                    $redirect = !$token;
                } else {
                    $this->session->set('APP_TOKEN', 'hidden');
                    foreach ($firewall[self::ANONYMOUS] as $anonymous) {
                        $redirect = $route !== $anonymous;
                        if (!$redirect) {
                            break;
                        }
                    }
                }
                break;
            case self::NOAUTH:
                $redirect = false;
                break;
        }
        return $redirect;
    }
}