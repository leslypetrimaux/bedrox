<?php

namespace Bedrox\Core;

use App\Kernel;
use Bedrox\Core\Exceptions\BedroxException;
use Bedrox\EDR\EDR;
use Bedrox\Skeleton;
use Bedrox\Yaml\YamlParser;
use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Setup;
use RuntimeException;

class Env extends Skeleton
{
    public const FILE_ENV_ROOT = 'config/env.yaml';
    public const FILE_ENV = '/../' . self::FILE_ENV_ROOT;
    public const FILE_ROUTER_ROOT = 'config/routes.yaml';
    public const FILE_ROUTER = '/../' . self::FILE_ROUTER_ROOT;
    public const FILE_SECURITY_ROOT = 'config/security.yaml';
    public const FILE_SECURITY = '/../' . self::FILE_SECURITY_ROOT;

    public const APP = 'APP';
    public const NAME = 'NAME';
    public const ENV = 'ENV';
    public const DEBUG = 'DEBUG';
    public const VERSION = 'VERSION';
    public const ENCODE = 'ENCODAGE';
    public const FORMAT = 'FORMAT';
    public const ROUTER = 'ROUTER';
    public const SECURITY = 'SECURITY';
    public const ENTITY = 'ENTITY';
    public const SGBD = 'SGBD';

    public const EDR_DRIVER = 'DRIVER';
    public const EDR_HOST = 'HOST';
    public const EDR_APIKEY = 'API_KEY';
    public const EDR_CLIENTID = 'CLIENT_ID';
    public const EDR_OAUTHTOKEN = 'OAUTH_TOKEN';
    public const EDR_ENCODE = 'ENCODE';
    public const EDR_PORT = 'PORT';
    public const EDR_USER = 'USER';
    public const EDR_PWD = 'PWD';
    public const EDR_SCHEMA = 'SCHEMA';
    public const EDR_TYPE = 'TYPE';

    public const ORM_TYPE = 'type';
    public const ORM_DRIVER = 'driver';
    public const ORM_HOST = 'host';
    public const ORM_PORT = 'port';
    public const ORM_USER = 'user';
    public const ORM_PASSWORD = 'password';
    public const ORM_SCHEMA = 'schema';
    public const ORM_DBNAME = 'dbname';
    public const ORM_FB_APIKEY = 'apiKey';
    public const ORM_FB_CLIENTID = 'clientId';
    public const ORM_FB_OAUTHTOKEN = 'oAuthToken';
    public const ORM_ENCODE = 'encode';
    public const ORM_CHARSET = 'charset';
    public const ORM_OPTIONS = 'options';
    public const ORM_OPT_DEPENDENCIES = 'dependencies';

    public const DB_NATIVE = 'native';
    public const DB_DOCTRINE = 'doctrine';

    public const DOCTRINE_CHARSET = 'utf8mb4';

    protected $content;

    /**
     * Load environments configuration file.
     *
     * @param string $file
     */
    public function load(string $file): void
    {
        try {
            if (file_exists($file)) {
                $this->content = YamlParser::YAMLLoad($file);
                if (
                    !empty($this->content['app']['env']) &&
                    !empty($this->content['app']['format'])
                ) {
                    $_SESSION['APP_DEBUG'] = $this->content['app']['env'];
                    $_SESSION['APP_FORMAT'] = $this->content['app']['format'];
                    $_SESSION['DUMPS_COUNT'] = 0;
                } else {
                    BedroxException::render(
                        'ERR_FILE_ENV',
                        'Your configuration is incomplete. Please check "config/env.yaml".',
                        500,
                        $this->parsing->parseAppFormat()
                    );
                }
            } else {
                BedroxException::render(
                    'ERR_FILE_ENV',
                    'Error while reading your configuration. Please check "config/env.yaml".',
                    500,
                    $this->parsing->parseAppFormat()
                );
            }

            if (
                is_array($this->content['app']) &&
                !empty($this->content['app']['name']) &&
                !empty($this->content['app']['env']) &&
                !empty($this->content['app']['format']) &&
                !empty($this->content['app']['encodage']) &&
                is_array($this->content['app']['database'])
            ) {
                $this->defineApp($this->content['app']['name']);
                $this->defineEnv($this->content['app']['env']);
                $this->defineVersion($this->content['app']['version']);
                $this->defineFile(self::ROUTER, $this->cmd ? self::FILE_ROUTER_ROOT : self::FILE_ROUTER);
                $this->defineFile(self::SECURITY, $this->cmd ? self::FILE_SECURITY_ROOT : self::FILE_SECURITY);
                $this->outputFormat($this->content['app']['format'], $this->content['app']['encodage']);
                $this->defineEntityLocation(!empty($this->content['app']['entity']) ? $this->content['app']['entity'] : null);
                $this->defineSGBD($this->content['app']['database']);
            } else {
                throw new RuntimeException(
                    'Error while reading your configuration. Please check "config/env.yaml".'
                );
            }
            if (!is_array($_SERVER[self::APP])) {
                BedroxException::render(
                    'ERR_VAR_APP',
                    'The configuration varaibles are not correctly defined. Please check "config/env.yaml".',
                    500,
                    $this->parsing->parseAppFormat()
                );
            }
        } catch (RuntimeException $e) {
            BedroxException::render(
                'ERR_FILE_ENV',
                $e->getMessage(),
                500,
                $this->parsing->parseAppFormat()
            );
        }
    }

