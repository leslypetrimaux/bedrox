<?php

namespace Bedrox\EDR\Databases;

use Bedrox\Core\Env;
use Bedrox\EDR\EDR;
use Bedrox\EDR\Entity;
use Bedrox\EDR\EntityManager;
use Bedrox\Core\Exceptions\BedroxException;
use Bedrox\EDR\Interfaces\iSgbd;
use Bedrox\EDR\Column;
use Exception;
use PDO;
use PDOException;
use RuntimeException;

class MySQL extends PDO implements iSgbd
{
    public const ENCODE = 'SET NAMES ';
    public const UTF8 = 'utf8';

    public const STRATEGY_UUID = 'uuid';
    public const STRATEGY_AI = 'auto';

    protected $em;
    protected $con;
    protected $driver;

    /**
     * MySQL constructor.
     * Class do manage MySQL transactions. Array used to retrieve Entities.
     * Read Entity parameters to search and write the database.
     * Connect user using Entity
     * Read all/one (select) Entity
     * Persists (insert/update) Entity
     * Remove (delete) Entity
     *
     * @param string $driver
     * @param string $host
     * @param int $port
     * @param string $user
     * @param string $pwd
     * @param string $schema
     */
    public function __construct(string $driver, string $host, int $port, string $user, string $pwd, string $schema)
    {
        try {
            $opt = $driver === EDR::MYSQL ? array(PDO::MYSQL_ATTR_INIT_COMMAND => $this->getEncodage($_SERVER[Env::APP][Env::SGBD][Env::EDR_ENCODE])) : null;
            $this->driver = $driver;
            parent::__construct(
                EDR::MYSQL . ':dbname=' . $schema . ';port=' . $port . ';host=' . $host,
                $user,
                $pwd,
                $opt
            );
            $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            if ($this->getAttribute(PDO::ATTR_DRIVER_NAME) === EDR::MYSQL) {
                $this->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
            }
            $this->em = new EntityManager();
        } catch (PDOException $e) {
            BedroxException::render(
                'ERR_' . strtoupper($this->driver) . '_CONSTRUCT_' . $e->getCode(),
                $e->getMessage()
            );
        }
    }

