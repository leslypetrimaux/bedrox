<?php

namespace Bedrox\Core\Interfaces;

use Bedrox\Core\Entity;

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