    /**
     * Define Application Name.
     *
     * @param string $app
     */
    private function defineApp(string $app): void
    {
        $_SERVER[self::APP][self::NAME] = $app;
        $this->session->set('APP_NAME', $app);
        $this->session->set('APP_TOKEN', null);
    }

    /**
     * Define the current environment (dev/prod).
     *
     * @param string $env
     */
    private function defineEnv(string $env): void
    {
        $_SERVER[self::APP][self::ENV] = $env;
        $_SERVER[self::APP][self::DEBUG] = $env !== 'prod';
        $this->session->set('APP_ENV', $env);
        $this->session->set('APP_DEBUG', $env !== 'prod');
    }

    /**
     * Define the current environment (dev/prod).
     *
     * @param string $version
     */
    private function defineVersion(string $version): void
    {
        $_SERVER[self::APP][self::VERSION] = $version;
        $this->session->set('APP_VERSION', $version);
    }

    /**
     * Define the Router/Security file configuration.
     *
     * @param string $type
     * @param string $file
     */
    private function defineFile(string $type, string $file): void
    {
        if (!empty($type) && !empty($file)) {
            $_SERVER[self::APP][$type] = realpath($_SERVER[Headers::SRV_DOCUMENT_ROOT] . $file);
        }
        if (!file_exists($_SERVER[self::APP][$type])) {
            BedroxException::render(
                'ERR_FILE_ENV',
                'Error while reading "' . $file . '".',
                500,
                $this->parsing->parseAppFormat()
            );
        }
    }

    /**
     * @param string|null $location
     */
    private function defineEntityLocation(?string $location): void
    {
        $realpath = !empty($_SERVER[Headers::SRV_DOCUMENT_ROOT]) ? $_SERVER[Headers::SRV_DOCUMENT_ROOT] . '/../' . $location : $location;
        $path = realpath($realpath);
        if (!$path) {
            BedroxException::render(
                'ERR_ENV_ENTITY',
                'Error while reading "' . $realpath . '". This directory does not exists.',
                500,
                $this->parsing->parseAppFormat()
            );
        }
        $_SERVER[self::APP][self::ENTITY] = $path;
        $this->session->set(self::ENTITY, $path);
    }

    /**
     * Define the Application format and encode type.
     *
     * @param string $format
     * @param string $encode
     */
    private function outputFormat(string $format, string $encode): void
    {
        $_SERVER[self::APP][self::ENCODE] = $encode;
        $_SERVER[self::APP][self::FORMAT] = $format;
        $this->session->set('APP_ENCODAGE', $encode);
        $this->session->set('APP_FORMAT', $format);
    }

