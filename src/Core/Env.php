<?php

namespace Bedrox\Core;

use App\Kernel;
use Bedrox\Skeleton;
use Bedrox\Yaml\YamlParser;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Setup;
use RuntimeException;

class Env extends Skeleton
{
    public const FILE_ENV = '/../config/env.yaml';

    public const FILE_ROUTER = 'ROUTER';
    public const FILE_SECURITY = 'SECURITY';

    public const DB_NATIVE = 'native';
    public const DB_DOCTRINE = 'doctrine';

    protected $content;
    protected $response;

    public function __construct()
    {
        parent::__construct();
        $this->response = new Response();
    }

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
                    $encode = $this->parsing->parseAppFormat();
                    http_response_code(500);
                    exit($this->response->renderView($encode, null,  array(
                        'code' => 'ERR_FILE_ENV',
                        'message' => 'Le fichier d\'environnement n\'est pas correctement complété. Veuillez vérifier votre fichier "./config/env.yaml".'
                    )));
                }
            } else {
                $encode = $this->parsing->parseAppFormat();
                http_response_code(500);
                exit($this->response->renderView($encode, null,  array(
                    'code' => 'ERR_FILE_ENV',
                    'message' => 'Echec lors de l\'ouverture du fichier d\'environnement. Veuillez vérifier votre fichier "./config/env.yaml".'
                )));
            }
            if (
                is_array($this->content['app']) &&
                !empty($this->content['app']['name']) &&
                !empty($this->content['app']['env']) &&
                !empty($this->content['app']['router']) &&
                !empty($this->content['app']['security']) &&
                !empty($this->content['app']['format']) &&
                !empty($this->content['app']['encodage']) &&
                is_array($this->content['app']['database'])
            ) {
                $this->defineApp($this->content['app']['name']);
                $this->defineEnv($this->content['app']['env']);
                $this->defineFile(self::FILE_ROUTER, $this->content['app']['router']);
                $this->defineFile(self::FILE_SECURITY, $this->content['app']['security']);
                $this->outputFormat($this->content['app']['format'], $this->content['app']['encodage']);
                $this->defineSGBD($this->content['app']['database']);
            } else {
                $encode = $this->parsing->parseAppFormat();
                http_response_code(500);
                exit($this->response->renderView($encode, null, array(
                    'code' => 'ERR_READ_APP',
                    'message' => 'Echec lors de la lecture du fichier d\'environnement. Veuillez vérifier votre fichier "./config/env.yaml".'
                )));
            }
            if (!is_array($_SERVER['APP'])) {
                $encode = $this->parsing->parseAppFormat();
                http_response_code(500);
                exit($this->response->renderView($encode, null, array(
                    'code' => 'ERR_VAR_APP',
                    'message' => 'Les variables de configuration de l\'application n\'ont pas pu être définies correctement. Veuillez vérifier votre fichier "./config/env.yaml".'
                )));
            }
        } catch (RuntimeException $e) {
            $encode = $this->parsing->parseAppFormat();
            http_response_code(500);
            exit($this->response->renderView($encode, null, array(
                'code' => 'ERR_FILE_ENV',
                'message' => $e
            )));
        }
    }

    /**
     * Define Application Name.
     *
     * @param string $app
     */
    public function defineApp(string $app): void
    {
        $_SERVER['APP']['NAME'] = $app;
        $this->session->set('APP_NAME', $app);
        $this->session->set('APP_TOKEN', null);
    }

    /**
     * Define the current environment (dev/prod).
     *
     * @param string $env
     */
    public function defineEnv(string $env): void
    {
        $_SERVER['APP']['ENV'] = $env;
        $_SERVER['APP']['DEBUG'] = $env !== 'prod';
        $this->session->set('APP_ENV', $env);
        $this->session->set('APP_DEBUG', $env !== 'prod');
    }

    /**
     * Define the Router/Security file configuration.
     *
     * @param string $type
     * @param string $file
     */
    public function defineFile(string $type, string $file): void
    {
        if (!empty($type) && !empty($file)) {
            $_SERVER['APP'][$type] = $_SERVER['DOCUMENT_ROOT'] . '/../config/' . $file;
        }
        if (!file_exists($_SERVER['APP'][$type])) {
            $encode = $this->parsing->parseAppFormat();
            http_response_code(500);
            exit($this->response->renderView($encode, null, array(
                'code' => 'ERR_FILE_ENV',
                'message' => 'Echec lors de la lecture du fichier "' . $file . '". Veuillez vérifier votre fichier "./config/env.yaml".'
            )));
        }
    }

    /**
     * Define the Application format and encode type.
     *
     * @param string $format
     * @param string $encode
     */
    public function outputFormat(string $format, string $encode): void
    {
        $_SERVER['APP']['ENCODAGE'] = $encode;
        $_SERVER['APP']['FORMAT'] = $format;
        $this->session->set('APP_ENCODAGE', $encode);
        $this->session->set('APP_FORMAT', $format);
    }

    /**
     * Define the SGBD to be used in the Application.
     *
     * @param array $database
     */
    public function defineSGBD(array $database): void
    {
        try {
            if (!empty($database) && is_array($database)) {
                if (!empty($database['type'])) {
                    switch ($database['type']) {
                        case self::DB_DOCTRINE:
                            if ( !empty($database['schema']) && !empty($database['password']) && !empty($database['user']) && !empty($database['host']) ) {
                                try {
                                    // database configuration parameters
                                    $_SERVER['APP']['SGBD'] = array(
                                        'driver' => $database['driver'],
                                        'host' => $database['host'],
                                        'port ' => $database['port'],
                                        'user' => $database['user'],
                                        'password' => $database['password'],
                                        'dbname' => $database['schema'],
                                        'charset' => $database['encode'],
                                    );
                                    // obtaining the entity manager
                                    $config = Setup::createAnnotationMetadataConfiguration(array(__DIR__ . '/new/App/Entity'), $this->content['app']['env'] !== 'prod');
                                    Skeleton::$entityManager = \Doctrine\ORM\EntityManager::create($_SERVER['APP']['SGBD'], $config);
                                } catch (ORMException $e) {
                                    throw new RuntimeException($e->getMessage());
                                }
                            } else {
                                throw new RuntimeException('Echec lors de la lecture des informations pour doctrine du fichier d\'environnement. Veuillez vérifier votre fichier "./config/env.yaml".');
                            }
                            break;
                        case self::DB_NATIVE:
                            if (!empty($database['driver'])) {
                                switch ($database['driver']) {
                                    case EDR::FIRESTORE:
                                    case EDR::FIREBASE:
                                        if ( !empty($database['host']) && !empty($database['apiKey']) && !empty($database['clientId']) && !empty($database['oAuthToken']) ) {
                                            $_SERVER['APP']['SGBD'] = array(
                                                'DRIVER' => $database['driver'],
                                                'HOST' => $database['host'],
                                                'API_KEY' => $database['apiKey'],
                                                'CLIENT_ID' => $database['clientId'],
                                                'OAUTH_TOKEN' => $database['oAuthToken']
                                            );
                                        } else {
                                            throw new RuntimeException('Echec lors de la lecture des informations de la base de données du fichier d\'environnement. Veuillez vérifier votre fichier "./config/env.yaml".');
                                        }
                                        break;
                                    case EDR::ORACLE:
                                    case EDR::MYSQL:
                                    case EDR::MARIADB:
                                    default:
                                        if ( !empty($database['schema']) && !empty($database['password']) && !empty($database['user']) && !empty($database['host']) ) {
                                            $_SERVER['APP']['SGBD'] = array(
                                                'ENCODE' => !empty($database['encode']) ? $database['encode'] : Kernel::DEFAULT_ENCODE,
                                                'DRIVER' => $database['driver'],
                                                'HOST' => $database['host'],
                                                'PORT' => $database['port'],
                                                'USER' => $database['user'],
                                                'PWD' => $database['password'],
                                                'SCHEMA' => $database['schema']
                                            );
                                        } else {
                                            throw new RuntimeException('Echec lors de la lecture des informations de la base de données du fichier d\'environnement. Veuillez vérifier votre fichier "./config/env.yaml".');
                                        }
                                        break;
                                }
                            } else {
                                throw new RuntimeException('Impossible de récupérer le driver dans le fichier d\'environnement. Veuillez vérifier votre fichier "./config/env.yaml".');
                            }
                            break;
                    }
                } else {
                    throw new RuntimeException('Impossible de récupérer le type de connexion de base de données dans le fichier d\'environnement. Veuillez vérifier votre fichier "./config/env.yaml".');
                }
            } else {
                throw new RuntimeException('Echec lors de récupérer les informations de la base de données du fichier d\'environnement. Veuillez vérifier votre fichier "./config/env.yaml".');
            }
        } catch (RuntimeException $e) {
            $encode = $this->parsing->parseAppFormat();
            http_response_code(500);
            exit($this->response->renderView($encode, null, array(
                'code' => 'ERR_FILE_ENV_' . $e->getCode(),
                'message' => $e->getMessage()
            )));
        }
    }
}
