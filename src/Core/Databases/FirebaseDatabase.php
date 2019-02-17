<?php

namespace Bedrox\Core\Databases;

use Bedrox\Core\Entity;
use Bedrox\Core\EntityManager;
use Bedrox\Core\Functions\Parsing;
use Bedrox\Core\Interfaces\iSgbd;
use Bedrox\Google\Firebase\RealtimeDatabase;

class FirebaseDatabase extends RealtimeDatabase implements iSgbd
{
    public const UTF8 = 'utf-8';

    protected $em;

    /**
     * Firestore constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct($config);
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
     * @return Entity|mixed|null
     */
    public function find(string $table, string $id): ?Entity
    {
        $path = $table . '/' . $id;
        $json = $this->get($path);
        $result = (new Parsing())->parseRecursiveToArray(json_decode($json));
        $entity = $this->em->getEntity($table);
        $columns = $this->em->getColumns($entity);
        foreach ($result as $key => $value) {
            $var = array_search($key, $columns, true);
            $entity->$var = $value;
        }
        return $entity;
    }

    /**
     * @param string $table
     * @return array|null
     */
    public function findAll(string $table): ?array
    {
        $json = $this->get($table);
        $content = (new Parsing())->parseRecursiveToArray(json_decode($json));
        $result = array();
        foreach ($content as $data) {
            if (!empty($data)) {
                $entity = $this->em->getEntity($table);
                $columns = $this->em->getColumns($entity);
                foreach ($data as $key => $value) {
                    $var = array_search($key, $columns, true);
                    $entity->$var = $value;
                }
                $result[] = $entity;
            }
        }
        return $result;
    }

    /**
     * @param Entity $entity
     * @return bool
     */
    public function persist(Entity $entity): bool
    {
        return $entity->getId() !== null ? $this->update($entity) : $this->insert($entity);
    }

    /**
     * @param Entity $entity
     * @return bool
     */
    public function insert(Entity $entity): bool
    {
        // TODO: Implement insert() method.
        // dd(uniqid('', true));
        return true;
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