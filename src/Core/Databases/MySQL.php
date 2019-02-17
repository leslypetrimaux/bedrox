<?php

namespace Bedrox\Core\Databases;

use Bedrox\Core\Db;
use Bedrox\Core\Entity;
use Bedrox\Core\EntityManager;
use Bedrox\Core\Interfaces\iSgbd;
use Bedrox\Core\Response;
use Exception;
use PDO;
use PDOException;
use RuntimeException;

class MySQL extends PDO implements iSgbd
{
    public const ENCODE = 'SET NAMES ';
    public const UTF8 = 'utf8';

    protected $em;
    protected $con;

    /**
     * MySQL constructor.
     * Class do manage MySQL transactions. Array used to retrieve Entities.
     * Read Entity parameters to search and write the database.
     * Connect user using Entity
     * Read all/one (select) Entity
     * Persists (insert/update) Entity
     * Remove (delete) Entity
     * 
     * @param string $host
     * @param string $user
     * @param string $pwd
     * @param string $schema
     */
    public function __construct(string $host, string $user, string $pwd, string $schema)
    {
        parent::__construct(
            Db::MYSQL . ':dbname=' . $schema . ';host=' . $host,
            $user,
            $pwd,
            array(PDO::MYSQL_ATTR_INIT_COMMAND => $this->getEncodage($_SERVER['APP']['SGBD']['ENCODE']))
        );
        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        if ($this->getAttribute(PDO::ATTR_DRIVER_NAME) === Db::MYSQL) {
            $this->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
        }
        $this->em = new EntityManager();
    }

    /**
     * Get PDO Encodage type (PDO::MYSQL_ATTR_INIT_COMMAND)
     *
     * @param string $encodage
     * @return string|null
     */
    public function getEncodage(string $encodage): ?string
    {
        switch ($encodage) {
            case self::UTF8:
            default:
                $result = self::ENCODE . self::UTF8;
                break;
        }
        return !empty($result) ? $result : self::ENCODE . self::UTF8;
    }

    /**
     * Execute the Scripted query from a controller.
     *
     * @param string $query
     * @return array|null
     */
    public function buildQuery(string $query): ?array
    {
        try {
            $this->con = $this->beginTransaction();
            $req = $this->query($query);
            $results = $req->fetchAll(PDO::FETCH_ASSOC);
            $this->con = $this->commit();
            return $results;
        } catch (PDOException | Exception $e) {
            http_response_code(500);
            exit((new Response())->renderView($_SERVER['APP']['FORMAT'], null, array(
                'code' => 'ERR_MYSQL_' . $e->getCode(),
                'message' => $e->getMessage()
            )));
        }
    }

    /**
     * Search the table to retrieve one field and convert it in Entity
     *
     * @param string $table
     * @param string $id
     * @return Entity|mixed|null
     */
    public function find(string $table, string $id): ?Entity
    {
        $entity = $this->em->getEntity($table);
        $primary = $this->em->getTableKey($entity);
        $columns = $this->em->getColumns($entity);
        $cols = '';
        foreach ($columns as $column) {
            $cols .= empty($cols) ? $column : ',' . $column;
        }
        try {
            $req = $this->prepare('SELECT ' . $cols . ' FROM ' . $table . ' WHERE ' . $primary . ' = :primary;');
            if (!empty($id)) {
                $req->bindParam('primary', $id);
            }
            $req->execute();
            $result = $req->fetch(PDO::FETCH_ASSOC);
            $e = $req->errorInfo();
            if (!empty($e[1]) && $_SERVER['APP']['DEBUG']) {
                throw new RuntimeException($e[2], $e[1]);
            }
            if ($result) {
                foreach ($result as $key => $value) {
                    $var = array_search($key, $columns, true);
                    $entity->$var = $value;
                }
            } else {
                $entity = null;
            }
            return $entity;
        } catch (PDOException | Exception | RuntimeException $e) {
            http_response_code(500);
            exit((new Response())->renderView($_SERVER['APP']['FORMAT'], null, array(
                'code' => 'ERR_MYSQL_' . $e->getCode(),
                'message' => $e->getMessage()
            )));
        }
    }

    /**
     * Search the table to retrieve all field and convert it in Entity
     *
     * @param string $table
     * @return array|null
     */
    public function findAll(string $table): ?array
    {
        $entities = array();
        $entity = $this->em->getEntity($table);
        $columns = $this->em->getColumns($entity);
        $cols = '';
        foreach ($columns as $column) {
            $cols .= empty($cols) ? $column : ',' . $column;
        }
        try {
            $req = $this->query('SELECT ' . $cols . ' FROM ' . $table . ';');
            $results = $req->fetchAll(PDO::FETCH_ASSOC);
            $e = $req->errorInfo();
            if (!empty($e[1]) && $_SERVER['APP']['DEBUG']) {
                throw new RuntimeException($e[2], $e[1]);
            }
            if ($results) {
                foreach ($results as $result) {
                    $entity = $this->em->getEntity($table);
                    foreach ($result as $key => $value) {
                        $var = array_search($key, $columns, true);
                        $entity->$var = $value;
                    }
                    $entities[] = $entity;
                }
            }
            return $entities;
        } catch (PDOException | Exception | RuntimeException $e) {
            http_response_code(500);
            exit((new Response())->renderView($_SERVER['APP']['FORMAT'], null, array(
                'code' => 'ERR_MYSQL_' . $e->getCode(),
                'message' => $e->getMessage()
            )));
        }
    }

