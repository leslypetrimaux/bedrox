<?php

namespace Bedrox\EDR;

use Bedrox\Core\Env;
use Bedrox\EDR\Databases\FirebaseDatabase;
use Bedrox\EDR\Databases\Firestore;
use Bedrox\EDR\Databases\MySQL;
use Bedrox\Core\Exceptions\BedroxException;

class EDR
{
    public const ENCODAGE_DEFAULT = 'utf-8';

    public const MYSQL = 'mysql';
    public const MARIADB = 'mariadb';
    public const ORACLE = 'oracle';
    public const FIREBASE = 'firebase';
    public const FIRESTORE = 'firestore';

    protected $con;
    protected $host;
    protected $port;
    protected $user;
    protected $pwd;
    protected $schema;
    protected $apiKey;
    protected $clientId;
    protected $oAuthToken;
    protected $type;

    /**
     * EDR constructor
     * Set variables from configurations to connect to the wanted SGBD.
     */
    public function __construct()
    {
        if (!empty($_SERVER[Env::APP][Env::SGBD][Env::EDR_DRIVER])) {
            switch ($_SERVER[Env::APP][Env::SGBD][Env::EDR_DRIVER]) {
                case self::FIRESTORE:
                case self::FIREBASE:
                    $this->host = $_SERVER[Env::APP][Env::SGBD][Env::EDR_HOST];
                    $this->apiKey = $_SERVER[Env::APP][Env::SGBD][Env::EDR_APIKEY];
                    $this->clientId = $_SERVER[Env::APP][Env::SGBD][Env::EDR_CLIENTID];
                    $this->oAuthToken = $_SERVER[Env::APP][Env::SGBD][Env::EDR_OAUTHTOKEN];
                    $this->type = $_SERVER[Env::APP][Env::SGBD][Env::EDR_TYPE];
                    break;
                case self::ORACLE:
                    // TODO: handle Oracle SGBD (with options)
                case self::MYSQL:
                case self::MARIADB:
                default:
                    $this->host = $_SERVER[Env::APP][Env::SGBD][Env::EDR_HOST];
                    $this->port = $_SERVER[Env::APP][Env::SGBD][Env::EDR_PORT];
                    $this->user = $_SERVER[Env::APP][Env::SGBD][Env::EDR_USER];
                    $this->pwd = $_SERVER[Env::APP][Env::SGBD][Env::EDR_PWD];
                    $this->schema = $_SERVER[Env::APP][Env::SGBD][Env::EDR_SCHEMA];
                    break;
            }
        } else {
            BedroxException::render(
                'ERR_DB_CONSTRUCT',
                'An error occurs while trying to access your database. Please check "config/env.yaml".'
            );
        }
    }

    /**
     * Set the SGBD to be used in the application.
     * Differents SGBD systems:
     * - MySQL: v 5.7+
     * - MariaDB: v 10.2+
     * - Oracle: v 12g  (currently unavailable)
     * - MSSQL: v xx (currently unavailable)
     * - Firestore: bêta
     * - FirebaseDatabase: bêta
     * - JSON: (currently unavailable)
     *
     * @param string $driver
     * @return bool|MySQL|FirebaseDatabase|Firestore|null
     */
    public function setDriver(string $driver)
    {
        $this->con = !empty($driver) ? null : false;
        switch ($driver) {
            case self::FIREBASE:
                $this->con = new FirebaseDatabase($this->host, $this->apiKey, $this->clientId, $this->oAuthToken, $this->type);
                break;
            case self::FIRESTORE:
                $this->con = new Firestore($this->host, $this->apiKey, $this->clientId, $this->oAuthToken, $this->type);
                break;
            case self::MYSQL:
            case self::MARIADB:
                $this->con = new MySQL(
                    !empty($driver) ? $driver : null,
                    !empty($this->host) ? $this->host : null,
                    !empty($this->port) ? (int)$this->port : null,
                    !empty($this->user) ? $this->user : null,
                    !empty($this->pwd) ? $this->pwd : null,
                    !empty($this->schema) ? $this->schema : null
                );
                break;
        }
        if (empty($this->con)) {
            BedroxException::render(
                'ERR_DB_CONNECT',
                'Unable to create a "' . $driver . '" connexion.'
            );
        }
        return $this->con;
    }
}
