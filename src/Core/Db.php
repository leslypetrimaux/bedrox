<?php

namespace Bedrox\Core;

use Bedrox\Core\Databases\MySQL;

class Db
{
    public const ENCODAGE_DEFAULT = 'utf-8';

    public const MYSQL = 'mysql';
    public const MARIADB = 'mariadb';
    public const ORACLE = 'oracle';

    protected $con;
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
        $this->host = $_SERVER['APP']['SGBD']['HOST'];
        $this->user = $_SERVER['APP']['SGBD']['USER'];
        $this->pwd = $_SERVER['APP']['SGBD']['PWD'];
        $this->schema = $_SERVER['APP']['SGBD']['SCHEMA'];
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
        $this->con = !empty($driver) ? null : false;
        switch ($driver) {
            case self::MYSQL:
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