    /**
     * Get PDO Encodage type (PDO::MYSQL_ATTR_INIT_COMMAND)
     *
     * @param string $encodage
     * @return string|null
     */
    public function getEncodage(string $encodage): ?string
    {
        return self::UTF8;
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
            BedroxException::render(
                'ERR_' . strtoupper($this->driver) . '_BUILDQUERY_' . $e->getCode(),
                $e->getMessage()
            );
        }
        return null;
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
        $vars = array();
        $entity = $this->em->getEntity($table);
        $primary = $this->em->getTableKey($entity);
        $foreign = $this->em->getForeignKey($entity);
        $columns = $this->em->getColumns($entity);
        $cols = '';
        foreach ($columns as $key => $column) {
            /** @var Column $column */
            $cols .= empty($cols) ? $column->getName() : ',' . $column->getName();
            $vars[$column->getName()] = $key;
        }
        try {
            $req = $this->prepare('SELECT ' . $cols . ' FROM ' . $table . ' WHERE ' . $primary . ' = :primary;');
            if (!empty($id)) {
                $req->bindParam('primary', $id);
            }
            $req->execute();
            $result = $req->fetch(PDO::FETCH_ASSOC);
            $e = $req->errorInfo();
            if (!empty($e[1]) && $_SERVER[Env::APP][Env::DEBUG]) {
                throw new RuntimeException($e[2], $e[1]);
            }
            if ($result) {
                foreach ($result as $key => $value) {
                    $var = $vars[$key];
                    if (is_object($foreign[$var])) {
                        $fTable = $this->em->getTable($foreign[$var]);
                        $value = $this->find($fTable, $value);
                    }
                    $entity->$var = $value;
                }
            } else {
                $entity = null;
            }
            return $entity;
        } catch (PDOException | Exception | RuntimeException $e) {
            BedroxException::render(
                'ERR_' . strtoupper($this->driver) . '_FIND_' . $e->getCode(),
                $e->getMessage()
            );
        }
        return null;
    }

    /**
     * Search the table to retrieve one field and convert it in Entity
     *
     * @param string $table
     * @param array $criteria
     * @return Entity|null
     */
    public function findOneBy(string $table, array $criteria): ?Entity
    {
        if (empty($criteria)) {
            throw new RuntimeException('You must send an array $criteria. Please check your function.');
        }
        $vars = array();
        $entity = $this->em->getEntity($table);
        $foreign = $this->em->getForeignKey($entity);
        $columns = $this->em->getColumns($entity);
        $cols = $clauses = '';
        foreach ($criteria as $key => $value) {
            $clauses .= empty($clauses) ? $key . '="' . $value . '"' : ' AND ' . $key . '"' . $value . '"';
        }
        foreach ($columns as $key => $column) {
            /** @var Column $column */
            $cols .= empty($cols) ? $column->getName() : ',' . $column->getName();
            $vars[$column->getName()] = $key;
        }
        if (empty($clauses)) {
            throw new RuntimeException('Your $criteria array is empty. Please check your function.');
        }
        try {
            $req = $this->query('SELECT ' . $cols . ' FROM ' . $table . ' WHERE ' . $clauses);
            $result = $req->fetch(PDO::FETCH_ASSOC);
            $e = $req->errorInfo();
            if (!empty($e[1]) && $_SERVER[Env::APP][Env::DEBUG]) {
                throw new RuntimeException($e[2], $e[1]);
            }
            if ($result) {
                foreach ($result as $key => $value) {
                    $var = $vars[$key];
                    if (is_object($foreign[$var])) {
                        $fTable = $this->em->getTable($foreign[$var]);
                        $value = $this->find($fTable, $value);
                    }
                    $entity->$var = $value;
                }
            } else {
                $entity = null;
            }
            return $entity;
        } catch (PDOException | Exception | RuntimeException $e) {
            BedroxException::render(
                'ERR_' . strtoupper($this->driver) . '_FINDONEBY_' . $e->getCode(),
                $e->getMessage()
            );
        }
        return null;
    }

    /**
     * Search the table to retrieve all field and convert it in Entity
     *
     * @param string $table
     * @return array|null
     */
    public function findAll(string $table): ?array
    {
        $entities = $vars = array();
        $entity = $this->em->getEntity($table);
        $columns = $this->em->getColumns($entity);
        $foreign = $this->em->getForeignKey($entity);
        $cols = '';
        foreach ($columns as $key => $column) {
            /** @var Column $column */
            $cols .= empty($cols) ? $column->getName() : ',' . $column->getName();
            $vars[$column->getName()] = $key;
        }
        try {
            $req = $this->query('SELECT ' . $cols . ' FROM ' . $table . ';');
            $results = $req->fetchAll(PDO::FETCH_ASSOC);
            $e = $req->errorInfo();
            if (!empty($e[1]) && $_SERVER[Env::APP][Env::DEBUG]) {
                throw new RuntimeException($e[2], $e[1]);
            }
            if ($results) {
                foreach ($results as $result) {
                    $entity = $this->em->getEntity($table);
                    foreach ($result as $key => $value) {
                        $var = $vars[$key];
                        if (is_object($foreign)) {
                            $fTable = $this->em->getTable($foreign[$var]);
                            $value = $this->find($fTable, $value);
                        }
                        $entity->$var = $value;
                    }
                    $entities[] = $entity;
                }
            }
            return $entities;
        } catch (PDOException | Exception | RuntimeException $e) {
            BedroxException::render(
                'ERR_' . strtoupper($this->driver) . '_FINDALL_' . $e->getCode(),
                $e->getMessage()
            );
        }
        return null;
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
        $vars = array();
        $table = $this->em->getTable($entity);
        $primary = $this->em->getTableKey($entity);
        $columns = $this->em->getColumns($entity);
        $primaryType = $this->em->getTableKeyStrategy($entity);
        $cols = $keys = '';
        $uuid = false;
        foreach ($columns as $key => $column) {
            /** @var Column $column */
            $primary = $column->getName() === $primary;
            $vars[$column->getName()] = $key;
            switch ($primaryType[$column->getName()]) {
                case self::STRATEGY_UUID:
                    $cols .= empty($cols) ? $column->getName() : ',' . $column->getName();
                    $keys .= empty($keys) ? '' : ',';
                    $keys .= ':' . $column->getName();
                    $uuid = true;
                    break;
                case self::STRATEGY_AI:
                default:
                    if (!$primary) {
                        $cols .= empty($cols) ? $column->getName() : ',' . $column->getName();
                        $keys .= empty($keys) ? '' : ',';
                        $keys .= ':' . $column->getName();
                    }
                    break;
            }
        }
        try {
            $this->con = $this->beginTransaction();
            $req = $this->prepare('INSERT INTO ' . $table . ' (' . $cols . ') VALUES (' . $keys . ');');
            $cols = explode(',', $cols);
            $keys = explode(',', $keys);
            foreach ($cols as $key => $value) {
                $value = str_replace(':', '', $value);
                $var = $vars[$value];
                if ($uuid && $var === $this->em->getTableKey($entity)) {
                    $entity->$var = uniqid('', true);
                    $req->bindParam($keys[$key], $entity->$var);
                }
                if (is_object($entity->$var)) {
                    $fKey = $this->em->getTableKey($entity->$var);
                    $req->bindParam($value, $entity->$var->$fKey);
                } else {
                    $req->bindParam($value, $entity->$var);
                }
            }
            $result = $req->execute();
            $e = $req->errorInfo();
            if (!empty($e[1]) && $_SERVER[Env::APP][Env::DEBUG]) {
                throw new RuntimeException($e[2], $e[1]);
            }
            $this->con = $this->commit();
            return $result;
        } catch (PDOException | Exception | RuntimeException $e) {
            $this->con = $this->rollBack();
            BedroxException::render(
                'ERR_' . strtoupper($this->driver) . '_INSERT_' . $e->getCode(),
                $e->getMessage()
            );
        }
        return null;
    }

    /**
     * Update an Entity in the database
     *
     * @param Entity $entity
     * @return bool
     */
    public function update(Entity $entity): bool
    {
        $vars = array();
        $table = $this->em->getTable($entity);
        $primary = $this->em->getTableKey($entity);
        $columns = $this->em->getColumns($entity);
        $pColumn = $pValue = $cols = $keys = '';
        foreach ($columns as $key => $column) {
            /** @var Column $column */
            $vars[$column->getName()] = $key;
            $primary = $column->getName() === $primary;
            if (!$primary) {
                $cols .= empty($cols) ? $column->getName() : ',' . $column->getName();
                $cols .= '=:' . $column->getName();
                $keys .= empty($keys) ? '' : ',';
                $keys .= ':' . $column->getName();
            } else {
                $pColumn = $column->getName();
                $pValue = $entity->$pColumn;
            }
        }
        try {
            $this->con = $this->beginTransaction();
            $req = $this->prepare('UPDATE ' . $table . ' SET ' . $cols . ' WHERE ' . $pColumn . ' = "' . $pValue . '";');
            $keys = explode(',', $keys);
            foreach ($keys as $value) {
                $value = str_replace(':', '', $value);
                $var = $vars[$value];
                if (is_object($entity->$var)) {
                    $fKey = $this->em->getTableKey($entity->$var);
                    $req->bindParam($value, $entity->$var->$fKey);
                } else {
                    $req->bindParam($value, $entity->$var);
                }
            }
            $result = $req->execute();
            $e = $req->errorInfo();
            if (!empty($e[1]) && $_SERVER[Env::APP][Env::DEBUG]) {
                throw new RuntimeException($e[2], $e[1]);
            }
            $this->con = $this->commit();
            return $result;
        } catch (PDOException | Exception | RuntimeException $e) {
            $this->con = $this->rollBack();
            BedroxException::render(
                'ERR_' . strtoupper($this->driver) . '_UPDATE_' . $e->getCode(),
                $e->getMessage()
            );
        }
        return null;
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
            if (!empty($e[1]) && $_SERVER[Env::APP][Env::DEBUG]) {
                throw new RuntimeException($e[2], $e[1]);
            }
            $this->con = $this->commit();
            return $result;
        } catch (PDOException | Exception | RuntimeException $e) {
            $this->con = $this->rollBack();
            BedroxException::render(
                'ERR_' . strtoupper($this->driver) . '_DELETE_' . $e->getCode(),
                $e->getMessage()
            );
        }
        return null;
    }
}