    /**
     * Persist an Entity (insert or update)
     *
     * @param Entity $entity
     * @return bool
     */
    public function persist(Entity $entity): bool
    {
        return $entity->getId() !== null ? $this->update($entity) : $this->insert($entity);
    }

    /**
     * Insert a new Entity as row in the database
     *
     * @param Entity $entity
     * @return bool
     */
    public function insert(Entity $entity): bool
    {
        $table = $this->em->getTable($entity);
        $primary = $this->em->getTableKey($entity);
        $columns = $this->em->getColumns($entity);
        $cols = $keys = '';
        foreach ($columns as $column) {
            $primary = $column === $primary;
            if (!$primary) {
                $cols .= empty($cols) ? $column : ',' . $column;
                $keys .= empty($keys) ? '' : ',';
                $keys .= ':' . $column;
            }
        }
        try {
            $this->con = $this->beginTransaction();
            $req = $this->prepare('INSERT INTO ' . $table . ' (' . $cols . ') VALUES (' . $keys . ');');
            $cols = explode(',', $cols);
            $keys = explode(',', $keys);
            foreach ($cols as $key => $value) {
                $value = str_replace(':', '', $value);
                $var = array_search($value, $columns, true);
                if (!empty($entity->$var)) {
                    $req->bindParam($keys[$key], $entity->$var);
                }
            }
            $result = $req->execute();
            $e = $req->errorInfo();
            if (!empty($e[1]) && $_SERVER['APP']['DEBUG']) {
                throw new RuntimeException($e[2], $e[1]);
            }
            $this->con = $this->commit();
            return $result;
        } catch (PDOException | Exception | RuntimeException $e) {
            $this->con = $this->rollBack();
            http_response_code(500);
            exit((new Response())->renderView($_SERVER['APP']['FORMAT'], null, array(
                'code' => 'ERR_MYSQL_' . $e->getCode(),
                'message' => $e->getMessage()
            )));
        }
    }

    /**
     * Update an Entity in the database
     *
     * @param Entity $entity
     * @return bool
     */
    public function update(Entity $entity): bool
    {
        $table = $this->em->getTable($entity);
        $primary = $this->em->getTableKey($entity);
        $columns = $this->em->getColumns($entity);
        $pColumn = $pValue = $cols = $keys = '';
        foreach ($columns as $column) {
            $primary = $column === $primary;
            if (!$primary) {
                $cols .= empty($cols) ? $column : ',' . $column;
                $cols .= '=:' . $column;
                $keys .= empty($keys) ? '' : ',';
                $keys .= ':' . $column;
            } else {
                $pColumn = $column;
                $pValue = $entity->$column;
            }
        }
        try {
            $this->con = $this->beginTransaction();
            $req = $this->prepare('UPDATE ' . $table . ' SET ' . $cols . ' WHERE ' . $pColumn . ' = ' . $pValue . ';');
            $keys = explode(',', $keys);
            foreach ($keys as $key => $value) {
                $value = str_replace(':', '', $value);
                $var = array_search($value, $columns, true);
                if (!empty($entity->$var)) {
                    $req->bindParam($keys[$key], $entity->$var);
                }
            }
            $result = $req->execute();
            $e = $req->errorInfo();
            if (!empty($e[1]) && $_SERVER['APP']['DEBUG']) {
                throw new RuntimeException($e[2], $e[1]);
            }
            $this->con = $this->commit();
            return $result;
        } catch (PDOException | Exception | RuntimeException $e) {
            $this->con = $this->rollBack();
            http_response_code(500);
            exit((new Response())->renderView($_SERVER['APP']['FORMAT'], null, array(
                'code' => 'ERR_MYSQL_' . $e->getCode(),
                'message' => $e->getMessage()
            )));
        }
    }

    /**
     * Delete an Entity from the database
     *
     * @param Entity $entity
     * @return bool
     */
    public function delete(Entity $entity): bool
    {
        $table = $this->em->getTable($entity);
        $primary = $this->em->getTableKey($entity);
        $pColumn = $pValue = '';
        foreach ($this->em->getColumns($entity) as $column) {
            $primary = $column === $primary;
            if ($primary) {
                $pColumn = $column;
                $pValue = $entity->$column;
            }
        }
        try {
            $this->con = $this->beginTransaction();
            $req = $this->prepare('DELETE FROM ' . $table . ' WHERE ' . $pColumn . ' = ' . $pValue . ';');
            $result = $req->execute();
            $e = $req->errorInfo();
            if (!empty($e[1]) && $_SERVER['APP']['DEBUG']) {
                throw new RuntimeException($e[2], $e[1]);
            }
            $this->con = $this->commit();
            return $result;
        } catch (PDOException | Exception | RuntimeException $e) {
            $this->con = $this->rollBack();
            http_response_code(500);
            exit((new Response())->renderView($_SERVER['APP']['FORMAT'], null, array(
                'code' => 'ERR_MYSQL_' . $e->getCode(),
                'message' => $e->getMessage()
            )));
        }
    }
}