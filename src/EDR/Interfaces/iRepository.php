<?php

namespace Bedrox\EDR\Interfaces;

use Bedrox\EDR\Entity;

interface iRepository
{
    /**
     * @return mixed
     */
    public function createQueryBuilder();

    /**
     * @return mixed
     */
    public function findAll();

    /**
     * @param string $id
     * @return mixed
     */
    public function find(string $id);

    /**
     * @param array $criteria
     * @return Entity|null
     */
    public function findOneBy(array $criteria): ?Entity;

    /**
     * @param Entity $entity
     * @return mixed
     */
    public function persist(Entity $entity);

    /**
     * @param Entity $entity
     * @return mixed
     */
    public function delete(Entity $entity);
}
