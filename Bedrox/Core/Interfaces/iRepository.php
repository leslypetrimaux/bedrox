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
     * @param int $id
     * @return mixed
     */
    public function find(int $id);

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