    /**
     * Define the SGBD to be used in the Application.
     *
     * @param array $database
     */
    private function defineSGBD(array $database): void
    {
        try {
            if (!empty($database) && is_array($database)) {
                if (!empty($database[self::ORM_TYPE])) {
                    switch ($database[self::ORM_TYPE]) {
                        case self::DB_DOCTRINE:
                            if ( !empty($database[self::ORM_SCHEMA]) && !empty($database[self::ORM_PASSWORD]) && !empty($database[self::ORM_USER]) && !empty($database[self::ORM_HOST]) ) {
                                try {
                                    // database configuration parameters
                                    $_SERVER[self::APP][self::SGBD] = array(
                                        self::ORM_TYPE => $database[self::ORM_TYPE],
                                        self::ORM_DRIVER => $database[self::ORM_DRIVER],
                                        self::ORM_HOST => $database[self::ORM_HOST],
                                        self::ORM_PORT => $database[self::ORM_PORT],
                                        self::ORM_USER => $database[self::ORM_USER],
                                        self::ORM_PASSWORD => $database[self::ORM_PASSWORD],
                                        self::ORM_DBNAME => $database[self::ORM_SCHEMA],
                                        self::ORM_CHARSET => !empty($database[self::ORM_ENCODE]) ? $database[self::ORM_ENCODE] : self::DOCTRINE_CHARSET,
                                    );
                                    $srvEntity = $_SERVER[self::APP][self::ENTITY];
                                    $rpEntity = realpath($srvEntity);
                                    // obtaining the entity manager
                                    $config = Setup::createAnnotationMetadataConfiguration(
                                        $this->cmd ? array($rpEntity) : array($srvEntity),
                                        $this->content['app']['env'] !== 'prod',
                                        null,
                                        null,
                                        false
                                    );
                                    $config->setSQLLogger(new DebugStack);
                                    Skeleton::$entityManager = EntityManager::create($_SERVER[self::APP][self::SGBD], $config);
                                } catch (ORMException $e) {
                                    throw new RuntimeException(
                                        $e->getMessage(),
                                        $e->getCode()
                                    );
                                }
                            } else {
                                throw new RuntimeException('Error while reading doctrine informations. Please check "config/env.yaml".');
                            }
                            break;
                        case self::DB_NATIVE:
                            if (!empty($database[self::ORM_DRIVER])) {
                                switch ($database[self::ORM_DRIVER]) {
                                    case EDR::FIRESTORE:
                                    case EDR::FIREBASE:
                                        if ( !empty($database[self::ORM_HOST]) && !empty($database[self::ORM_FB_APIKEY]) && !empty($database[self::ORM_FB_CLIENTID]) && !empty($database[self::ORM_FB_OAUTHTOKEN]) ) {
                                            $_SERVER[self::APP][self::SGBD] = array(
                                                self::EDR_DRIVER => $database[self::ORM_DRIVER],
                                                self::EDR_HOST => $database[self::ORM_HOST],
                                                self::EDR_APIKEY => $database[self::ORM_FB_APIKEY],
                                                self::EDR_CLIENTID => $database[self::ORM_FB_CLIENTID],
                                                self::EDR_OAUTHTOKEN => $database[self::ORM_FB_OAUTHTOKEN]
                                            );
                                        } else {
                                            throw new RuntimeException('Error while reading Firebase informations. Please check "config/env.yaml".');
                                        }
                                        break;
                                    case EDR::ORACLE:
                                    case EDR::MYSQL:
                                    case EDR::MARIADB:
                                    default:
                                        if ( !empty($database[self::ORM_SCHEMA]) && !empty($database[self::ORM_PASSWORD]) && !empty($database[self::ORM_USER]) && !empty($database[self::ORM_HOST]) ) {
                                            $_SERVER[self::APP][self::SGBD] = array(
                                                self::EDR_ENCODE => !empty($database[self::ORM_ENCODE]) ? $database[self::ORM_ENCODE] : Kernel::DEFAULT_ENCODE,
                                                self::EDR_DRIVER => $database[self::ORM_DRIVER],
                                                self::EDR_HOST => $database[self::ORM_HOST],
                                                self::EDR_PORT => $database[self::ORM_PORT],
                                                self::EDR_USER => $database[self::ORM_USER],
                                                self::EDR_PWD => $database[self::ORM_PASSWORD],
                                                self::EDR_SCHEMA => $database[self::ORM_SCHEMA]
                                            );
                                        } else {
                                            throw new RuntimeException('Error while reading EDR informations. Please check "config/env.yaml".');
                                        }
                                        break;
                                }
                            } else {
                                throw new RuntimeException('Unable to access the database driver. Please check "config/env.yaml".');
                            }
                            break;
                    }
                } else {
                    throw new RuntimeException('Unable to connect to the database. Please check "config/env.yaml".');
                }
            } else {
                throw new RuntimeException('Unable to access database informations. Please check "config/env.yaml".');
            }
        } catch (RuntimeException $e) {
            BedroxException::render(
                'ERR_FILE_ENV_' . $e->getCode(),
                $e->getMessage(),
                500,
                $this->parsing->parseAppFormat()
            );
        }
    }
}
