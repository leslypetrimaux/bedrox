<?php

namespace Bedrox\EDR;

use Bedrox\Core\Env;
use Bedrox\EDR\Interfaces\iRepository;

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
        $this->con = (new EDR)->setDriver(!empty($_SERVER[Env::APP][Env::SGBD][Env::EDR_DRIVER]) ? $_SERVER[Env::APP][Env::SGBD][Env::EDR_DRIVER] : null);
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
     * @param array $criteria
     * @return Entity|null
     */
    public function findOneBy(array $criteria): ?Entity
    {
        return $this->con->findOneBy($this->table, $criteria);
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
