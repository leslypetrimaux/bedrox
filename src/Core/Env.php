<?php

namespace Bedrox\Core;

use Bedrox\Skeleton;
use RuntimeException;

class Env extends Skeleton
{
    public const FILE_ENV = '/../environnement.xml';

    public const FILE_ROUTER = 'ROUTER';
    public const FILE_SECURITY = 'SECURITY';

    protected $content;

    /**
     * Load "environnements.xml" configuration file.
     *
     * @param string $file
     */
    public function load(string $file): void
    {
        try {
            if (file_exists($file)) {
                $this->content = $this->parsing->parseXmlToArray($file);
            } else {
                $encode = $this->parsing->parseAppFormat();
                http_response_code(500);
                exit((new Response)->renderView($encode, null,  array(
                    'code' => 'ERR_FILE_ENV',
                    'message' => 'Echec lors de l\'ouverture du fichier d\'environnement. Veuillez vérifier votre fichier "./environnement.xml".'
                )));
            }
            if ( is_array($this->content) && !empty($this->content['name']) && !empty($this->content['env']) && !empty($this->content['router']) && !empty($this->content['security']) && !empty($this->content['format']) && !empty($this->content['encodage']) && is_array($this->content['database']) ) {
                $this->defineApp($this->content['name']);
                $this->defineEnv($this->content['env']);
                $this->defineFile(self::FILE_ROUTER, $this->content['router']);
                $this->defineFile(self::FILE_SECURITY, $this->content['security']);
                $this->outputFormat($this->content['format'], $this->content['encodage']);
                $this->defineSGBD($this->content['database']);
            } else {
                throw new RuntimeException(
                    'Echec lors de la lecture du fichier d\'environnement. Veuillez vérifier votre fichier "./environnement.xml".'
                );
            }
            if (!is_array($_SERVER['APP'])) {
                $encode = $this->parsing->parseAppFormat();
                http_response_code(500);
                exit((new Response)->renderView($encode, null, array(
                    'code' => 'ERR_VAR_APP',
                    'message' => 'Les variables de configuration de l\'application n\'ont pas pu être définies correctement. Veuillez réessayer.'
                )));
            }
        } catch (RuntimeException $e) {
            $encode = $this->parsing->parseAppFormat();
            http_response_code(500);
            exit((new Response)->renderView($encode, null, array(
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
        $_SERVER['APP']['NAME'] = $_SESSION['APP_NAME'] = $app;
        $_SESSION['APP_TOKEN'] = null;
    }

    /**
     * Define the current environment (dev/prod).
     *
     * @param string $env
     */
    public function defineEnv(string $env): void
    {
        $_SERVER['APP']['ENV'] = $_SESSION['APP_ENV'] = $env;
        $_SERVER['APP']['DEBUG'] = $_SESSION['APP_DEBUG'] = $env !== 'prod';
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
            $_SERVER['APP'][$type] = __DIR__ . '/../../../../../' . $file;
        }
        if (!file_exists($_SERVER['APP'][$type])) {
            $encode = $this->parsing->parseAppFormat();
            http_response_code(500);
            exit((new Response)->renderView($encode, null, array(
                'code' => 'ERR_FILE_ENV',
                'message' => 'Echec lors de la lecture du fichier "' . $file . '". Veuillez vérifier votre fichier "./environnement.xml".'
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
        $_SERVER['APP']['ENCODAGE'] = $_SESSION['APP_ENCODAGE'] = $encode;
        $_SERVER['APP']['FORMAT'] = $_SESSION['APP_FORMAT'] = $format;
    }

    /**
     * Define the SGBD to be used in the Application.
     *
     * @param array $database
     */
    public function defineSGBD(array $database): void
    {
        if (!empty($database) && is_array($database)) {
            if ( !empty($database['schema']) && !empty($database['password']) && !empty($database['user']) && !empty($database['host']) && !empty($database['driver']) && !empty($database['@attributes']['encode'])) {
                $_SERVER['APP']['SGBD'] = array(
                    'ENCODE' => $database['@attributes']['encode'],
                    'DRIVER' => $database['driver'],
                    'HOST' => $database['host'],
                    'USER' => $database['user'],
                    'PWD' => $database['password'],
                    'SCHEMA' => $database['schema']
                );
            } else {
                $encode = $this->parsing->parseAppFormat();
                http_response_code(500);
                exit((new Response)->renderView($encode, null, array(
                    'code' => 'ERR_FILE_ENV',
                    'message' => 'Echec lors de la lecture des informations de la base de données du fichier d\'environnement. Veuillez vérifier votre fichier "./environnement.xml".'
                )));
            }
        }
    }
}