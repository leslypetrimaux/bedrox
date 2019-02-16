<?php

namespace Bedrox\Core;

use Bedrox\Core\Databases\FirebaseDatabase;
use Bedrox\Core\Databases\Firestore;
use Bedrox\Core\Databases\MySQL;

class Db
{
    public const ENCODAGE_DEFAULT = 'utf-8';

    public const MYSQL = 'mysql';
    public const MARIADB = 'mariadb';
    public const ORACLE = 'oracle';
    public const FIREBASE = 'firebase';
    public const FIRESTORE = 'firestore';

    protected $con;
    protected $config;
    protected $host;
    protected $user;
    protected $pwd;
    protected $schema;

    /**
     * Db constructor
     * Set variables from configurations to connect to the wanted SGBD.
     */
    public function __construct()
    {
        if (!empty($_SERVER['APP']['SGBD']['DRIVER'])) {
            switch ($_SERVER['APP']['SGBD']['DRIVER']) {
                case self::FIREBASE:
                    $this->config = $_SERVER['APP']['SGBD']['CONF'];
                    break;
                case self::MYSQL:
                case self::MARIADB:
                case self::ORACLE:
                default:
                    $this->host = $_SERVER['APP']['SGBD']['HOST'];
                    $this->user = $_SERVER['APP']['SGBD']['USER'];
                    $this->pwd = $_SERVER['APP']['SGBD']['PWD'];
                    $this->schema = $_SERVER['APP']['SGBD']['SCHEMA'];
                    break;
            }
        } else {
            http_response_code(500);
            exit((new Response())->renderView($_SERVER['APP']['FORMAT'], null, array(
                'code' => 'ERR_DB_CONSTRUCT',
                'message' => 'Echec lors de création de la connexion à la base de données. Veuillez vérifier votre fichier "./environnement.xml".'
            )));
        }
    }

    /**
     * Set the SGBD to be used in the application.
     * Differents SGBD systems:
     * - MySQL: v 5.7+
     * - MariaDB: v 10.2+ (currently unavailable)
     * - Oracle: v 12g  (currently unavailable)
     * - MSSQL: v xx (currently unavailable)
     * - FirebaseDb: (currently unavailable)
     * - JSON: (currently unavailable)
     *
     * @param string $driver
     * @return bool|MySQL|null
     */
    public function setDriver(string $driver)
    {
        // TODO: Make FirebaseDatabase a Virtual SGBD
        $this->con = !empty($driver) ? null : false;
        switch ($driver) {
            case self::FIREBASE:
                $this->con = new FirebaseDatabase($this->config);
                break;
            case self::FIRESTORE:
                $this->con = new Firestore($this->config);
                break;
            case self::MYSQL:
            case self::MARIADB:
                $this->con = new MySQL(
                    !empty($this->host) ? $this->host : null,
                    !empty($this->user) ? $this->user : null,
                    !empty($this->pwd) ? $this->pwd : null,
                    !empty($this->schema) ? $this->schema : null
                );
                break;
        }
        if (empty($this->con)) {
            http_response_code(500);
            exit((new Response())->renderView($_SERVER['APP']['FORMAT'], null, array(
                'code' => 'ERR_DB_CONNECT',
                'message' => 'Impossible de créer une connexion "' . $driver . '".'
            )));
        }
        return $this->con;
    }
}