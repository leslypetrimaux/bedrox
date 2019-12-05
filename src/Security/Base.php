<?php

namespace Bedrox\Security;

use Bedrox\Core\Env;
use Bedrox\Core\Exceptions\BedroxException;
use Bedrox\Skeleton;
use Bedrox\Yaml\YamlParser;
use RuntimeException;

class Base extends Skeleton
{
    public const REPLACE_FLAGS = ENT_COMPAT | ENT_HTML5;

    public const SECURITY = 'security';
    public const FIREWALL = 'firewall';
    public const ENCODE = 'encode';
    public const SECRET = 'secret';
    public const TYPE = 'type';
    public const TOKEN = 'token';
    public const SIGNIN = 'signin';
    public const NOAUTH = 'public';

    public const SOURCE = 'source';
    public const STRATEGY = 'strategy';
    public const ENTITY = 'entity';
    public const ENTITY_CLASS = 'class';
    public const ENTITY_LOGIN = 'login';
    public const ENTITY_PASS = 'password';
    
    public const ROUTE = 'route';
    public const ANONYMOUS = 'anonymous';

    protected $security;

    /**
     * Security constructor.
     * Load Security configuration file.
     */
    public function __construct()
    {
        try {
            parent::__construct();
            if (file_exists($_SERVER[Env::APP][Env::SECURITY])) {
                $content = YamlParser::YAMLLoad($_SERVER[Env::APP][Env::SECURITY]);
                $this->security = $content['security'];
            } else {
                BedroxException::render(
                    'ERR_FILE_SECURITY:',
                    'Error while trying to access your security file. Please check "' . $_SERVER[Env::APP][Env::SECURITY] . '".',
                    500,
                    $this->parsing->parseAppFormat()
                );
            }
        } catch (RuntimeException $e) {
            BedroxException::render(
                'ERR_SECU_FIREWALL',
                $e->getMessage()
            );
        }
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
        $this->session->set('APP_AUTH', false);
        switch ($firewall[self::TYPE]) {
            case self::TOKEN:
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
            case self::SIGNIN:
                // TODO: handle user login
                BedroxException::render(
                    'FIREWALL_USERS_AUTH',
                    'Entity authentication is not available yet. Please use "token" or "public" type.'
                );
                break;
            case self::NOAUTH:
                $redirect = false;
                break;
        }
        return $redirect;
    }
}
