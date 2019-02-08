<?php

namespace Bedrox\Core\Interfaces;

use Bedrox\Core\Entity;
use Bedrox\Core\Repository;

interface iEntityManager
{
    /**
     * @param string $entity
     * @return Repository|null
     */
    public function getRepo(?string $entity): ?Repository;

    /**
     * @param string $entity
     * @return Entity|mixed|null
     */
    public function getEntity(?string $entity): ?Entity;

    /**
     * @param Entity $entity
     * @return string|null
     */
    public function getTable(Entity $entity): ?string;

    /**
     * @param Entity $entity
     * @return string|null
     */
    public function getTableKey(Entity $entity): ?string;

    /**
     * @param Entity $entity
     * @return array|null
     */
    public function getColumns(Entity $entity): array;
}