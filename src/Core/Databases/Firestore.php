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
     * @param int $id
     * @return Entity|mixed|null
     */
    public function find(string $table, int $id): ?Entity
    {
        // TODO: Implement find() method.
        return null;
    }

    /**
     * @param string $table
     * @return array|null
     */
    public function findAll(string $table): ?array
    {
        // TODO: Implement findAll() method.
        return null;
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