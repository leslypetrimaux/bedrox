<?php

namespace Bedrox\Core;

use Bedrox\Core\Interfaces\iRepository;

class Repository implements iRepository
{
    protected $entity;
    protected $table;
    public $con;
    public $em;

    /**
     * Repository constructor
     * Set the SGBD Driver to be used and the table for requests.
     * 
     * @param string $table
     */
    public function __construct(string $table)
    {
        $this->con = (new Db())->setDriver(!empty($_SERVER['APP']['SGBD']['DRIVER']) ? $_SERVER['APP']['SGBD']['DRIVER'] : null);
        $this->table = $table;
    }

    /**
     * Return a QueryBuilder for the selected table.
     *
     * @return QueryBuilder
     */
    public function createQueryBuilder(): QueryBuilder
    {
        return new QueryBuilder($this->table);
    }

    /**
     * See the SGBD function for documentation.
     *
     * @return array|null
     */
    public function findAll(): ?array
    {
        return $this->con->findAll($this->table);
    }

    /**
     * See the SGBD function for documentation.
     *
     * @param string $id
     * @return Entity|mixed|null
     */
    public function find(string $id): ?Entity
    {
        return $this->con->find($this->table, $id);
    }

    /**
     * See the SGBD function for documentation.
     *
     * @param Entity $entity
     * @return bool
     */
    public function persist(Entity $entity): bool
    {
        return $this->con->persist($entity);
    }

    /**
     * See the SGBD function for documentation.
     *
     * @param Entity $entity
     * @return bool
     */
    public function delete(Entity $entity): bool
    {
        return $this->con->delete($entity);
    }
}