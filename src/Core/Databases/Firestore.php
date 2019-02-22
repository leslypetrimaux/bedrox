<?php

namespace Bedrox\Core\Databases;

use Bedrox\Core\Entity;
use Bedrox\Core\EntityManager;
use Bedrox\Core\Interfaces\iSgbd;
use Bedrox\Google\Firebase\CloudFirestore;

class Firestore extends CloudFirestore implements iSgbd
{
    public const UTF8 = 'utf-8';

    protected $em;

    /**
     * Firestore constructor.
     *
     * @param string $host
     * @param string $apiKey
     * @param string $oAuthToken
     * @param string $type
     */
    public function __construct(string &$host, string $apiKey, string $oAuthToken, string $type = 'public')
    {
        parent::__construct($host, $apiKey, $oAuthToken, $type);
        $this->em = new EntityManager();
    }

    /**
     * Get Encodage type (PDO::MYSQL_ATTR_INIT_COMMAND)
     *
     * @param string $encodage
     * @return string|null
     */
    public function getEncodage(string $encodage): ?string
    {
        switch ($encodage) {
            case self::UTF8:
            default:
                $result = self::UTF8;
                break;
        }
        return !empty($result) ? $result : self::UTF8;
    }

    /**
     * A customized query builder for FirebaseDatabase Cloud Firestore
     *
     * @param string $query
     * @return array|null
     */
    public function buildQuery(string $query): ?array
    {
        // TODO: Implement buildQuery() method.
        return null;
    }

    /**
     * @param string $table
     * @param string $id
     * @return Entity|null
     */
    public function find(string $table, string $id): ?Entity
    {
        $path = $table . '/' . $id;
        $content = $this->get($path);
        $entity = $this->em->getEntity($table);
        $columns = $this->em->getColumns($entity);
        if ($content !== null) {
            foreach ($content as $key => $value) {
                $var = array_search($key, $columns, true);
                $entity->$var = $value;
            }
        } else {
            $entity = null;
        }
        return $entity;
    }

    /**
     * @param string $table
     * @return array|null
     */
    public function findAll(string $table): ?array
    {
        $content = $this->get($table);
        $result = array();
        foreach ($content as $col) {
            $entity = $this->em->getEntity($table);
            $columns = $this->em->getColumns($entity);
            foreach ($col as $key => $value) {
                $var = array_search($key, $columns, true);
                $entity->$var = $value;
            }
            $result[] = $entity;
        }
        return $result;
    }

    /**
     * @param Entity $entity
     * @return bool
     */
    public function persist(Entity $entity): bool
    {
        // TODO: Implement persist() method.
        return false;
    }

    /**
     * @param Entity $entity
     * @return bool
     */
    public function insert(Entity $entity): bool
    {
        // TODO: Implement insert() method.
        return false;
    }

    /**
     * @param Entity $entity
     * @return bool
     */
    public function update(Entity $entity): bool
    {
        // TODO: Implement update() method.
        return false;
    }

    /**
     * @param Entity $entity
     * @return bool
     */
    public function delete(Entity $entity): bool
    {
        // TODO: Implement delete() method.
        return false;
    }
}